<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function positions(): array
{
    return ['GOL', 'ZAG', 'LE', 'LD', 'VOL', 'MC', 'MEI', 'PE', 'PD', 'ATA'];
}

function countriesWithSecondDivision(): array
{
    return db()->query(
        "SELECT co.name, co.code, l.slug, d.id division_id
         FROM countries co
         JOIN leagues l ON l.country_id = co.id
         JOIN divisions d ON d.league_id = l.id AND d.level = 2
         ORDER BY co.name"
    )->fetchAll();
}

function userPlayer(): ?array
{
    $stmt = db()->query(
        "SELECT p.*, c.name club_name, c.reputation, d.id division_id, d.name division_name,
                l.name league_name, l.slug league_slug, co.name country_name, s.current_year, s.current_round
         FROM players p
         JOIN clubs c ON c.id = p.club_id
         JOIN divisions d ON d.id = c.division_id
         JOIN leagues l ON l.id = d.league_id
         JOIN countries co ON co.id = l.country_id
         CROSS JOIN seasons s
         WHERE p.user_controlled = 1 AND s.active = 1
         ORDER BY p.id DESC
         LIMIT 1"
    );

    return $stmt->fetch() ?: null;
}

function countryNameByCode(string $code): string
{
    $stmt = db()->prepare('SELECT name FROM countries WHERE code = ? LIMIT 1');
    $stmt->execute([$code]);

    return (string) ($stmt->fetchColumn() ?: $code);
}

function createCareerPlayer(string $name, string $position, string $nationalityCode): int
{
    if (!in_array($position, positions(), true)) {
        throw new InvalidArgumentException('Posição inválida.');
    }

    $stmt = db()->prepare(
        "SELECT c.id
         FROM clubs c
         JOIN divisions d ON d.id = c.division_id
         JOIN leagues l ON l.id = d.league_id
         JOIN countries co ON co.id = l.country_id
         WHERE co.code = ? AND d.level = 2
         ORDER BY c.reputation DESC, c.id
         LIMIT 1"
    );
    $stmt->execute([$nationalityCode]);
    $clubId = (int) $stmt->fetchColumn();

    if (!$clubId) {
        throw new RuntimeException('Não existe segunda divisão cadastrada para esse país.');
    }

    db()->beginTransaction();
    resetCareerData(false);
    $initialOverall = random_int(54, 62);
    $abilities = generatePlayerAbilities($position, $initialOverall);
    $insert = db()->prepare(
        "INSERT INTO players (
            club_id, user_controlled, name, position, nationality, overall, potential,
            pace, dribbling, finishing, defending, passing, physical
         )
         VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insert->execute([
        $clubId,
        trim($name),
        $position,
        $nationalityCode,
        $initialOverall,
        random_int(72, 88),
        $abilities['pace'],
        $abilities['dribbling'],
        $abilities['finishing'],
        $abilities['defending'],
        $abilities['passing'],
        $abilities['physical'],
    ]);
    $playerId = (int) db()->lastInsertId();
    generateSeasonFixturesIfNeeded($clubId);
    db()->commit();

    return $playerId;
}

function generatePlayerAbilities(string $position, int $overall): array
{
    $abilities = [
        'pace' => $overall + random_int(-3, 8),
        'dribbling' => $overall + random_int(-4, 7),
        'finishing' => $overall + random_int(-6, 7),
        'defending' => $overall + random_int(-8, 5),
        'passing' => $overall + random_int(-4, 8),
        'physical' => $overall + random_int(-2, 9),
    ];

    if (in_array($position, ['ZAG', 'LE', 'LD', 'VOL'], true)) {
        $abilities['defending'] += 8;
        $abilities['physical'] += 4;
    }

    if (in_array($position, ['MEI', 'MC', 'PE', 'PD'], true)) {
        $abilities['dribbling'] += 6;
        $abilities['passing'] += 6;
    }

    if ($position === 'ATA') {
        $abilities['finishing'] += 10;
        $abilities['pace'] += 4;
    }

    if ($position === 'GOL') {
        $abilities['defending'] += 12;
        $abilities['physical'] += 5;
        $abilities['finishing'] -= 12;
    }

    foreach ($abilities as $key => $value) {
        $abilities[$key] = max(35, min(99, $value));
    }

    return $abilities;
}

function generateSeasonFixturesIfNeeded(int $clubId): void
{
    $seasonId = activeSeasonId();
    $divisionId = (int) db()->query("SELECT division_id FROM clubs WHERE id = {$clubId}")->fetchColumn();

    $check = db()->prepare('SELECT COUNT(*) FROM fixtures WHERE season_id = ? AND division_id = ?');
    $check->execute([$seasonId, $divisionId]);
    if ((int) $check->fetchColumn() > 0) {
        return;
    }

    $clubs = db()->prepare('SELECT id FROM clubs WHERE division_id = ? ORDER BY reputation DESC, id');
    $clubs->execute([$divisionId]);
    $clubIds = array_map('intval', array_column($clubs->fetchAll(), 'id'));

    if (count($clubIds) < 2) {
        return;
    }

    $round = 1;
    $insert = db()->prepare(
        "INSERT INTO fixtures (season_id, division_id, round_number, home_club_id, away_club_id, user_match)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    for ($i = 0; $i < count($clubIds); $i++) {
        for ($j = $i + 1; $j < count($clubIds); $j++) {
            $insert->execute([$seasonId, $divisionId, $round++, $clubIds[$i], $clubIds[$j], (int) ($clubIds[$i] === $clubId || $clubIds[$j] === $clubId)]);
            $insert->execute([$seasonId, $divisionId, $round++, $clubIds[$j], $clubIds[$i], (int) ($clubIds[$i] === $clubId || $clubIds[$j] === $clubId)]);
        }
    }
}

function activeSeasonId(): int
{
    $id = (int) db()->query('SELECT id FROM seasons WHERE active = 1 ORDER BY id DESC LIMIT 1')->fetchColumn();
    if ($id) {
        return $id;
    }

    db()->exec('INSERT INTO seasons (current_year, current_round, active) VALUES (2026, 1, 1)');
    return (int) db()->lastInsertId();
}

function nextUserFixture(int $playerId): ?array
{
    $player = userPlayer();
    if (!$player) {
        return null;
    }

    generateSeasonFixturesIfNeeded((int) $player['club_id']);

    $stmt = db()->prepare(
        "SELECT f.*, hc.name home_name, ac.name away_name
         FROM fixtures f
         JOIN clubs hc ON hc.id = f.home_club_id
         JOIN clubs ac ON ac.id = f.away_club_id
         WHERE f.season_id = ? AND f.played = 0
           AND (f.home_club_id = ? OR f.away_club_id = ?)
         ORDER BY f.round_number, f.id
         LIMIT 1"
    );
    $stmt->execute([activeSeasonId(), $player['club_id'], $player['club_id']]);

    return $stmt->fetch() ?: null;
}

function simulateFixture(int $fixtureId, bool $withEvents = true): array
{
    $fixture = fixtureById($fixtureId);
    if (!$fixture || (int) $fixture['played'] === 1) {
        return ['fixture' => $fixture, 'events' => eventsForFixture($fixtureId), 'player_status' => 'Partida já encerrada.'];
    }

    $player = userPlayer();
    $isUserHome = $player && (int) $fixture['home_club_id'] === (int) $player['club_id'];
    $isUserAway = $player && (int) $fixture['away_club_id'] === (int) $player['club_id'];
    $userInMatch = $isUserHome || $isUserAway;
    $injured = false;
    $starts = $userInMatch && $player && (int) $player['stamina'] >= 50;
    $events = [];
    $homeOverall = clubOverall((int) $fixture['home_club_id']);
    $awayOverall = clubOverall((int) $fixture['away_club_id']);
    $homeGoals = 0;
    $awayGoals = 0;
    $homeGoalMinutes = [];
    $awayGoalMinutes = [];

    for ($minute = 1; $minute <= 90; $minute++) {
        $homeChance = goalChancePerMinute($homeOverall, $awayOverall);
        $awayChance = goalChancePerMinute($awayOverall, $homeOverall);

        if (chance($homeChance)) {
            $homeGoals++;
            $homeGoalMinutes[] = $minute;
            $events[] = [$minute, $fixture['home_name'] . ' faz gol. Placar: ' . $homeGoals . ' x ' . $awayGoals . '.'];
        }

        if (chance($awayChance)) {
            $awayGoals++;
            $awayGoalMinutes[] = $minute;
            $events[] = [$minute, $fixture['away_name'] . ' faz gol. Placar: ' . $homeGoals . ' x ' . $awayGoals . '.'];
        }

        if ($withEvents && chance(randomNarrationChance($minute))) {
            $events[] = [$minute, randomMatchNarration($fixture, $homeGoals, $awayGoals)];
        }
    }

    if ($starts && $player) {
        $performance = random_int(1, 100) + (int) $player['overall'];
        if ($performance > 112 && $player['position'] !== 'GOL' && userTeamScored($isUserHome, $homeGoalMinutes, $awayGoalMinutes)) {
            db()->prepare('UPDATE players SET goals = goals + 1 WHERE id = ?')->execute([$player['id']]);
            $events[] = [userGoalMinute($isUserHome, $homeGoalMinutes, $awayGoalMinutes), $player['name'] . ' aparece na área e marca um gol importante.'];
        }
        if ($performance > 98 && random_int(0, 1) === 1) {
            db()->prepare('UPDATE players SET assists = assists + 1 WHERE id = ?')->execute([$player['id']]);
            $events[] = [random_int(10, 88), $player['name'] . ' dá um passe decisivo e levanta a torcida.'];
        }
        db()->prepare('UPDATE players SET appearances = appearances + 1 WHERE id = ?')
            ->execute([$player['id']]);
    } elseif ($userInMatch && $player) {
        db()->prepare('UPDATE players SET bench_games = bench_games + 1 WHERE id = ?')
            ->execute([$player['id']]);
        $events[] = [1, $player['name'] . ' começa no banco de reservas.'];
    }

    $events[] = [1, 'Apita o árbitro. Cada minuto será calculado pela diferença de overall médio dos times.'];
    $events[] = [90, 'Fim de jogo: ' . $fixture['home_name'] . ' ' . $homeGoals . ' x ' . $awayGoals . ' ' . $fixture['away_name'] . '.'];
    usort($events, fn (array $a, array $b): int => $a[0] <=> $b[0]);

    db()->prepare('UPDATE fixtures SET home_goals = ?, away_goals = ?, played = 1 WHERE id = ?')
        ->execute([$homeGoals, $awayGoals, $fixtureId]);
    db()->prepare('UPDATE seasons SET current_round = GREATEST(current_round, ?) WHERE id = ?')
        ->execute([(int) $fixture['round_number'] + 1, $fixture['season_id']]);

    db()->prepare('DELETE FROM match_events WHERE fixture_id = ?')->execute([$fixtureId]);
    $eventInsert = db()->prepare('INSERT INTO match_events (fixture_id, minute, event_text) VALUES (?, ?, ?)');
    foreach ($events as $event) {
        $eventInsert->execute([$fixtureId, $event[0], $event[1]]);
    }

    $status = updatePlayerProgressAfterMatch($fixture, $starts, $injured);

    return ['fixture' => fixtureById($fixtureId), 'events' => eventsForFixture($fixtureId), 'player_status' => $status];
}

function updatePlayerProgressAfterMatch(array $fixture, bool $starts, bool $injured): string
{
    $player = userPlayer();
    if (!$player) {
        return '';
    }

    $staminaCost = playerStaminaCost($player);

    if ($starts && random_int(1, 100) <= 55) {
        db()->prepare('UPDATE players SET overall = LEAST(potential, overall + 1), stamina = GREATEST(0, stamina - ?), injured_until_match = NULL WHERE id = ?')->execute([$staminaCost, $player['id']]);
        return 'Boa atuação: seu overall evoluiu.';
    }

    if ($starts) {
        db()->prepare('UPDATE players SET stamina = GREATEST(0, stamina - ?), injured_until_match = NULL WHERE id = ?')->execute([$staminaCost, $player['id']]);
        return 'Você ganhou minutos e perdeu ' . $staminaCost . '% de energia.';
    }

    db()->prepare('UPDATE players SET stamina = LEAST(100, stamina + 18), injured_until_match = NULL WHERE id = ?')->execute([$player['id']]);
    return 'Você ficou no banco e recuperou energia.';
}

function fixtureById(int $fixtureId): ?array
{
    $stmt = db()->prepare(
        "SELECT f.*, hc.name home_name, ac.name away_name
         FROM fixtures f
         JOIN clubs hc ON hc.id = f.home_club_id
         JOIN clubs ac ON ac.id = f.away_club_id
         WHERE f.id = ?"
    );
    $stmt->execute([$fixtureId]);

    return $stmt->fetch() ?: null;
}

function eventsForFixture(int $fixtureId): array
{
    $stmt = db()->prepare('SELECT * FROM match_events WHERE fixture_id = ? ORDER BY minute, id');
    $stmt->execute([$fixtureId]);

    return $stmt->fetchAll();
}

function clubStrength(int $clubId): int
{
    return clubOverall($clubId);
}

function clubOverall(int $clubId): int
{
    $stmt = db()->prepare('SELECT reputation FROM clubs WHERE id = ?');
    $stmt->execute([$clubId]);
    $base = (int) $stmt->fetchColumn();

    $players = db()->prepare('SELECT AVG(overall) FROM players WHERE club_id = ?');
    $players->execute([$clubId]);
    $avg = (int) $players->fetchColumn();

    return max(35, min(99, $avg > 0 ? (int) round(($base + $avg) / 2) : $base));
}

function playerMatchSituation(array $player): string
{
    if ((int) $player['stamina'] < 50) {
        return 'Banco';
    }

    return 'Titular';
}

function playerStaminaCost(array $player): int
{
    $physical = (int) ($player['physical'] ?? 60);

    return max(6, min(14, 16 - intdiv($physical, 10)));
}

function opponentForFixture(array $fixture, int $clubId): ?array
{
    $opponentId = (int) $fixture['home_club_id'] === $clubId ? (int) $fixture['away_club_id'] : (int) $fixture['home_club_id'];
    $stmt = db()->prepare('SELECT * FROM clubs WHERE id = ? LIMIT 1');
    $stmt->execute([$opponentId]);

    return $stmt->fetch() ?: null;
}

function leagueStandings(int $divisionId): array
{
    $clubs = db()->prepare('SELECT id, name, reputation FROM clubs WHERE division_id = ? ORDER BY reputation DESC, name');
    $clubs->execute([$divisionId]);
    $table = [];
    foreach ($clubs->fetchAll() as $club) {
        $table[(int) $club['id']] = [
            'club_id' => (int) $club['id'],
            'name' => $club['name'],
            'reputation' => (int) $club['reputation'],
            'played' => 0,
            'points' => 0,
            'gf' => 0,
            'ga' => 0,
            'gd' => 0,
            'position' => 0,
        ];
    }

    $fixtures = db()->prepare(
        'SELECT home_club_id, away_club_id, home_goals, away_goals
         FROM fixtures
         WHERE season_id = ? AND division_id = ? AND played = 1'
    );
    $fixtures->execute([activeSeasonId(), $divisionId]);

    foreach ($fixtures->fetchAll() as $fixture) {
        $homeId = (int) $fixture['home_club_id'];
        $awayId = (int) $fixture['away_club_id'];
        if (!isset($table[$homeId], $table[$awayId])) {
            continue;
        }

        $homeGoals = (int) $fixture['home_goals'];
        $awayGoals = (int) $fixture['away_goals'];
        $table[$homeId]['played']++;
        $table[$awayId]['played']++;
        $table[$homeId]['gf'] += $homeGoals;
        $table[$homeId]['ga'] += $awayGoals;
        $table[$awayId]['gf'] += $awayGoals;
        $table[$awayId]['ga'] += $homeGoals;

        if ($homeGoals > $awayGoals) {
            $table[$homeId]['points'] += 3;
        } elseif ($awayGoals > $homeGoals) {
            $table[$awayId]['points'] += 3;
        } else {
            $table[$homeId]['points']++;
            $table[$awayId]['points']++;
        }
    }

    foreach ($table as &$row) {
        $row['gd'] = $row['gf'] - $row['ga'];
    }
    unset($row);

    usort($table, fn (array $a, array $b): int => [$b['points'], $b['gd'], $b['gf'], $b['reputation']] <=> [$a['points'], $a['gd'], $a['gf'], $a['reputation']]);

    foreach ($table as $index => &$row) {
        $row['position'] = $index + 1;
    }
    unset($row);

    return $table;
}

function teamLeaguePosition(int $divisionId, int $clubId): ?int
{
    foreach (leagueStandings($divisionId) as $row) {
        if ((int) $row['club_id'] === $clubId) {
            return (int) $row['position'];
        }
    }

    return null;
}

function goalChancePerMinute(int $attackingOverall, int $defendingOverall): float
{
    $ratio = $attackingOverall / max(1, $defendingOverall);

    return max(0.004, min(0.045, 0.018 * $ratio));
}

function chance(float $probability): bool
{
    return random_int(1, 100000) <= (int) round($probability * 100000);
}

function randomNarrationChance(int $minute): float
{
    if ($minute < 3 || $minute > 88) {
        return 0.01;
    }

    return 0.12;
}

function randomMatchNarration(array $fixture, int $homeGoals, int $awayGoals): string
{
    $attackingTeam = random_int(0, 1) === 1 ? $fixture['home_name'] : $fixture['away_name'];
    $defendingTeam = $attackingTeam === $fixture['home_name'] ? $fixture['away_name'] : $fixture['home_name'];
    $templates = [
        '%s está no ataque e empurra o adversário para trás.',
        'Bela defesa do goleiro de %s.',
        '%s troca passes perto da área.',
        '%s tenta acelerar pela ponta.',
        'A defesa de %s corta o cruzamento no momento certo.',
        '%s arrisca de fora da área e leva perigo.',
        '%s recupera a bola no meio-campo.',
        'A torcida de %s cresce no jogo.',
        '%s escapa em contra-ataque.',
        'O goleiro de %s sai bem do gol e fica com a bola.',
        '%s pressiona buscando mudar o placar de ' . $homeGoals . ' x ' . $awayGoals . '.',
    ];
    $template = $templates[array_rand($templates)];
    $team = str_contains($template, 'defesa de') || str_contains($template, 'goleiro de') ? $defendingTeam : $attackingTeam;

    return sprintf($template, $team);
}

function userTeamScored(bool $isUserHome, array $homeGoalMinutes, array $awayGoalMinutes): bool
{
    return count($isUserHome ? $homeGoalMinutes : $awayGoalMinutes) > 0;
}

function userGoalMinute(bool $isUserHome, array $homeGoalMinutes, array $awayGoalMinutes): int
{
    $minutes = $isUserHome ? $homeGoalMinutes : $awayGoalMinutes;

    return (int) $minutes[array_rand($minutes)];
}

function resetCareerData(bool $withTransaction = true): void
{
    if ($withTransaction) {
        db()->beginTransaction();
    }

    db()->exec('DELETE FROM match_events');
    db()->exec('DELETE FROM fixtures');
    db()->exec('DELETE FROM transfer_offers');
    db()->exec('DELETE FROM players WHERE user_controlled = 1');
    db()->exec('UPDATE seasons SET active = 0');
    db()->exec('INSERT INTO seasons (current_year, current_round, active) VALUES (2026, 1, 1)');

    if ($withTransaction) {
        db()->commit();
    }
}

function skipSeason(int $playerId): void
{
    while ($fixture = nextUserFixture($playerId)) {
        simulateFixture((int) $fixture['id'], false);
    }

    createTransferOffers($playerId);
    $season = db()->query('SELECT current_year FROM seasons WHERE active = 1 ORDER BY id DESC LIMIT 1')->fetch();
    db()->exec('UPDATE seasons SET active = 0 WHERE active = 1');
    $nextYear = ((int) $season['current_year']) + 1;
    db()->prepare('INSERT INTO seasons (current_year, current_round, active) VALUES (?, 1, 1)')->execute([$nextYear]);

    $player = userPlayer();
    if ($player) {
        generateSeasonFixturesIfNeeded((int) $player['club_id']);
    }
}

function createTransferOffers(int $playerId): void
{
    $player = userPlayer();
    if (!$player) {
        return;
    }

    $stmt = db()->prepare(
        "SELECT c.id
         FROM clubs c
         JOIN divisions d ON d.id = c.division_id
         WHERE c.id <> ? AND c.reputation BETWEEN ? AND ?
         ORDER BY ABS(c.reputation - ?) ASC, RAND()
         LIMIT 3"
    );
    $min = max(45, (int) $player['overall'] - 8);
    $max = min(95, (int) $player['overall'] + 26);
    $stmt->execute([$player['club_id'], $min, $max, $player['overall']]);
    $offers = $stmt->fetchAll();

    $insert = db()->prepare(
        'INSERT INTO transfer_offers (player_id, from_club_id, to_club_id, season_year) VALUES (?, ?, ?, ?)'
    );
    foreach ($offers as $offer) {
        $insert->execute([$playerId, $player['club_id'], $offer['id'], $player['current_year']]);
    }
}

function pendingOffers(int $playerId): array
{
    $stmt = db()->prepare(
        "SELECT o.*, c.name to_club_name
         FROM transfer_offers o
         JOIN clubs c ON c.id = o.to_club_id
         WHERE o.player_id = ? AND o.accepted IS NULL
         ORDER BY o.id DESC"
    );
    $stmt->execute([$playerId]);

    return $stmt->fetchAll();
}

function acceptOffer(int $offerId): void
{
    $stmt = db()->prepare('SELECT * FROM transfer_offers WHERE id = ? AND accepted IS NULL');
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();
    if (!$offer) {
        return;
    }

    db()->beginTransaction();
    db()->prepare('UPDATE players SET club_id = ?, stamina = 100, injured_until_match = NULL WHERE id = ?')
        ->execute([$offer['to_club_id'], $offer['player_id']]);
    db()->prepare('UPDATE transfer_offers SET accepted = IF(id = ?, 1, 0) WHERE player_id = ? AND accepted IS NULL')
        ->execute([$offerId, $offer['player_id']]);
    generateSeasonFixturesIfNeeded((int) $offer['to_club_id']);
    db()->commit();
}

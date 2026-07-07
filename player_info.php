<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/game.php';

function playerEstimatedSalary(array $player): string
{
    $overall = (int) ($player['overall'] ?? 58);
    $potential = (int) ($player['potential'] ?? $overall);
    $goals = (int) ($player['goals'] ?? 0);
    $assists = (int) ($player['assists'] ?? 0);
    $salary = max(1200, (($overall - 45) * 850) + (($potential - $overall) * 450) + ($goals * 180) + ($assists * 140));

    return 'R$ ' . number_format($salary, 0, ',', '.') . '/mês';
}

function playerAverageStat(int $total, int $appearances): string
{
    if ($appearances <= 0) {
        return '-';
    }

    return number_format($total / $appearances, 2, ',', '.');
}

function playerRevelationClub(array $player): string
{
    $stmt = db()->prepare(
        "SELECT c.name
         FROM transfer_offers o
         JOIN clubs c ON c.id = o.from_club_id
         WHERE o.player_id = ? AND o.accepted = 1
         ORDER BY o.id
         LIMIT 1"
    );
    $stmt->execute([$player['id']]);

    return (string) ($stmt->fetchColumn() ?: $player['club_name']);
}

$player = userPlayer();
if (!$player) {
    header('Location: index.php');
    exit;
}

$appearances = (int) $player['appearances'];
$goals = (int) $player['goals'];
$assists = (int) $player['assists'];
$bestRatedSeason = (int) $player['season_rating_count'] > 0 ? (string) $player['current_year'] : '-';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informações do jogador - FutCarreira</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="shell player-info-shell">
        <nav class="appbar">
            <a class="brand" href="career.php">FutCarreira</a>
            <div class="appbar-actions">
                <a class="button" href="career.php">Voltar para carreira</a>
            </div>
        </nav>

        <section class="player-info-hero">
            <div>
                <p class="eyebrow">Informações do jogador</p>
                <h1><?= e($player['name']) ?></h1>
                <p><?= e($player['position']) ?> do <?= e($player['club_name']) ?>, representando <?= e(countryNameByCode($player['nationality'])) ?>.</p>
            </div>
            <div class="overall-badge player-info-overall">
                <span>OVR</span>
                <strong><?= e($player['overall']) ?></strong>
            </div>
        </section>

        <section class="player-info-layout">
            <article class="panel">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Perfil</p>
                        <h2>Dados gerais</h2>
                    </div>
                    <strong class="player-value"><?= e(playerMarketValue($player)) ?></strong>
                </div>
                <div class="player-grid player-info-grid">
                    <div><span>Valor</span><strong><?= e(playerMarketValue($player)) ?></strong></div>
                    <div><span>Salário</span><strong><?= e(playerEstimatedSalary($player)) ?></strong></div>
                    <div><span>Idade</span><strong><?= e($player['age']) ?></strong></div>
                    <div><span>Altura</span><strong><?= e($player['height_cm']) ?> cm</strong></div>
                    <div><span>Peso</span><strong><?= e($player['weight_kg']) ?> kg</strong></div>
                    <div><span>Perna dominante</span><strong><?= e($player['dominant_foot']) ?></strong></div>
                    <div><span>Nacionalidade</span><strong><?= e(countryNameByCode($player['nationality'])) ?></strong></div>
                    <div><span>Time revelação</span><strong><?= e(playerRevelationClub($player)) ?></strong></div>
                    <div><span>Time atual</span><strong><?= e($player['club_name']) ?></strong></div>
                    <div><span>Liga atual</span><strong><?= e($player['league_name']) ?></strong></div>
                    <div><span>Divisão</span><strong><?= e($player['division_name']) ?></strong></div>
                    <div><span>Temporada</span><strong><?= e($player['current_year']) ?></strong></div>
                </div>
            </article>

            <article class="panel">
                <p class="eyebrow">Rendimento</p>
                <h2>Dados de jogo</h2>
                <div class="player-grid player-info-grid">
                    <div><span>Gols totais</span><strong><?= e($goals) ?></strong></div>
                    <div><span>Assistências totais</span><strong><?= e($assists) ?></strong></div>
                    <div><span>Jogos totais</span><strong><?= e($appearances) ?></strong></div>
                    <div><span>Média de gols</span><strong><?= e(playerAverageStat($goals, $appearances)) ?></strong></div>
                    <div><span>Média de assistências</span><strong><?= e(playerAverageStat($assists, $appearances)) ?></strong></div>
                    <div><span>Nota média por partida</span><strong><?= e(playerAverageRating($player)) ?></strong></div>
                    <div><span>Recorde de gols em uma temporada</span><strong><?= e($goals) ?></strong></div>
                    <div><span>Recorde de assistências</span><strong><?= e($assists) ?></strong></div>
                    <div><span>Recorde de gols em uma partida</span><strong>-</strong></div>
                    <div><span>Quantidade de hat-trick</span><strong>0</strong></div>
                    <div><span>Prêmios de jogador da partida</span><strong>-</strong></div>
                    <div><span>Temporada com melhor nota</span><strong><?= e($bestRatedSeason) ?></strong></div>
                </div>
            </article>

            <article class="panel trophies-panel">
                <p class="eyebrow">Conquistas</p>
                <h2>Troféus ganhos</h2>
                <div class="player-grid player-info-grid">
                    <div class="empty-stat"><span>Troféus</span><strong>Nenhum troféu ganho</strong></div>
                </div>
            </article>
        </section>
    </main>
</body>
</html>

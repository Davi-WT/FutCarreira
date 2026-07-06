<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/game.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'logout') {
        resetCareerData();
        header('Location: index.php');
        exit;
    }
    if ($action === 'accept_offer') {
        acceptOffer((int) ($_POST['offer_id'] ?? 0));
        header('Location: career.php');
        exit;
    }
}

$player = userPlayer();
if (!$player) {
    header('Location: index.php');
    exit;
}

$fixture = nextUserFixture((int) $player['id']);
$standings = leagueStandings((int) $player['division_id']);
$opponent = $fixture ? opponentForFixture($fixture, (int) $player['club_id']) : null;
$opponentPosition = $opponent ? teamLeaguePosition((int) $player['division_id'], (int) $opponent['id']) : null;
$situation = playerMatchSituation($player);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carreira - FutCarreira</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="shell career-shell dashboard-shell">
        <nav class="appbar">
            <a class="brand" href="career.php">FutCarreira</a>
            <div class="appbar-actions">
                <button class="button" type="button" id="theme-toggle">Tema escuro</button>
                <form method="post" onsubmit="return confirm('Sair vai reiniciar a carreira por enquanto. Continuar?')">
                    <input type="hidden" name="action" value="logout">
                    <button class="button danger" type="submit">Sair</button>
                </form>
            </div>
        </nav>

        <section class="career-top-grid">
            <article class="panel career-card">
                <div>
                    <p class="eyebrow">Jogador</p>
                    <h1><?= e($player['name']) ?></h1>
                </div>

                <div class="career-stats">
                    <div><span>Temporada</span><strong><?= e($player['current_year']) ?></strong></div>
                    <div><span>Posição</span><strong><?= e($player['position']) ?></strong></div>
                    <div><span>Time</span><strong><?= e($player['club_name']) ?></strong></div>
                    <div><span>Nacionalidade</span><strong><?= e(countryNameByCode($player['nationality'])) ?></strong></div>
                    <div><span>Gols na temporada</span><strong><?= e($player['goals']) ?></strong></div>
                    <div><span>Assistências na temporada</span><strong><?= e($player['assists']) ?></strong></div>
                    <div><span>Partidas na temporada</span><strong><?= e($player['appearances']) ?></strong></div>
                </div>
            </article>

            <article class="panel ability-card">
                <p class="eyebrow">Overall e habilidades</p>
                <div class="overall-badge">
                    <span>OVR</span>
                    <strong><?= e($player['overall']) ?></strong>
                </div>
                <div class="ability-list">
                    <div><span>Corrida</span><strong><?= e($player['pace']) ?></strong></div>
                    <div><span>Drible</span><strong><?= e($player['dribbling']) ?></strong></div>
                    <div><span>Finalização</span><strong><?= e($player['finishing']) ?></strong></div>
                    <div><span>Defesa</span><strong><?= e($player['defending']) ?></strong></div>
                    <div><span>Passe</span><strong><?= e($player['passing']) ?></strong></div>
                    <div><span>Físico</span><strong><?= e($player['physical']) ?></strong></div>
                </div>
            </article>
        </section>

        <section class="career-columns">
            <article class="panel career-match-panel">
                <h2>Próxima partida</h2>
                <?php if ($fixture && $opponent): ?>
                    <div class="next-match">
                        <span>Rodada <?= e($fixture['round_number']) ?></span>
                        <strong><?= e($player['club_name']) ?> x <?= e($opponent['name']) ?></strong>
                        <p><?= e($opponent['name']) ?> está em <?= e($opponentPosition) ?>º na liga.</p>
                    </div>

                    <div class="match-info-grid">
                        <div><span>Energia</span><strong><?= e($player['stamina']) ?>%</strong></div>
                        <div><span>Situação</span><strong><?= e($situation) ?></strong></div>
                        <div><span>Overall <?= e($player['club_name']) ?></span><strong><?= e(clubOverall((int) $player['club_id'])) ?></strong></div>
                        <div><span>Overall <?= e($opponent['name']) ?></span><strong><?= e(clubOverall((int) $opponent['id'])) ?></strong></div>
                    </div>

                    <?php if ((int) $player['stamina'] < 50): ?>
                        <div class="notice">Energia abaixo de 50%. O jogador ficará no banco e recuperará energia.</div>
                    <?php endif; ?>

                    <div class="actions match-actions">
                        <a class="button primary" href="match.php?id=<?= e($fixture['id']) ?>&mode=watch">Assistir partida</a>
                        <a class="button" href="match.php?id=<?= e($fixture['id']) ?>&mode=skip">Pular partida</a>
                    </div>
                <?php else: ?>
                    <p class="muted">A temporada terminou. Em breve esta tela terá a rotina de fim de temporada.</p>
                <?php endif; ?>
            </article>

            <article class="panel league-panel">
                <h2><?= e($player['league_name']) ?></h2>
                <p class="muted"><?= e($player['division_name']) ?></p>
                <div class="league-table">
                    <?php foreach ($standings as $row): ?>
                        <div class="<?= (int) $row['club_id'] === (int) $player['club_id'] ? 'is-player-team' : '' ?>">
                            <span><?= e($row['position']) ?>º</span>
                            <strong><?= e($row['name']) ?></strong>
                            <em><?= e($row['points']) ?> pts</em>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>
    </main>
    <script>
        const themeToggle = document.querySelector('#theme-toggle');
        const savedTheme = localStorage.getItem('futcarreira-theme');

        if (savedTheme === 'dark') {
            document.body.classList.add('dark-theme');
            themeToggle.textContent = 'Tema claro';
        }

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            const isDark = document.body.classList.contains('dark-theme');
            localStorage.setItem('futcarreira-theme', isDark ? 'dark' : 'light');
            themeToggle.textContent = isDark ? 'Tema claro' : 'Tema escuro';
        });
    </script>
</body>
</html>

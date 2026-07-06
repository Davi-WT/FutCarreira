<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/game.php';

$fixtureId = (int) ($_GET['id'] ?? 0);
$mode = $_GET['mode'] ?? 'watch';
$result = simulateFixture($fixtureId, $mode === 'watch');

if ($mode === 'skip') {
    header('Location: career.php');
    exit;
}

$fixture = $result['fixture'];
$events = $result['events'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partida - FutCarreira</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <script>
        if (localStorage.getItem('futcarreira-theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }
    </script>
    <main class="shell career-shell match-shell">
        <nav class="appbar">
            <a class="brand" href="career.php">FutCarreira</a>
            <div class="appbar-actions">
                <a class="button" href="career.php">Voltar</a>
            </div>
        </nav>

        <?php if ($fixture): ?>
            <section class="panel match-score">
                <p class="eyebrow"><?= $mode === 'watch' ? 'Ao vivo procedural' : 'Resultado final' ?></p>
                <h1><span id="live-home">0</span> x <span id="live-away">0</span></h1>
                <h2><?= e($fixture['home_name']) ?> x <?= e($fixture['away_name']) ?></h2>
                <p id="live-minute">Minuto 0</p>
                <p><?= e($result['player_status']) ?></p>
                <?php if ($mode === 'watch'): ?>
                    <button class="button live-speed" type="button" id="speed-toggle">Acelerar</button>
                <?php endif; ?>
            </section>

            <section class="panel match-news-panel">
                <h2>Notícias da partida</h2>
                <div class="timeline">
                    <?php foreach ($events as $index => $event): ?>
                        <article class="event" data-minute="<?= e($event['minute']) ?>" data-text="<?= e($event['event_text']) ?>" style="<?= $mode === 'watch' ? 'display:none' : '' ?>">
                            <span><?= e($event['minute']) ?>'</span>
                            <p><?= e($event['event_text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <a class="button primary" href="career.php">Continuar</a>
            </section>
            <script>
                const watchMode = <?= json_encode($mode === 'watch') ?>;
                const finalHome = <?= (int) $fixture['home_goals'] ?>;
                const finalAway = <?= (int) $fixture['away_goals'] ?>;
                const eventNodes = [...document.querySelectorAll('.event')];
                const homeScore = document.querySelector('#live-home');
                const awayScore = document.querySelector('#live-away');
                const liveMinute = document.querySelector('#live-minute');
                const speedToggle = document.querySelector('#speed-toggle');
                const defaultSpeed = 1.5;
                const fastSpeed = defaultSpeed * 3;
                let currentSpeed = defaultSpeed;
                let timer = null;

                if (!watchMode) {
                    homeScore.textContent = finalHome;
                    awayScore.textContent = finalAway;
                    liveMinute.textContent = 'Fim de jogo';
                } else {
                    let minute = 0;
                    let home = 0;
                    let away = 0;

                    const playMinute = () => {
                        minute++;
                        liveMinute.textContent = minute < 90 ? `Minuto ${minute}` : 'Fim de jogo';

                        eventNodes
                            .filter((node) => Number(node.dataset.minute) === minute)
                            .forEach((node) => {
                                node.style.display = 'grid';
                                node.scrollIntoView({ block: 'nearest' });
                                const text = node.dataset.text || '';
                                const score = text.match(/Placar: (\d+) x (\d+)/);
                                if (score) {
                                    home = Number(score[1]);
                                    away = Number(score[2]);
                                    homeScore.textContent = home;
                                    awayScore.textContent = away;
                                }
                            });

                        if (minute >= 90) {
                            homeScore.textContent = finalHome;
                            awayScore.textContent = finalAway;
                            clearInterval(timer);
                        }
                    };

                    const startTimer = () => {
                        clearInterval(timer);
                        timer = setInterval(playMinute, Math.round(1000 / currentSpeed));
                    };

                    speedToggle?.addEventListener('click', () => {
                        currentSpeed = currentSpeed === defaultSpeed ? fastSpeed : defaultSpeed;
                        speedToggle.textContent = currentSpeed === defaultSpeed ? 'Acelerar' : 'Velocidade normal';
                        startTimer();
                    });

                    startTimer();
                }
            </script>
        <?php else: ?>
            <section class="panel">
                <h1>Partida não encontrada</h1>
                <a class="button" href="career.php">Voltar para carreira</a>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>

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
}

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
    <main class="shell career-shell match-shell">
        <nav class="appbar">
            <a class="brand" href="career.php">FutCarreira</a>
            <div class="appbar-actions">
                <form method="post" onsubmit="return confirm('Sair vai reiniciar a carreira por enquanto. Continuar?')">
                    <input type="hidden" name="action" value="logout">
                    <button class="button danger" type="submit">Sair</button>
                </form>
            </div>
        </nav>

        <?php if ($fixture): ?>
            <section class="panel match-score">
                <div class="scoreboard">
                    <div class="score-team score-team-home"><?= e($fixture['home_name']) ?></div>
                    <div class="score-center">
                        <strong><span id="live-home">0</span>:<span id="live-away">0</span></strong>
                        <span id="live-minute">Minuto 0</span>
                    </div>
                    <div class="score-team score-team-away"><?= e($fixture['away_name']) ?></div>
                </div>
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
                <div class="match-controls">
                    <div class="half-time-message" id="half-time-message">Intervalo. A partida está pausada.</div>
                    <button class="button" type="button" id="speed-toggle">Acelerar</button>
                    <button class="button" type="button" id="pause-toggle">Pausar</button>
                    <button class="button" type="button" id="skip-toggle">Pular partida</button>
                </div>
            </section>
            <script>
                const watchMode = <?= json_encode($mode === 'watch') ?>;
                const finalHome = <?= (int) $fixture['home_goals'] ?>;
                const finalAway = <?= (int) $fixture['away_goals'] ?>;
                const eventNodes = [...document.querySelectorAll('.event')];
                const homeScore = document.querySelector('#live-home');
                const awayScore = document.querySelector('#live-away');
                const liveMinute = document.querySelector('#live-minute');
                const halfTimeMessage = document.querySelector('#half-time-message');
                const speedToggle = document.querySelector('#speed-toggle');
                const pauseToggle = document.querySelector('#pause-toggle');
                const skipToggle = document.querySelector('#skip-toggle');
                const defaultSpeed = 1.5;
                const fastSpeed = defaultSpeed * 3;
                let currentSpeed = defaultSpeed;
                let timer = null;
                let minute = 0;
                let home = 0;
                let away = 0;
                let finished = false;
                let paused = false;
                let pausedAtHalfTime = false;

                if (!watchMode) {
                    homeScore.textContent = finalHome;
                    awayScore.textContent = finalAway;
                    liveMinute.textContent = 'Fim de jogo';
                    finished = true;
                    pauseToggle.textContent = 'Continuar';
                } else {
                    const playMinute = () => {
                        minute++;
                        paused = false;
                        pauseToggle.classList.remove('is-active');
                        pauseToggle.textContent = 'Pausar';
                        halfTimeMessage.classList.remove('is-visible');
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
                            finished = true;
                            pauseToggle.textContent = 'Continuar';
                            pauseToggle.classList.remove('is-active');
                            clearInterval(timer);
                            return;
                        }

                        if (minute === 45 && !pausedAtHalfTime) {
                            pausedAtHalfTime = true;
                            paused = true;
                            liveMinute.textContent = 'Intervalo';
                            pauseToggle.textContent = 'Continuar';
                            pauseToggle.classList.add('is-active');
                            halfTimeMessage.classList.add('is-visible');
                            clearInterval(timer);
                        }
                    };

                    const startTimer = () => {
                        clearInterval(timer);
                        timer = setInterval(playMinute, Math.round(1000 / currentSpeed));
                    };

                    speedToggle?.addEventListener('click', () => {
                        currentSpeed = currentSpeed === defaultSpeed ? fastSpeed : defaultSpeed;
                        speedToggle.classList.toggle('is-active', currentSpeed !== defaultSpeed);
                        if (!finished) {
                            startTimer();
                        }
                    });

                    pauseToggle?.addEventListener('click', () => {
                        if (finished) {
                            window.location.href = 'career.php';
                            return;
                        }

                        if (paused) {
                            paused = false;
                            pauseToggle.textContent = 'Pausar';
                            pauseToggle.classList.remove('is-active');
                            halfTimeMessage.classList.remove('is-visible');
                            startTimer();
                            return;
                        }

                        paused = true;
                        pauseToggle.textContent = 'Continuar';
                        pauseToggle.classList.add('is-active');
                        clearInterval(timer);
                    });

                    skipToggle?.addEventListener('click', () => {
                        clearInterval(timer);
                        minute = 90;
                        finished = true;
                        homeScore.textContent = finalHome;
                        awayScore.textContent = finalAway;
                        liveMinute.textContent = 'Fim de jogo';
                        pauseToggle.textContent = 'Continuar';
                        pauseToggle.classList.remove('is-active');
                        eventNodes.forEach((node) => {
                            node.style.display = 'grid';
                        });
                        window.location.href = 'career.php';
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

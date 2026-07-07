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
$preMatchPlayer = userPlayer();
$result = simulateFixture($fixtureId, $mode === 'watch');

if ($mode === 'skip') {
    header('Location: career.php');
    exit;
}

$fixture = $result['fixture'];
$events = $result['events'];
$playerMatch = $result['player_match'] ?? null;
$minuteStates = $result['minute_states'] ?? [];
$displayStamina = $preMatchPlayer ? (int) $preMatchPlayer['stamina'] : null;
$playerIsHome = $preMatchPlayer && $fixture && (int) $fixture['home_club_id'] === (int) $preMatchPlayer['club_id'];
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
                <div class="match-side-info">
                    <div class="match-player-note">
                        <span>Nota</span>
                        <strong id="live-rating"><?= $playerMatch && $playerMatch['rating'] !== null ? '6,0' : '-' ?></strong>
                    </div>
                    <div class="match-reserved">
                        <div class="dominance-bar <?= $playerIsHome ? 'player-left' : 'player-right' ?>">
                            <div class="dominance-fill player" id="dominance-player"></div>
                            <div class="dominance-fill opponent" id="dominance-opponent"></div>
                        </div>
                    </div>
                    <div class="match-player-stamina">
                        <span>Estamina</span>
                        <strong id="live-stamina"><?= $displayStamina !== null ? e($displayStamina) . '%' : '-' ?></strong>
                    </div>
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
                const matchStates = <?= json_encode($minuteStates, JSON_UNESCAPED_UNICODE) ?>;
                const eventNodes = [...document.querySelectorAll('.event')];
                const homeScore = document.querySelector('#live-home');
                const awayScore = document.querySelector('#live-away');
                const liveMinute = document.querySelector('#live-minute');
                const liveRating = document.querySelector('#live-rating');
                const liveStamina = document.querySelector('#live-stamina');
                const dominancePlayer = document.querySelector('#dominance-player');
                const dominanceOpponent = document.querySelector('#dominance-opponent');
                const playerIsHome = <?= json_encode($playerIsHome) ?>;
                const halfTimeMessage = document.querySelector('#half-time-message');
                const speedToggle = document.querySelector('#speed-toggle');
                const pauseToggle = document.querySelector('#pause-toggle');
                const skipToggle = document.querySelector('#skip-toggle');
                const defaultSpeed = 1.5;
                const fastSpeed = defaultSpeed * 4.5;
                let currentSpeed = defaultSpeed;
                let timer = null;
                let minute = 0;
                let home = 0;
                let away = 0;
                let finished = false;
                let paused = false;
                let pausedAtHalfTime = false;

                const formatRating = (rating) => {
                    if (rating === null || rating === undefined) {
                        return '-';
                    }

                    return Number(rating).toFixed(1).replace('.', ',');
                };

                const applyState = (state) => {
                    if (!state) {
                        return;
                    }

                    homeScore.textContent = state.home;
                    awayScore.textContent = state.away;
                    liveRating.textContent = formatRating(state.rating);
                    if (state.stamina !== null && state.stamina !== undefined) {
                        liveStamina.textContent = `${state.stamina}%`;
                    }

                    const dominance = Math.max(-100, Math.min(100, Number(state.dominance || 0)));
                    const homeShare = 50 + (dominance * 0.45);
                    const playerShare = playerIsHome ? homeShare : 100 - homeShare;
                    dominancePlayer.style.width = `${playerShare}%`;
                    dominanceOpponent.style.width = `${100 - playerShare}%`;
                };

                if (!watchMode) {
                    applyState(matchStates[90]);
                    if (!matchStates[90]) {
                        homeScore.textContent = finalHome;
                        awayScore.textContent = finalAway;
                    }
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
                        applyState(matchStates[minute]);

                        eventNodes
                            .filter((node) => Number(node.dataset.minute) === minute)
                            .forEach((node) => {
                                node.style.display = 'grid';
                                node.scrollIntoView({ block: 'nearest' });
                                const text = node.dataset.text || '';
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
                        applyState(matchStates[90]);
                        if (!matchStates[90]) {
                            homeScore.textContent = finalHome;
                            awayScore.textContent = finalAway;
                        }
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

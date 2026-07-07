<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/game.php';

$player = null;
$error = null;

try {
    $player = userPlayer();
} catch (Throwable $exception) {
    $error = 'Banco não encontrado. Importe primeiro o arquivo db/001_initial_schema.sql no MySQL.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FutCarreira</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="shell">
        <section class="hero">
            <div>
                <p class="eyebrow">Modo carreira em PHP</p>
                <h1>FutCarreira</h1>
                <p>Crie seu jogador, escolha onde ele começa e acompanhe uma carreira com partidas, banco, lesões, evolução e transferências.</p>
            </div>
            <div class="score-card">
                <span>Temporada</span>
                <strong><?= $player ? e($player['current_year']) : '2026' ?></strong>
                <small><?= $player ? e($player['club_name']) : 'Sem jogador ativo' ?></small>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="notice danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <section class="panel">
                <h2>Como iniciar</h2>
                <p class="muted">Abra o phpMyAdmin ou o cliente MySQL e execute o conteúdo de <strong>db/001_initial_schema.sql</strong>. Depois atualize esta página.</p>
            </section>
        <?php elseif ($player): ?>
            <section class="panel">
                <h2>Carreira ativa</h2>
                <div class="player-grid">
                    <div><span>Jogador</span><strong><?= e($player['name']) ?></strong></div>
                    <div><span>Posição</span><strong><?= e($player['position']) ?></strong></div>
                    <div><span>Overall</span><strong><?= e($player['overall']) ?></strong></div>
                    <div><span>Clube</span><strong><?= e($player['club_name']) ?></strong></div>
                </div>
                <a class="button primary" href="career.php">Continuar carreira</a>
            </section>
        <?php else: ?>
            <section class="panel">
                <h2>Criar jogador</h2>
                <form class="form" action="create_player.php" method="post">
                    <label>
                        Nome
                        <input name="name" required maxlength="120" placeholder="Ex: João Silva">
                    </label>
                    <label>
                        Posição
                        <select name="position" required>
                            <?php foreach (positions() as $position): ?>
                                <option value="<?= e($position) ?>"><?= e($position) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Nacionalidade
                        <select name="nationality" required>
                            <?php foreach (countriesWithSecondDivision() as $country): ?>
                                <option value="<?= e($country['code']) ?>"><?= e($country['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Perna dominante
                        <select name="dominant_foot" required>
                            <?php foreach (dominantFeet() as $foot): ?>
                                <option value="<?= e($foot) ?>"><?= e($foot) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Altura (cm)
                        <input type="number" name="height_cm" required min="140" max="220" value="175">
                    </label>
                    <label>
                        Peso (kg)
                        <input type="number" name="weight_kg" required min="45" max="130" value="70">
                    </label>
                    <label>
                        País onde vai começar
                        <select name="start_country" required>
                            <?php foreach (countriesWithSecondDivision() as $country): ?>
                                <option value="<?= e($country['code']) ?>"><?= e($country['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="button primary" type="submit">Começar na segunda divisão</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>

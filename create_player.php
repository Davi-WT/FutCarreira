<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/game.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$position = (string) ($_POST['position'] ?? '');
$nationality = (string) ($_POST['nationality'] ?? '');

if ($name === '') {
    header('Location: index.php');
    exit;
}

createCareerPlayer($name, $position, $nationality);

header('Location: career.php');
exit;

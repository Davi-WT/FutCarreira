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
$dominantFoot = (string) ($_POST['dominant_foot'] ?? '');
$heightCm = (int) ($_POST['height_cm'] ?? 0);
$weightKg = (int) ($_POST['weight_kg'] ?? 0);
$startCountry = (string) ($_POST['start_country'] ?? '');

if ($name === '') {
    header('Location: index.php');
    exit;
}

createCareerPlayer($name, $position, $nationality, $dominantFoot, $heightCm, $weightKg, $startCountry);

header('Location: career.php');
exit;

<?php
$pageTitle = $pageTitle ?? 'Bibliotech';
$appBaseUrl = '/Bibliotech';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> - Bibliotech</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css">
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten</a>
    <div class="app-shell">
        <?php require __DIR__ . '/sidebar.php'; ?>
        <div class="app-main">

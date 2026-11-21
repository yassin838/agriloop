<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

$status = 'error';
$message = '';
$details = [];

try {
    $pdo = Database::getConnexion();
    $serverVersion = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

    $status = 'success';
    $message = 'Connexion MySQL reussie.';
    $details = [
        'base' => $database,
        'serveur' => $serverVersion,
        'tables' => $tables,
    ];
} catch (Throwable $exception) {
    $message = 'Impossible de se connecter a MySQL.';
    $details = [
        'message_technique' => $exception->getMessage(),
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test de connexion AgriLoop</title>
    <link rel="stylesheet" href="frontoffice/styles.css">
    <link rel="stylesheet" href="frontoffice/freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="../index.php"><span class="brand-mark"></span>AgriLoop</a>
    </header>
    <main class="content">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Diagnostic MySQL</p>
                <h1>Test de connexion</h1>
                <p class="subtitle"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <div class="alert <?= $status ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <?php foreach ($details as $label => $value): ?>
            <div class="card" style="padding:18px;margin-bottom:12px;">
                <strong><?= htmlspecialchars(str_replace('_', ' ', $label), ENT_QUOTES, 'UTF-8') ?> :</strong>
                <?php if (is_array($value)): ?>
                    <?= htmlspecialchars(implode(', ', $value), ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                    <?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>

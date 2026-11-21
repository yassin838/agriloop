<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Ce script d'installation doit etre execute en ligne de commande.\n");
    exit(1);
}

$dsn = 'mysql:host=127.0.0.1;charset=utf8mb4';

try {
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $sql = file_get_contents(__DIR__ . '/agriloop.sql');
    if ($sql === false) {
        throw new RuntimeException('Impossible de lire agriloop.sql.');
    }

    $pdo->exec($sql);
    echo "Base agriloop installee avec succes.\n";
    echo "Tables: association, utilisateur, don.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Erreur: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

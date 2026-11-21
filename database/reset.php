<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Ce script doit etre execute en ligne de commande.\n");
    exit(1);
}

$host = getenv('AGRILOOP_DB_HOST') ?: '127.0.0.1';
$port = getenv('AGRILOOP_DB_PORT') ?: '3306';
$username = getenv('AGRILOOP_DB_USER') ?: 'root';
$password = getenv('AGRILOOP_DB_PASSWORD') ?: '';
$databaseName = getenv('AGRILOOP_DB_NAME') ?: 'agriloop';

echo "Vous allez supprimer et recreer la base '{$databaseName}'.\n";
echo "Toutes les donnees actuelles de cette base seront perdues.\n";
echo "Tapez RESET pour confirmer : ";
$answer = trim((string) fgets(STDIN));

if ($answer !== 'RESET') {
    echo "Operation annulee.\n";
    exit(0);
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $databaseName) ?: 'agriloop';
    $pdo->exec("DROP DATABASE IF EXISTS `{$safeName}`");
    $sql = file_get_contents(__DIR__ . '/agriloop.sql');
    if ($sql === false) {
        throw new RuntimeException('Impossible de lire database/agriloop.sql.');
    }

    $pdo->exec($sql);

    $accounts = [
        'acheteur' => ['Acheteur', 'Demo', 'acheteur@agriloop.tn', 'Acheteur123!', '22111111', 'Tunis'],
        'agriculteur' => ['Agriculteur', 'Demo', 'agriculteur@agriloop.tn', 'Farmer123!', '22222222', 'Sousse'],
        'administrateur' => ['Admin', 'AgriLoop', 'admin.agriloop@agriloop.tn', 'AgriloopAdmin2026!', '23333333', 'Tunis'],
    ];

    foreach ($accounts as $role => $account) {
        $userStmt = $pdo->prepare(
            'INSERT INTO utilisateur
             (nom, prenom, email, mot_de_passe, telephone, ville, role)
             VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :ville, :role)'
        );
        $userStmt->execute([
            'nom' => $account[0],
            'prenom' => $account[1],
            'email' => $account[2],
            'mot_de_passe' => password_hash($account[3], PASSWORD_DEFAULT),
            'telephone' => $account[4],
            'ville' => $account[5],
            'role' => $role,
        ]);
    }

    echo "Base '{$safeName}' recreee avec succes.\n";
    echo "Table : utilisateur.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Erreur: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

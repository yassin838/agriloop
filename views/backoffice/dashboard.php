<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'administrateur') {
    header('Location: ../frontoffice/login.php');
    exit;
}

require_once __DIR__ . '/../../controller/AssociationController.php';
require_once __DIR__ . '/../../controller/DonController.php';

$associationController = new AssociationController(Association::createWithDatabase());
$donController = new DonController(Don::createWithDatabase());

$associations = $associationController->index();
$dons = $donController->index();
$stats = (new Don(Database::getConnexion()))->stats();

function eDashboard(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord | AgriLoop</title>
    <link rel="stylesheet" href="../frontoffice/styles.css">
    <link rel="stylesheet" href="../frontoffice/freshcart-template.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="dashboard.php"><span class="brand-mark"></span><span>AgriLoop<small>Administration</small></span></a>
        <nav aria-label="Navigation du back-office">
            <p class="nav-title">Tableau de bord</p>
            <a class="admin-nav-link active" href="dashboard.php">Vue generale</a>
            <a class="admin-nav-link" href="utilisateurs.php">Utilisateurs</a>
            <p class="nav-title">Gestion Yassine Amri</p>
            <a class="admin-nav-link" href="associations.php">Associations <span><?= count($associations) ?></span></a>
            <a class="admin-nav-link" href="dons.php">Dons <span><?= (int) ($stats['total'] ?? 0) ?></span></a>
            <p class="nav-title">Compte</p>
            <a class="admin-nav-link" href="../frontoffice/logout.php">Deconnexion</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="eyebrow">Module administrateur Yassine Amri</p>
                <h1>Tableau de bord</h1>
            </div>
            <a class="button secondary" href="../../index.php">Retour au site</a>
        </header>

        <section class="admin-content">
            <div class="page-heading">
                <div>
                    <p class="eyebrow">Administration</p>
                    <h2>Gestion du module Dons et Associations</h2>
                    <p class="subtitle">Accedez aux deux sections reservees a l'administrateur.</p>
                </div>
            </div>

            <div class="stats-grid">
                <article class="stat-card"><p>Total des associations</p><strong><?= count($associations) ?></strong></article>
                <article class="stat-card"><p>Associations en attente</p><strong><?= count(array_filter($associations, static fn(array $a): bool => $a['statut'] === 'en_attente')) ?></strong></article>
                <article class="stat-card"><p>Total des dons</p><strong><?= (int) ($stats['total'] ?? 0) ?></strong></article>
                <article class="stat-card"><p>Dons en attente</p><strong><?= (int) ($stats['en_attente'] ?? 0) ?></strong></article>
            </div>

            <section class="grid-layout">
                <article class="card form-card">
                    <div class="card-heading">
                        <span class="card-icon">U</span>
                        <div><p class="eyebrow">Administration</p><h2>Utilisateurs</h2></div>
                    </div>
                    <p>Créer, consulter, modifier les rôles et supprimer les utilisateurs.</p>
                    <a class="button" href="utilisateurs.php">Gerer les utilisateurs</a>
                </article>

                <article class="card form-card">
                    <div class="card-heading">
                        <span class="card-icon">A</span>
                        <div><p class="eyebrow">Administration</p><h2>Associations</h2></div>
                    </div>
                    <p>Accepter, modifier ou supprimer les demandes d'association des acheteurs.</p>
                    <a class="button" href="associations.php">Gerer les associations</a>
                </article>

                <article class="card form-card">
                    <div class="card-heading">
                        <span class="card-icon">D</span>
                        <div><p class="eyebrow">Administration</p><h2>Dons</h2></div>
                    </div>
                    <p>Consulter, confirmer, annuler, modifier ou supprimer les dons des agriculteurs.</p>
                    <a class="button" href="dons.php">Gerer les dons</a>
                </article>
            </section>
        </section>
    </main>
</div>
</body>
</html>

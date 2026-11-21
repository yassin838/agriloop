<?php

declare(strict_types=1);

require_once __DIR__ . '/config/session.php';
$currentUser = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriLoop | Accueil</title>
    <link rel="stylesheet" href="views/frontoffice/styles.css">
    <link rel="stylesheet" href="views/frontoffice/freshcart-template.css">
</head>
<body>
<div class="page page-wide">
    <header class="site-header">
        <a class="brand" href="index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav" aria-label="Navigation principale">
            <a href="index.php" aria-current="page">Accueil</a>
            <a href="#offres">Offres</a>
            <a href="views/frontoffice/don.php">Dons</a>
            <a href="#parcelles">Parcelles</a>
            <a href="#avis">Avis</a>
            <?php if ($currentUser): ?>
                <a href="views/frontoffice/logout.php">Deconnexion (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</a>
            <?php else: ?>
                <a href="views/frontoffice/login.php">Connexion</a>
                <a href="views/frontoffice/register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="fo-layout">
        <aside class="fo-sidebar" aria-label="Menu utilisateur">
            <p>ESPACE UTILISATEUR</p>
            <a class="active" href="index.php">Accueil</a>
            <a href="views/frontoffice/association.php">Associations</a>
            <a href="views/frontoffice/don.php">Dons</a>
            <?php if ($currentUser): ?>
                <?php if (($currentUser['role'] ?? '') === 'administrateur'): ?>
                    <a href="views/backoffice/dashboard.php">Back-office</a>
                <?php endif; ?>
                <a href="views/frontoffice/profil.php">Mon profil</a>
                <a href="views/frontoffice/logout.php">Deconnexion</a>
            <?php else: ?>
                <a href="views/frontoffice/register.php">Inscription</a>
                <a href="views/frontoffice/login.php">Connexion</a>
            <?php endif; ?>
        </aside>
        <main class="content fo-main">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Plateforme solidaire</p>
                <h1>AgriLoop</h1>
                <p class="subtitle">Gestion des associations, des offres et des dons agricoles.</p>
            </div>
        </div>
        <section class="grid-layout">
            <article class="card form-card">
                <div class="card-heading"><span class="card-icon">A</span><div><p class="eyebrow">Partenaires</p><h2>Associations</h2></div></div>
                <p>Créez, consultez, modifiez et supprimez les associations partenaires.</p>
                <a class="button" href="views/frontoffice/association.php">Ouvrir le module</a>
            </article>
            <article class="card form-card">
                <div class="card-heading"><span class="card-icon">D</span><div><p class="eyebrow">Solidarité</p><h2>Dons</h2></div></div>
                <p>Proposez un don lié à une association et à une offre.</p>
                <a class="button" href="views/frontoffice/don.php">Ouvrir le module</a>
            </article>
        </section>
        </main>
    </div>
</div>
</body>
</html>

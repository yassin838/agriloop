<?php
declare(strict_types=1);
session_start();
$currentUser = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriLoop | Accueil commun</title>
    <link rel="stylesheet" href="views/frontoffice/styles.css">
    <link rel="stylesheet" href="views/frontoffice/freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav">
            <a href="index.php" aria-current="page">Accueil</a>
            <?php if ($currentUser): ?>
                <a href="views/frontoffice/logout.php">Deconnexion (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</a>
            <?php else: ?>
                <a href="views/frontoffice/login.php">Connexion</a>
                <a href="views/frontoffice/register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="content fo-main">
        <div class="page-heading">
            <div><p class="eyebrow">Socle commun</p><h1>AgriLoop</h1><p class="subtitle">Template, inscription, authentification et roles utilisateurs.</p></div>
        </div>
        <?php if (!$currentUser): ?>
            <section class="grid-layout">
                <article class="card form-card"><div class="card-heading"><span class="card-icon">I</span><div><p class="eyebrow">Compte</p><h2>Inscription</h2></div></div><p>Creer un compte Agriculteur ou Acheteur.</p><a class="button" href="views/frontoffice/register.php">S'inscrire</a></article>
                <article class="card form-card"><div class="card-heading"><span class="card-icon">C</span><div><p class="eyebrow">Compte</p><h2>Connexion</h2></div></div><p>S'authentifier avec votre email et votre mot de passe.</p><a class="button" href="views/frontoffice/login.php">Se connecter</a></article>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../controller/UtilisateurController.php';

$controller = new UtilisateurController(Utilisateur::createWithDatabase());
$errors = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['csrf'] = 'Session invalide. Rechargez la page.';
    } else {
        $result = $controller->login(
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['mot_de_passe'] ?? '')
        );
        if ($result['success']) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $result['user']['id'],
                'nom' => $result['user']['nom'],
                'prenom' => $result['user']['prenom'],
                'email' => $result['user']['email'],
                'role' => $result['user']['role'],
            ];

            if ($result['user']['role'] === 'administrateur') {
                header('Location: ../backoffice/dashboard.php');
            } else {
                header('Location: ../../index.php');
            }
            exit;
        }
        $errors = $result['errors'];
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eLogin(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion | AgriLoop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="../../index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav"><a href="../../index.php">Accueil</a><a href="register.php">Inscription</a></nav>
    </header>
    <div class="fo-layout">
        <aside class="fo-sidebar" aria-label="Menu utilisateur">
            <p>ESPACE UTILISATEUR</p>
            <a href="../../index.php">Accueil</a>
            <a href="association.php">Associations</a>
            <a href="don.php">Dons</a>
            <a href="register.php">Inscription</a>
            <a class="active" href="login.php">Connexion</a>
        </aside>
        <main class="content fo-main">
        <div class="page-heading"><div><p class="eyebrow">Acces utilisateur</p><h1>Connexion</h1><p class="subtitle">Connectez-vous pour utiliser AgriLoop.</p></div></div>
        <?php if ($flash): ?><div class="alert <?= eLogin($flash['type']) ?>"><?= eLogin($flash['message']) ?></div><?php endif; ?>
        <?php if (isset($errors['identifiants'])): ?><div class="alert error"><?= eLogin($errors['identifiants']) ?></div><?php endif; ?>
        <?php if (isset($errors['csrf'])): ?><div class="alert error"><?= eLogin($errors['csrf']) ?></div><?php endif; ?>
        <article class="card" style="max-width:520px;padding:24px;">
            <form class="form-grid" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= eLogin($_SESSION['csrf_token']) ?>">
                <div class="field field-full"><label for="email">Email *</label><input id="email" name="email" type="email" autocomplete="email" required></div>
                <div class="field field-full"><label for="mot_de_passe">Mot de passe *</label><input id="mot_de_passe" name="mot_de_passe" type="password" autocomplete="current-password" required></div>
                <div class="form-actions field-full"><button type="submit" class="button">Se connecter</button></div>
            </form>
        </article>
        </main>
    </div>
</div>
</body>
</html>

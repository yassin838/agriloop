<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../controller/UtilisateurController.php';

$controller = new UtilisateurController(Utilisateur::createWithDatabase());
$errors = [];
$old = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'telephone' => '',
    'ville' => '',
    'role' => '',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['csrf'] = 'Session invalide. Rechargez la page.';
    } else {
        $old = array_intersect_key($_POST, $old);
        $result = $controller->register($_POST);
        if ($result['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Inscription reussie. Connectez-vous.'];
            header('Location: login.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

function eRegister(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription | AgriLoop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="../../index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav"><a href="../../index.php">Accueil</a><a href="login.php">Connexion</a></nav>
    </header>
    <div class="fo-layout">
        <aside class="fo-sidebar" aria-label="Menu utilisateur">
            <p>ESPACE UTILISATEUR</p>
            <a href="../../index.php">Accueil</a>
            <a href="association.php">Associations</a>
            <a href="don.php">Dons</a>
            <a class="active" href="register.php">Inscription</a>
            <a href="login.php">Connexion</a>
        </aside>
        <main class="content fo-main">
        <div class="page-heading">
            <div><p class="eyebrow">Compte utilisateur</p><h1>Creer un compte</h1><p class="subtitle">Choisissez entre agriculteur ou acheteur.</p></div>
        </div>
        <?php if (isset($errors['csrf'])): ?><div class="alert error"><?= eRegister($errors['csrf']) ?></div><?php endif; ?>
        <article class="card" style="max-width:900px;padding:24px;">
            <form class="form-grid" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= eRegister($_SESSION['csrf_token']) ?>">
                <div class="field"><label for="nom">Nom *</label><input id="nom" name="nom" minlength="2" maxlength="100" required value="<?= eRegister($old['nom']) ?>"><?php if (isset($errors['nom'])): ?><small class="field-error"><?= eRegister($errors['nom']) ?></small><?php endif; ?></div>
                <div class="field"><label for="prenom">Prenom *</label><input id="prenom" name="prenom" minlength="2" maxlength="100" required value="<?= eRegister($old['prenom']) ?>"><?php if (isset($errors['prenom'])): ?><small class="field-error"><?= eRegister($errors['prenom']) ?></small><?php endif; ?></div>
                <div class="field"><label for="email">Email *</label><input id="email" name="email" type="email" maxlength="180" required value="<?= eRegister($old['email']) ?>"><?php if (isset($errors['email'])): ?><small class="field-error"><?= eRegister($errors['email']) ?></small><?php endif; ?></div>
                <div class="field"><label for="telephone">Telephone *</label><input id="telephone" name="telephone" type="tel" required value="<?= eRegister($old['telephone']) ?>"><?php if (isset($errors['telephone'])): ?><small class="field-error"><?= eRegister($errors['telephone']) ?></small><?php endif; ?></div>
                <div class="field"><label for="ville">Ville *</label><input id="ville" name="ville" maxlength="100" required value="<?= eRegister($old['ville']) ?>"><?php if (isset($errors['ville'])): ?><small class="field-error"><?= eRegister($errors['ville']) ?></small><?php endif; ?></div>
                <div class="field"><label for="role">Role *</label><select id="role" name="role" required><option value="" disabled <?= $old['role'] === '' ? 'selected' : '' ?>>Choisir...</option><option value="agriculteur" <?= $old['role'] === 'agriculteur' ? 'selected' : '' ?>>Agriculteur</option><option value="acheteur" <?= $old['role'] === 'acheteur' ? 'selected' : '' ?>>Acheteur</option></select><?php if (isset($errors['role'])): ?><small class="field-error"><?= eRegister($errors['role']) ?></small><?php endif; ?></div>
                <div class="field"><label for="mot_de_passe">Mot de passe *</label><input id="mot_de_passe" name="mot_de_passe" type="password" minlength="8" required><?php if (isset($errors['mot_de_passe'])): ?><small class="field-error"><?= eRegister($errors['mot_de_passe']) ?></small><?php endif; ?></div>
                <div class="field"><label for="mot_de_passe_confirmation">Confirmer le mot de passe *</label><input id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" type="password" minlength="8" required><?php if (isset($errors['mot_de_passe_confirmation'])): ?><small class="field-error"><?= eRegister($errors['mot_de_passe_confirmation']) ?></small><?php endif; ?></div>
                <div class="form-actions field-full"><button type="reset" class="button neutral">Effacer</button><button type="submit" class="button">S'inscrire</button></div>
            </form>
        </article>
        </main>
    </div>
</div>
</body>
</html>

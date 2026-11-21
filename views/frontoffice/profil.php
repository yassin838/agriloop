<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../controller/UtilisateurController.php';
require_once __DIR__ . '/../../model/Utilisateur.php';

$controller = new UtilisateurController(Utilisateur::createWithDatabase());
$id = (int) $_SESSION['user']['id'];
$errors = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['csrf'] = 'Session invalide. Rechargez la page.';
    } else {
        $result = $controller->updateProfile($id, $_POST);
        if ($result['success']) {
            $updated = $controller->find($id);
            if ($updated) {
                $_SESSION['user']['nom'] = $updated['nom'];
                $_SESSION['user']['prenom'] = $updated['prenom'];
                $_SESSION['user']['email'] = $updated['email'];
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Votre profil a ete modifie.'];
            header('Location: profil.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

$user = $controller->find($id);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eProfil(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil | AgriLoop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="../../index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav">
            <a href="../../index.php">Accueil</a>
            <a class="active" href="profil.php" aria-current="page">Mon profil</a>
            <a href="logout.php">Deconnexion</a>
        </nav>
    </header>
    <div class="fo-layout">
        <aside class="fo-sidebar" aria-label="Menu utilisateur">
            <p>ESPACE UTILISATEUR</p>
            <a href="../../index.php">Accueil</a>
            <a href="association.php">Associations</a>
            <a href="don.php">Dons</a>
            <a class="active" href="profil.php">Mon profil</a>
            <?php if (($_SESSION['user']['role'] ?? '') === 'administrateur'): ?>
                <a href="../backoffice/dashboard.php">Back-office</a>
            <?php endif; ?>
        </aside>
        <main class="content fo-main">
            <div class="page-heading"><div><p class="eyebrow">Compte personnel</p><h1>Mon profil</h1><p class="subtitle">Fonction metier : modifier mes informations et mon mot de passe.</p></div></div>
            <?php if ($flash): ?><div class="alert <?= eProfil($flash['type']) ?>"><?= eProfil($flash['message']) ?></div><?php endif; ?>
            <?php if (isset($errors['csrf'])): ?><div class="alert error"><?= eProfil($errors['csrf']) ?></div><?php endif; ?>
            <?php if (!$user): ?><div class="alert error">Compte introuvable.</div><?php else: ?>
            <article class="card" style="max-width:900px;padding:24px;">
                <form class="form-grid" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= eProfil($_SESSION['csrf_token']) ?>">
                    <div class="field"><label for="nom">Nom *</label><input id="nom" name="nom" maxlength="100" required value="<?= eProfil($user['nom']) ?>"><?php if (isset($errors['nom'])): ?><small class="field-error"><?= eProfil($errors['nom']) ?></small><?php endif; ?></div>
                    <div class="field"><label for="prenom">Prenom *</label><input id="prenom" name="prenom" maxlength="100" required value="<?= eProfil($user['prenom']) ?>"><?php if (isset($errors['prenom'])): ?><small class="field-error"><?= eProfil($errors['prenom']) ?></small><?php endif; ?></div>
                    <div class="field"><label for="email">Email *</label><input id="email" name="email" type="email" maxlength="180" required value="<?= eProfil($user['email']) ?>"><?php if (isset($errors['email'])): ?><small class="field-error"><?= eProfil($errors['email']) ?></small><?php endif; ?></div>
                    <div class="field"><label for="telephone">Telephone *</label><input id="telephone" name="telephone" type="tel" maxlength="30" required value="<?= eProfil($user['telephone']) ?>"><?php if (isset($errors['telephone'])): ?><small class="field-error"><?= eProfil($errors['telephone']) ?></small><?php endif; ?></div>
                    <div class="field"><label for="ville">Ville *</label><input id="ville" name="ville" maxlength="100" required value="<?= eProfil($user['ville']) ?>"><?php if (isset($errors['ville'])): ?><small class="field-error"><?= eProfil($errors['ville']) ?></small><?php endif; ?></div>
                    <div class="field"><label>Role</label><input value="<?= eProfil($user['role']) ?>" disabled></div>
                    <div class="field"><label for="mot_de_passe">Nouveau mot de passe</label><input id="mot_de_passe" name="mot_de_passe" type="password" minlength="8" autocomplete="new-password"><?php if (isset($errors['mot_de_passe'])): ?><small class="field-error"><?= eProfil($errors['mot_de_passe']) ?></small><?php endif; ?></div>
                    <div class="field"><label for="mot_de_passe_confirmation">Confirmation</label><input id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" type="password" minlength="8" autocomplete="new-password"><?php if (isset($errors['mot_de_passe_confirmation'])): ?><small class="field-error"><?= eProfil($errors['mot_de_passe_confirmation']) ?></small><?php endif; ?></div>
                    <div class="form-actions field-full"><button type="submit" class="button">Enregistrer mon profil</button></div>
                </form>
            </article>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>

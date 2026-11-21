<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../controller/AssociationController.php';

$currentUser = $_SESSION['user'] ?? null;
$canRegisterAssociation = $currentUser !== null
    && ($currentUser['role'] ?? '') === 'acheteur';

$controller = new AssociationController(Association::createWithDatabase());
$errors = [];
$old = [
    'nom' => '',
    'domaine' => '',
    'ville' => '',
    'telephone' => '',
    'email' => '',
    'responsable' => '',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canRegisterAssociation) {
        $errors['authorization'] = 'Seuls les utilisateurs avec le role acheteur peuvent enregistrer une association.';
    } elseif (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['csrf'] = 'Session invalide. Rechargez la page.';
    } elseif ((string) ($_POST['action'] ?? '') === 'create') {
        $old = array_intersect_key($_POST, $old);
        $_POST['id_user'] = (int) $currentUser['id'];
        $result = $controller->store($_POST);

        if ($result['success']) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Votre association a ete enregistree. Elle sera validee par l\'administrateur.',
            ];
            header('Location: association.php');
            exit;
        }

        $errors = $result['errors'];
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eAssociation(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enregistrer une association | AgriLoop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="freshcart-template.css">
</head>
<body>
<div class="page">
    <header class="site-header">
        <a class="brand" href="../../index.php"><span class="brand-mark"></span>AgriLoop</a>
        <nav class="main-nav" aria-label="Navigation utilisateur">
            <a href="../../index.php">Accueil</a>
            <a href="#offres">Offres</a>
            <a href="don.php">Dons</a>
            <a href="#parcelles">Parcelles</a>
            <a href="#avis">Avis</a>
            <?php if ($currentUser): ?>
                <a href="logout.php">Deconnexion (<?= eAssociation($currentUser['role']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="fo-layout">
        <aside class="fo-sidebar" aria-label="Menu utilisateur">
            <p>ESPACE UTILISATEUR</p>
            <a href="../../index.php">Accueil</a>
            <a class="active" href="association.php">Associations</a>
            <a href="don.php">Dons</a>
            <a href="register.php">Inscription</a>
            <?php if ($currentUser): ?>
                <a href="logout.php">Deconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
            <?php endif; ?>
        </aside>
        <main class="content fo-main">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Demande partenaire</p>
                <h1>Enregistrer une association</h1>
                <p class="subtitle">Les informations seront examinees par l'administrateur.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= eAssociation($flash['type']) ?>" role="alert">
                <?= eAssociation($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['csrf'])): ?>
            <div class="alert error" role="alert"><?= eAssociation($errors['csrf']) ?></div>
        <?php endif; ?>

        <?php if (!$canRegisterAssociation): ?>
            <div class="alert error" role="alert">
                <?= isset($errors['authorization'])
                    ? eAssociation($errors['authorization'])
                    : 'Connectez-vous avec le role acheteur pour enregistrer une association.' ?>
            </div>
            <article class="card" style="max-width:700px;padding:24px;">
                <p>Seuls les acheteurs peuvent envoyer une demande d'association.</p>
                <div class="form-actions">
                    <a class="button" href="login.php">Se connecter</a>
                    <a class="button secondary" href="register.php">Creer un compte acheteur</a>
                </div>
            </article>
        <?php else: ?>
        <article class="card" style="max-width:900px;padding:24px;">
            <form class="form-grid" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= eAssociation($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="create">

                <div class="field">
                    <label for="nom">Nom <span>*</span></label>
                    <input id="nom" name="nom" type="text" minlength="3" maxlength="150" required value="<?= eAssociation($old['nom']) ?>">
                    <?php if (isset($errors['nom'])): ?><small class="field-error"><?= eAssociation($errors['nom']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="domaine">Domaine d'activite <span>*</span></label>
                    <select id="domaine" name="domaine" required>
                        <option value="" disabled <?= $old['domaine'] === '' ? 'selected' : '' ?>>Choisir...</option>
                        <?php foreach (['Agriculture', 'Alimentation solidaire', 'Environnement'] as $domaine): ?>
                            <option value="<?= eAssociation($domaine) ?>" <?= $old['domaine'] === $domaine ? 'selected' : '' ?>><?= eAssociation($domaine) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['domaine'])): ?><small class="field-error"><?= eAssociation($errors['domaine']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="ville">Ville <span>*</span></label>
                    <select id="ville" name="ville" required>
                        <option value="" disabled <?= $old['ville'] === '' ? 'selected' : '' ?>>Choisir...</option>
                        <?php foreach (['Tunis', 'Sfax', 'Sousse', 'Nabeul', 'Kairouan', 'Gabes'] as $ville): ?>
                            <option value="<?= eAssociation($ville) ?>" <?= $old['ville'] === $ville ? 'selected' : '' ?>><?= eAssociation($ville) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['ville'])): ?><small class="field-error"><?= eAssociation($errors['ville']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="telephone">Telephone <span>*</span></label>
                    <input id="telephone" name="telephone" type="tel" inputmode="tel" autocomplete="tel" required value="<?= eAssociation($old['telephone']) ?>">
                    <?php if (isset($errors['telephone'])): ?><small class="field-error"><?= eAssociation($errors['telephone']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="email">Email <span>*</span></label>
                    <input id="email" name="email" type="email" maxlength="180" autocomplete="email" required value="<?= eAssociation($old['email']) ?>">
                    <?php if (isset($errors['email'])): ?><small class="field-error"><?= eAssociation($errors['email']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="responsable">Responsable <span>*</span></label>
                    <input id="responsable" name="responsable" type="text" minlength="3" maxlength="150" required value="<?= eAssociation($old['responsable']) ?>">
                    <?php if (isset($errors['responsable'])): ?><small class="field-error"><?= eAssociation($errors['responsable']) ?></small><?php endif; ?>
                </div>

                <div class="form-actions field-full">
                    <button type="reset" class="button neutral">Effacer</button>
                    <button type="submit" class="button">Envoyer la demande</button>
                </div>
            </form>
        </article>
        <?php endif; ?>
        </main>
    </div>
</div>
<script src="../assets/typing-controls.js"></script>
</body>
</html>

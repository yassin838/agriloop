<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../controller/DonController.php';

$currentUser = $_SESSION['user'] ?? null;
$canMakeDonation = $currentUser !== null
    && ($currentUser['role'] ?? '') === 'agriculteur';

$controller = new DonController(Don::createWithDatabase());
$errors = [];
$old = [
    'id_association' => '',
    'quantite' => '',
    'date_retrait' => '',
    'remarque' => '',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canMakeDonation) {
        $errors['authorization'] = 'Seuls les utilisateurs avec le role agriculteur peuvent proposer un don.';
    } elseif (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['csrf'] = 'Session invalide. Rechargez la page.';
    } else {
        $old = array_intersect_key($_POST, $old);
        $_POST['id_user'] = (int) $currentUser['id'];
        $result = $controller->store($_POST);

        if ($result['success']) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Votre don a ete propose. Il est en attente de confirmation.',
            ];
            header('Location: don.php');
            exit;
        }

        $errors = $result['errors'];
    }
}

$associations = $controller->associationsActives();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eDon(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proposer un don | AgriLoop</title>
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
            <a href="don.php" aria-current="page">Dons</a>
            <a href="#parcelles">Parcelles</a>
            <a href="#avis">Avis</a>
            <?php if ($currentUser): ?>
                <a href="logout.php">Deconnexion (<?= eDon($currentUser['role']) ?>)</a>
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
            <a href="association.php">Associations</a>
            <a class="active" href="don.php">Dons</a>
            <a href="register.php">Inscription</a>
            <a href="login.php">Connexion</a>
        </aside>
        <main class="content fo-main">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Proposition utilisateur</p>
                <h1>Proposer un don</h1>
                <p class="subtitle">Votre proposition sera visible et traite uniquement dans le back-office.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= eDon($flash['type']) ?>" role="alert"><?= eDon($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (isset($errors['csrf'])): ?>
            <div class="alert error" role="alert"><?= eDon($errors['csrf']) ?></div>
        <?php endif; ?>

        <?php if (!$canMakeDonation): ?>
            <div class="alert error" role="alert">
                <?= isset($errors['authorization'])
                    ? eDon($errors['authorization'])
                    : 'Connectez-vous avec le role agriculteur pour proposer un don.' ?>
            </div>
            <article class="card" style="max-width:700px;padding:24px;">
                <p>Seuls les agriculteurs peuvent remplir le formulaire de don.</p>
                <div class="form-actions">
                    <a class="button" href="login.php">Se connecter</a>
                    <a class="button secondary" href="register.php">Creer un compte agriculteur</a>
                </div>
            </article>
        <?php else: ?>
        <article class="card" style="max-width:900px;padding:24px;">
            <form class="form-grid" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= eDon($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="create">

                <div class="field">
                    <label for="id_association">Association <span>*</span></label>
                    <select id="id_association" name="id_association" required>
                        <option value="" disabled <?= $old['id_association'] === '' ? 'selected' : '' ?>>Choisir...</option>
                        <?php foreach ($associations as $association): ?>
                            <option value="<?= (int) $association['id'] ?>" <?= (string) $old['id_association'] === (string) $association['id'] ? 'selected' : '' ?>><?= eDon($association['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['id_association'])): ?><small class="field-error"><?= eDon($errors['id_association']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="quantite">Quantite (kg) <span>*</span></label>
                    <input id="quantite" name="quantite" type="number" min="0.1" max="1000000" step="0.1" inputmode="decimal" required value="<?= eDon($old['quantite']) ?>">
                    <?php if (isset($errors['quantite'])): ?><small class="field-error"><?= eDon($errors['quantite']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="date_retrait">Date de retrait <span>*</span></label>
                    <input id="date_retrait" name="date_retrait" type="date" required value="<?= eDon($old['date_retrait']) ?>">
                    <?php if (isset($errors['date_retrait'])): ?><small class="field-error"><?= eDon($errors['date_retrait']) ?></small><?php endif; ?>
                </div>

                <div class="field field-full">
                    <label for="remarque">Remarque</label>
                    <textarea id="remarque" name="remarque" maxlength="1000" rows="4"><?= eDon($old['remarque']) ?></textarea>
                    <?php if (isset($errors['remarque'])): ?><small class="field-error"><?= eDon($errors['remarque']) ?></small><?php endif; ?>
                </div>

                <div class="form-actions field-full">
                    <button type="reset" class="button neutral">Effacer</button>
                    <button type="submit" class="button">Envoyer</button>
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

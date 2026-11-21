<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'administrateur') {
    header('Location: ../frontoffice/login.php');
    exit;
}

require_once __DIR__ . '/../../controller/UtilisateurController.php';
require_once __DIR__ . '/../../model/Utilisateur.php';

$controller = new UtilisateurController(Utilisateur::createWithDatabase());
$errors = [];
$editing = null;
$old = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'telephone' => '',
    'ville' => '',
    'role' => 'acheteur',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session invalide. Rechargez la page.'];
        header('Location: utilisateurs.php');
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'create') {
        $result = $controller->createByAdmin($_POST);
        if ($result['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Utilisateur cree.'];
            header('Location: utilisateurs.php');
            exit;
        }
        $errors = $result['errors'];
    } elseif ($action === 'set_active') {
        $active = (string) ($_POST['active'] ?? '') === '1';
        $result = $controller->setActive($id, $active, $currentUserId);
        $_SESSION['flash'] = $result['success']
            ? ['type' => 'success', 'message' => $active ? 'Utilisateur active.' : 'Utilisateur desactive.']
            : ['type' => 'error', 'message' => ($result['errors']['utilisateur'] ?? 'Modification impossible.')];
        header('Location: utilisateurs.php');
        exit;
    } elseif ($action === 'update') {
        $result = $controller->update($id, $_POST, $currentUserId);
        if ($result['success']) {
            $updated = $controller->find($id);
            if ($updated && $id === $currentUserId) {
                $_SESSION['user']['nom'] = $updated['nom'];
                $_SESSION['user']['prenom'] = $updated['prenom'];
                $_SESSION['user']['email'] = $updated['email'];
                $_SESSION['user']['role'] = $updated['role'];
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Utilisateur modifie.'];
            header('Location: utilisateurs.php');
            exit;
        }
        $errors = $result['errors'];
        $editing = $controller->find($id);
    } elseif ($action === 'delete') {
        $result = $controller->delete($id, $currentUserId);
        $_SESSION['flash'] = $result['success']
            ? ['type' => 'success', 'message' => 'Utilisateur supprime.']
            : ['type' => 'error', 'message' => ($result['errors']['utilisateur'] ?? 'Suppression impossible.')];
        header('Location: utilisateurs.php');
        exit;
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
if ($editing === null && $editId > 0) {
    $editing = $controller->find($editId);
}
if ($editing) {
    $old = [
        'nom' => (string) $editing['nom'],
        'prenom' => (string) $editing['prenom'],
        'email' => (string) $editing['email'],
        'telephone' => (string) $editing['telephone'],
        'ville' => (string) $editing['ville'],
        'role' => (string) $editing['role'],
    ];
}

$users = $controller->all();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eUser(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des utilisateurs | AgriLoop</title>
    <link rel="stylesheet" href="../frontoffice/styles.css">
    <link rel="stylesheet" href="../frontoffice/freshcart-template.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="dashboard.php"><span class="brand-mark"></span><span>AgriLoop<small>Administration</small></span></a>
        <nav aria-label="Navigation du back-office">
            <p class="nav-title">Tableau de bord</p>
            <a class="admin-nav-link" href="dashboard.php">Vue generale</a>
            <a class="admin-nav-link active" href="utilisateurs.php">Utilisateurs <span><?= count($users) ?></span></a>
            <p class="nav-title">Gestion Yassine Amri</p>
            <a class="admin-nav-link" href="associations.php">Associations</a>
            <a class="admin-nav-link" href="dons.php">Dons</a>
            <p class="nav-title">Compte</p>
            <a class="admin-nav-link" href="../frontoffice/logout.php">Deconnexion</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div><p class="eyebrow">Gestion des comptes</p><h1>Utilisateurs</h1></div>
            <a class="button secondary" href="dashboard.php">Tableau de bord</a>
        </header>

        <section class="admin-content">
            <?php if ($flash): ?>
                <div class="alert <?= eUser($flash['type']) ?>" role="alert"><?= eUser($flash['message']) ?></div>
            <?php endif; ?>

            <article class="card" style="padding:20px;margin-bottom:20px;">
                <div class="card-heading">
                    <span class="card-icon">+</span>
                    <div><p class="eyebrow">Creation administrateur</p><h2>Ajouter un utilisateur</h2></div>
                </div>
                <form class="form-grid" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= eUser($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="create">
                    <?php foreach (['nom', 'prenom', 'email', 'telephone', 'ville'] as $field): ?>
                        <div class="field">
                            <label for="create_<?= eUser($field) ?>"><?= eUser(ucfirst($field)) ?> *</label>
                            <input id="create_<?= eUser($field) ?>" name="<?= eUser($field) ?>"
                                type="<?= $field === 'email' ? 'email' : 'text' ?>"
                                maxlength="<?= $field === 'email' ? '180' : '100' ?>"
                                value="<?= eUser($old[$field]) ?>" required>
                            <?php if (isset($errors[$field])): ?><small class="field-error"><?= eUser($errors[$field]) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="field">
                        <label for="create_role">Role *</label>
                        <select id="create_role" name="role" required>
                            <option value="agriculteur" <?= $old['role'] === 'agriculteur' ? 'selected' : '' ?>>Agriculteur</option>
                            <option value="acheteur" <?= $old['role'] === 'acheteur' ? 'selected' : '' ?>>Acheteur</option>
                            <option value="administrateur" <?= $old['role'] === 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                        <?php if (isset($errors['role'])): ?><small class="field-error"><?= eUser($errors['role']) ?></small><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="create_password">Mot de passe *</label>
                        <input id="create_password" name="mot_de_passe" type="password" minlength="8" autocomplete="new-password" required>
                        <?php if (isset($errors['mot_de_passe'])): ?><small class="field-error"><?= eUser($errors['mot_de_passe']) ?></small><?php endif; ?>
                    </div>
                    <div class="form-actions field-full">
                        <button class="button" type="submit">Ajouter l'utilisateur</button>
                    </div>
                </form>
            </article>

            <?php if ($editing): ?>
                <article class="card" style="padding:20px;margin-bottom:20px;">
                    <div class="card-heading">
                        <span class="card-icon">U</span>
                        <div><p class="eyebrow">Modification administrateur</p><h2><?= eUser($editing['prenom'] . ' ' . $editing['nom']) ?></h2></div>
                    </div>
                    <form class="form-grid" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= eUser($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">

                        <?php foreach (['nom', 'prenom', 'email', 'telephone', 'ville'] as $field): ?>
                            <div class="field">
                                <label for="<?= eUser($field) ?>"><?= eUser(ucfirst($field)) ?> *</label>
                                <input id="<?= eUser($field) ?>" name="<?= eUser($field) ?>"
                                    type="<?= $field === 'email' ? 'email' : 'text' ?>"
                                    maxlength="<?= $field === 'email' ? '180' : '100' ?>"
                                    value="<?= eUser($old[$field]) ?>" required>
                                <?php if (isset($errors[$field])): ?><small class="field-error"><?= eUser($errors[$field]) ?></small><?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="field">
                            <label for="role">Role *</label>
                            <select id="role" name="role" required>
                                <?php foreach (['agriculteur' => 'Agriculteur', 'acheteur' => 'Acheteur', 'administrateur' => 'Administrateur'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $old['role'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role'])): ?><small class="field-error"><?= eUser($errors['role']) ?></small><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="mot_de_passe">Nouveau mot de passe</label>
                            <input id="mot_de_passe" name="mot_de_passe" type="password" minlength="8" autocomplete="new-password">
                            <?php if (isset($errors['mot_de_passe'])): ?><small class="field-error"><?= eUser($errors['mot_de_passe']) ?></small><?php endif; ?>
                        </div>

                        <div class="form-actions field-full">
                            <a class="button neutral" href="utilisateurs.php">Annuler</a>
                            <button class="button" type="submit">Modifier l'utilisateur</button>
                        </div>
                    </form>
                </article>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div><h2>Tous les utilisateurs</h2><p class="subtitle">Consultation, modification, rôles et suppression.</p></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Utilisateur</th><th>Email</th><th>Telephone</th><th>Ville</th><th>Role</th><th>Etat</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="7" class="empty">Aucun utilisateur.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= eUser($user['prenom'] . ' ' . $user['nom']) ?></strong><small>#<?= (int) $user['id'] ?></small></td>
                                <td><?= eUser($user['email']) ?></td>
                                <td><?= eUser($user['telephone']) ?></td>
                                <td><?= eUser($user['ville']) ?></td>
                                <td><span class="status <?= eUser($user['role']) ?>"><?= eUser($user['role']) ?></span></td>
                                <td><span class="status <?= ((int) ($user['actif'] ?? 1) === 1) ? 'active' : 'inactive' ?>"><?= ((int) ($user['actif'] ?? 1) === 1) ? 'Actif' : 'Desactive' ?></span></td>
                                <td class="admin-actions">
                                    <a class="icon-button" href="?edit=<?= (int) $user['id'] ?>">Modifier</a>
                                    <?php if ((int) $user['id'] !== $currentUserId): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= eUser($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="set_active">
                                            <input type="hidden" name="active" value="<?= ((int) ($user['actif'] ?? 1) === 1) ? '0' : '1' ?>">
                                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                            <button class="icon-button" type="submit"><?= ((int) ($user['actif'] ?? 1) === 1) ? 'Desactiver' : 'Reactiver' ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ((int) $user['id'] !== $currentUserId): ?>
                                        <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                            <input type="hidden" name="csrf_token" value="<?= eUser($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                            <button class="icon-button danger" type="submit">Supprimer</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>

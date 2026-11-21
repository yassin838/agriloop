<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'administrateur') {
    header('Location: ../frontoffice/login.php');
    exit;
}

require_once __DIR__ . '/../../controller/AssociationController.php';

$controller = new AssociationController(Association::createWithDatabase());
$errors = [];
$editing = null;
$old = [
    'nom' => '',
    'domaine' => '',
    'ville' => '',
    'telephone' => '',
    'email' => '',
    'responsable' => '',
    'statut' => 'active',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session invalide. Rechargez la page.'];
        header('Location: associations.php');
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'accept') {
        $result = $controller->updateStatut((int) ($_POST['id'] ?? 0), 'active');
        $_SESSION['flash'] = $result['success']
            ? ['type' => 'success', 'message' => 'Association acceptee.']
            : ['type' => 'error', 'message' => 'Impossible d accepter cette association.'];
        header('Location: associations.php');
        exit;
    } elseif ($action === 'update') {
        $result = $controller->update((int) ($_POST['id'] ?? 0), $_POST);
        if ($result['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Association modifiee.'];
            header('Location: associations.php');
            exit;
        }
        $errors = $result['errors'];
        $editing = $controller->find((int) ($_POST['id'] ?? 0));
    } elseif ($action === 'delete') {
        $result = $controller->delete((int) ($_POST['id'] ?? 0));
        $_SESSION['flash'] = $result['success']
            ? ['type' => 'success', 'message' => 'Association supprimee.']
            : ['type' => 'error', 'message' => ($result['errors']['association'] ?? 'Suppression impossible.')];
        header('Location: associations.php');
        exit;
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0 && $editing === null) {
    $editing = $controller->find($editId);
}
if ($editing) {
    $old = [
        'nom' => (string) $editing['nom'],
        'domaine' => (string) $editing['domaine'],
        'ville' => (string) $editing['ville'],
        'telephone' => (string) $editing['telephone'],
        'email' => (string) $editing['email'],
        'responsable' => (string) $editing['responsable'],
        'statut' => (string) $editing['statut'],
    ];
}

$associations = $controller->index();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eAdminAssociation(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des associations | AgriLoop</title>
    <link rel="stylesheet" href="../frontoffice/styles.css">
    <link rel="stylesheet" href="../frontoffice/freshcart-template.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="dons.php"><span class="brand-mark"></span><span>AgriLoop<small>Administration</small></span></a>
        <nav aria-label="Navigation du back-office">
            <p class="nav-title">Tableau de bord</p>
            <a class="admin-nav-link" href="dashboard.php">Vue generale</a>
            <a class="admin-nav-link" href="utilisateurs.php">Utilisateurs</a>
            <p class="nav-title">Gestion Yassine Amri</p>
            <a class="admin-nav-link active" href="associations.php">Associations <span><?= count($associations) ?></span></a>
            <a class="admin-nav-link" href="dons.php">Dons</a>
            <p class="nav-title">Navigation</p>
            <a class="admin-nav-link" href="../../index.php">Retour au site</a>
            <a class="admin-nav-link" href="../frontoffice/logout.php">Deconnexion</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div><p class="eyebrow">Module administrateur</p><h1>Gestion des associations</h1></div>
            <a class="button secondary" href="dons.php">Voir les dons</a>
        </header>

        <section class="admin-content">
            <?php if ($flash): ?>
                <div class="alert <?= eAdminAssociation($flash['type']) ?>" role="alert"><?= eAdminAssociation($flash['message']) ?></div>
            <?php endif; ?>

            <?php if ($editing): ?>
                <article class="card" style="padding:20px;margin-bottom:20px;">
                    <div class="card-heading"><span class="card-icon">M</span><div><p class="eyebrow">Modification administrateur</p><h2><?= eAdminAssociation($editing['nom']) ?></h2></div></div>
                    <form class="form-grid" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= eAdminAssociation($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                        <?php foreach (['nom', 'domaine', 'ville', 'telephone', 'email', 'responsable'] as $field): ?>
                            <div class="field">
                                <label for="<?= eAdminAssociation($field) ?>"><?= eAdminAssociation(ucfirst($field)) ?></label>
                                <input id="<?= eAdminAssociation($field) ?>" name="<?= eAdminAssociation($field) ?>" value="<?= eAdminAssociation($old[$field]) ?>" required>
                            </div>
                        <?php endforeach; ?>
                        <div class="field">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut">
                                <option value="en_attente" <?= $old['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="active" <?= $old['statut'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $old['statut'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-actions field-full">
                            <a class="button neutral" href="associations.php">Annuler</a>
                            <button class="button" type="submit">Modifier</button>
                        </div>
                    </form>
                </article>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div><h2>Associations enregistrees</h2><p class="subtitle">Gestion reservée à l'administrateur.</p></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Association</th><th>Domaine</th><th>Ville</th><th>Responsable</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$associations): ?>
                            <tr><td colspan="6" class="empty">Aucune association.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($associations as $association): ?>
                            <tr>
                                <td><strong><?= eAdminAssociation($association['nom']) ?></strong><small><?= eAdminAssociation($association['email']) ?></small></td>
                                <td><?= eAdminAssociation($association['domaine']) ?></td>
                                <td><?= eAdminAssociation($association['ville']) ?></td>
                                <td><?= eAdminAssociation($association['responsable']) ?></td>
                                <td><span class="status <?= eAdminAssociation($association['statut']) ?>"><?= eAdminAssociation($association['statut']) ?></span></td>
                                <td class="admin-actions">
                                    <?php if ($association['statut'] === 'en_attente'): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= eAdminAssociation($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <input type="hidden" name="id" value="<?= (int) $association['id'] ?>">
                                            <button class="icon-button" type="submit">Accepter</button>
                                        </form>
                                    <?php endif; ?>
                                    <a class="icon-button" href="?edit=<?= (int) $association['id'] ?>">Modifier</a>
                                    <form method="post" onsubmit="return confirm('Supprimer cette association ?');">
                                        <input type="hidden" name="csrf_token" value="<?= eAdminAssociation($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $association['id'] ?>">
                                        <button class="icon-button danger" type="submit">Supprimer</button>
                                    </form>
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

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'administrateur') {
    header('Location: ../frontoffice/login.php');
    exit;
}

require_once __DIR__ . '/../../controller/DonController.php';
require_once __DIR__ . '/../../model/Association.php';

$controller = new DonController(Don::createWithDatabase());
$associationModel = Association::createWithDatabase();
$errors = [];
$editing = null;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session invalide. Rechargez la page.'];
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $action = (string) ($_POST['action'] ?? '');

        if (in_array($action, ['en_attente', 'confirme', 'annule'], true)) {
            $result = $controller->updateStatut($id, $action);
            $_SESSION['flash'] = $result['success']
                ? ['type' => 'success', 'message' => 'Statut du don mis à jour.']
                : ['type' => 'error', 'message' => 'Modification du statut impossible.'];
        } elseif ($action === 'delete') {
            $result = $controller->delete($id);
            $_SESSION['flash'] = $result['success']
                ? ['type' => 'success', 'message' => 'Don supprimé.']
                : ['type' => 'error', 'message' => 'Suppression impossible.'];
        } elseif ($action === 'update') {
            $result = $controller->update($id, $_POST);
            if ($result['success']) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Don modifié.'];
                if ($errors === []) {
                    header('Location: dons.php');
                    exit;
                }
            }
            $errors = $result['errors'];
            $editing = $controller->find($id);
        }
    }

    header('Location: dons.php');
    exit;
}

$filtre = in_array($_GET['statut'] ?? '', ['en_attente', 'confirme', 'annule'], true)
    ? (string) $_GET['statut']
    : null;
$dons = $controller->index($filtre);
$stats = (new Don(Database::getConnexion()))->stats();
$associations = $associationModel->all();
$associationsActives = $controller->associationsActives();
$editId = (int) ($_GET['edit'] ?? 0);
if ($editing === null && $editId > 0) {
    $editing = $controller->find($editId);
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function eBack(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des dons | AgriLoop</title>
    <link rel="stylesheet" href="../frontoffice/styles.css">
    <link rel="stylesheet" href="../frontoffice/freshcart-template.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="dons.php"><span class="brand-mark"></span><span>AgriLoop<small>Administration</small></span></a>
        <nav aria-label="Navigation du back-office">
            <p class="nav-title">Vue generale</p>
            <a class="admin-nav-link" href="dashboard.php">⌂ Tableau de bord</a>
            <a class="admin-nav-link" href="utilisateurs.php">♙ Utilisateurs</a>
            <p class="nav-title">Gestion</p>
            <a class="admin-nav-link" href="associations.php">♧ Associations <span><?= count($associations) ?></span></a>
            <a class="admin-nav-link active" href="dons.php" aria-current="page">♡ Dons <span><?= (int) ($stats['total'] ?? 0) ?></span></a>
            <p class="nav-title">Compte</p>
            <a class="admin-nav-link" href="../frontoffice/logout.php">Deconnexion</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div><p class="eyebrow">Espace de gestion</p><h1>Gestion des dons</h1></div>
            <a class="button secondary" href="../frontoffice/don.php">Retour au formulaire</a>
        </header>

        <section class="admin-content">
            <?php if ($flash): ?>
                <div class="alert <?= eBack($flash['type']) ?>" role="alert"><?= eBack($flash['message']) ?></div>
            <?php endif; ?>

            <?php if ($editing): ?>
                <article class="card" style="padding:20px;margin-bottom:20px;">
                    <div class="card-heading"><span class="card-icon">M</span><div><p class="eyebrow">Modification administrateur</p><h2>Modifier le don #<?= (int) $editing['id'] ?></h2></div></div>
                    <form class="form-grid" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= eBack($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                        <div class="field">
                            <label for="id_association">Association</label>
                            <select id="id_association" name="id_association" required>
                                <?php foreach ($associationsActives as $association): ?>
                                    <option value="<?= (int) $association['id'] ?>" <?= (int) $editing['id_association'] === (int) $association['id'] ? 'selected' : '' ?>><?= eBack($association['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="quantite">Quantite (kg)</label>
                            <input id="quantite" name="quantite" type="number" min="0.1" max="1000000" step="0.1" value="<?= eBack($editing['quantite']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="date_retrait">Date de retrait</label>
                            <input id="date_retrait" name="date_retrait" type="date" value="<?= eBack($editing['date_retrait']) ?>" required>
                        </div>
                        <div class="field field-full">
                            <label for="remarque">Remarque</label>
                            <textarea id="remarque" name="remarque" maxlength="1000"><?= eBack($editing['remarque'] ?? '') ?></textarea>
                        </div>
                        <div class="field field-full">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut">
                                <?php foreach (['en_attente' => 'En attente', 'confirme' => 'Confirme', 'annule' => 'Annule'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $editing['statut'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-actions field-full">
                            <a class="button neutral" href="dons.php">Annuler</a>
                            <button class="button" type="submit">Modifier</button>
                        </div>
                    </form>
                </article>
            <?php endif; ?>

            <div class="page-heading">
                <div><p class="eyebrow">Suivi des échanges</p><h2>Dons proposés</h2><p class="subtitle">Consultez, confirmez ou annulez les dons enregistrés.</p></div>
                <a class="button" href="../frontoffice/don.php">+ Nouveau don</a>
            </div>

            <div class="stats-grid">
                <article class="stat-card"><p>Total des dons</p><strong><?= (int) ($stats['total'] ?? 0) ?></strong></article>
                <article class="stat-card"><p>En attente</p><strong><?= (int) ($stats['en_attente'] ?? 0) ?></strong></article>
                <article class="stat-card"><p>Dons confirmés</p><strong><?= (int) ($stats['confirmes'] ?? 0) ?></strong></article>
                <article class="stat-card"><p>Quantité confirmée</p><strong><?= number_format((float) ($stats['quantite_confirme'] ?? 0), 1, ',', ' ') ?> kg</strong></article>
            </div>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div><h2>Historique des dons</h2><p class="subtitle">Données liées aux associations approuvées.</p></div>
                    <form class="filter-form" method="get">
                        <label for="statut" class="eyebrow">Filtre</label>
                        <select id="statut" name="statut" onchange="this.form.submit()">
                            <option value="" <?= $filtre === null ? 'selected' : '' ?>>Tous les statuts</option>
                            <option value="en_attente" <?= $filtre === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="confirme" <?= $filtre === 'confirme' ? 'selected' : '' ?>>Confirmés</option>
                            <option value="annule" <?= $filtre === 'annule' ? 'selected' : '' ?>>Annulés</option>
                        </select>
                    </form>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Association</th><th>Quantité</th><th>Retrait</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$dons): ?>
                            <tr><td colspan="5" class="empty">Aucun don ne correspond au filtre.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($dons as $don): ?>
                            <tr>
                                <td><strong><?= eBack($don['association_nom']) ?></strong><small><?= eBack($don['association_responsable']) ?></small></td>
                                <td><?= number_format((float) $don['quantite'], 1, ',', ' ') ?> kg</td>
                                <td><?= date('d/m/Y', strtotime($don['date_retrait'])) ?></td>
                                <td><span class="status <?= eBack($don['statut']) ?>"><?= eBack(str_replace('_', ' ', $don['statut'])) ?></span></td>
                                <td class="admin-actions">
                                    <a class="icon-button" href="?edit=<?= (int) $don['id'] ?>">Modifier</a>
                                    <?php if ($don['statut'] !== 'confirme'): ?>
                                        <form method="post"><input type="hidden" name="csrf_token" value="<?= eBack($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= (int) $don['id'] ?>"><input type="hidden" name="action" value="confirme"><button class="icon-button" type="submit">Confirmer</button></form>
                                    <?php endif; ?>
                                    <?php if ($don['statut'] !== 'annule'): ?>
                                        <form method="post"><input type="hidden" name="csrf_token" value="<?= eBack($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= (int) $don['id'] ?>"><input type="hidden" name="action" value="annule"><button class="icon-button" type="submit">Annuler</button></form>
                                    <?php endif; ?>
                                    <form method="post" onsubmit="return confirm('Supprimer ce don ?');"><input type="hidden" name="csrf_token" value="<?= eBack($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= (int) $don['id'] ?>"><input type="hidden" name="action" value="delete"><button class="icon-button danger" type="submit">Supprimer</button></form>
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

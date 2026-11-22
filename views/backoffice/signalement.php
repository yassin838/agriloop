<?php
// Back office — signalements.
// Cette page fait 3 choses : 1) enregistrer un signalement envoyé par le front office,
// 2) exécuter les boutons du back office (valider, rejeter, supprimer), 3) afficher la liste.

// ---- Connexion à la base (à adapter) ----
$pdo = new PDO('mysql:host=localhost;dbname=agriloop;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
const USER_ID = 4;   // utilisateur qui signale depuis le site (pas encore de connexion)

function e($texte) {   // affichage sécurisé dans la page
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

$motifs  = ['spam' => 'Spam', 'injurieux' => 'Propos injurieux', 'faux' => 'Faux avis', 'autre' => 'Autre'];
$statuts = ['attente' => 'En attente', 'traite' => 'Traité', 'rejete' => 'Rejeté'];

// ---- 1 et 2 : un formulaire a été envoyé ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        // Formulaire du front office (signalement.html)
        $avis  = (int)($_POST['avis'] ?? 0);
        $motif = $_POST['motif'] ?? '';
        $desc  = trim($_POST['description'] ?? '');

        if ($avis < 1 || $desc === '' || !isset($motifs[$motif])) {
            exit('Données invalides. <a href="javascript:history.back()">Retour</a>');
        }
        $s = $pdo->prepare('SELECT nom FROM utilisateur WHERE id = ?');
        $s->execute([USER_ID]);
        $auteur = $s->fetchColumn() ?: 'Anonyme';

        $pdo->prepare('INSERT INTO signalement (motif, description, auteur, idAvis) VALUES (?, ?, ?, ?)')
            ->execute([$motif, $desc, $auteur, $avis]);
        header('Location: ../front%20office/signalement.html#merci');
        exit;
    }

    // Boutons du back office
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'valider') {
        // Signalement justifié : on le marque « traité » et on masque l'avis visé
        $pdo->prepare("UPDATE signalement SET statut = 'traite' WHERE id = ?")->execute([$id]);
        $pdo->prepare("UPDATE avis SET statut = 'masque'
                       WHERE id = (SELECT idAvis FROM signalement WHERE id = ?)")->execute([$id]);
    } elseif ($action === 'rejeter') {
        $pdo->prepare("UPDATE signalement SET statut = 'rejete' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'supprimer') {
        $pdo->prepare('DELETE FROM signalement WHERE id = ?')->execute([$id]);
    }
    header('Location: signalement.php');
    exit;
}

// ---- 3 : affichage ----
$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(statut = 'attente'), 0) AS attente,
            COALESCE(SUM(statut = 'traite'), 0)  AS traite,
            COALESCE(SUM(statut = 'rejete'), 0)  AS rejete
     FROM signalement"
)->fetch();

$liste = $pdo->query(
    "SELECT s.*, a.titre AS titre_avis
     FROM signalement s
     JOIN avis a ON a.id = s.idAvis
     ORDER BY s.date_signalement DESC, s.id DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AgriLoop — Back office : Signalements</title>
  <link rel="stylesheet" href="back.css">
</head>
<body>
  <header>
    <a class="logo" href="avis.php">AgriLoop<small>BACK OFFICE</small></a>
    <nav>
      <a href="avis.php">Avis</a>
      <a href="signalement.php" class="active">Signalements</a>
      <a href="../front-office/avis.html">Front office</a>
    </nav>
  </header>

  <main>
    <h1>Gestion des signalements</h1>

    <div class="stats">
      <div class="stat"><b><?= (int)$stats['total'] ?></b><span>Signalements au total</span></div>
      <div class="stat"><b><?= (int)$stats['attente'] ?></b><span>En attente</span></div>
      <div class="stat"><b><?= (int)$stats['traite'] ?></b><span>Traités</span></div>
      <div class="stat"><b><?= (int)$stats['rejete'] ?></b><span>Rejetés</span></div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Date</th><th>Avis signalé</th><th>Motif</th>
            <th>Description</th><th>Auteur</th><th>Statut</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$liste): ?>
          <tr><td class="vide" colspan="8">Aucun signalement.</td></tr>
        <?php endif; ?>

        <?php foreach ($liste as $s): ?>
          <tr>
            <td><?= $s['id'] ?></td>
            <td><?= e($s['date_signalement']) ?></td>
            <td><?= e($s['titre_avis']) ?></td>
            <td><?= e($motifs[$s['motif']] ?? $s['motif']) ?></td>
            <td class="cell-texte"><?= e($s['description']) ?></td>
            <td><?= e($s['auteur']) ?></td>
            <td><span class="badge <?= e($s['statut']) ?>"><?= e($statuts[$s['statut']] ?? $s['statut']) ?></span></td>
            <td>
              <form method="post" class="actions-cell" onsubmit="return confirm('Confirmer ?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <?php if ($s['statut'] === 'attente'): ?>
                  <button class="btn" name="action" value="valider">Valider + masquer l'avis</button>
                  <button class="btn sec" name="action" value="rejeter">Rejeter</button>
                <?php endif; ?>
                <button class="btn danger" name="action" value="supprimer">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>

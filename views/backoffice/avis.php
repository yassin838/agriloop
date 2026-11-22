<?php
// Back office — avis.
// Cette page fait 3 choses : 1) enregistrer un avis envoyé par le front office,
// 2) exécuter les boutons du back office (masquer, publier, supprimer), 3) afficher la liste.

// ---- Connexion à la base (à adapter) ----
$pdo = new PDO('mysql:host=localhost;dbname=agriloop;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
const USER_ID = 4;   // utilisateur qui publie les avis du site (pas encore de connexion)

function e($texte) {   // affichage sécurisé dans la page
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

// ---- 1 et 2 : un formulaire a été envoyé ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        // Formulaire du front office (avis.html)
        $offre = (int)($_POST['offre'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $note  = (int)($_POST['note'] ?? 0);
        $fraic = (int)($_POST['fraicheur'] ?? -1);
        $com   = trim($_POST['commentaire'] ?? '');

        if ($offre < 1 || $titre === '' || $com === '' || $note < 1 || $note > 5 || $fraic < 0 || $fraic > 5) {
            exit('Données invalides. <a href="javascript:history.back()">Retour</a>');
        }
        $sql = 'INSERT INTO avis (titre, note, fraicheur, commentaire, idOffre, idAuteur) VALUES (?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$titre, $note, $fraic, $com, $offre, USER_ID]);
        header('Location: ../front%20office/avis.html#merci');
        exit;
    }

    // Boutons du back office
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'masquer') {
        $pdo->prepare("UPDATE avis SET statut = 'masque' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'publier') {
        $pdo->prepare("UPDATE avis SET statut = 'publie' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'supprimer') {
        // Les signalements de cet avis sont supprimés automatiquement (ON DELETE CASCADE)
        $pdo->prepare('DELETE FROM avis WHERE id = ?')->execute([$id]);
    }
    header('Location: avis.php');
    exit;
}

// ---- 3 : affichage ----
$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(statut = 'publie'), 0) AS publies,
            COALESCE(SUM(statut = 'masque'), 0) AS masques,
            AVG(note) AS moyenne
     FROM avis"
)->fetch();

$liste = $pdo->query(
    "SELECT a.*, o.nom AS offre, u.nom AS auteur, COUNT(s.id) AS nb_signalements
     FROM avis a
     JOIN offre o ON o.id = a.idOffre
     JOIN utilisateur u ON u.id = a.idAuteur
     LEFT JOIN signalement s ON s.idAvis = a.id
     GROUP BY a.id
     ORDER BY a.date_avis DESC, a.id DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AgriLoop — Back office : Avis</title>
  <link rel="stylesheet" href="back.css">
</head>
<body>
  <header>
    <a class="logo" href="avis.php">AgriLoop<small>BACK OFFICE</small></a>
    <nav>
      <a href="avis.php" class="active">Avis</a>
      <a href="signalement.php">Signalements</a>
      <a href="../front-office/avis.html">Front office</a>
    </nav>
  </header>

  <main>
    <h1>Gestion des avis</h1>

    <div class="stats">
      <div class="stat"><b><?= (int)$stats['total'] ?></b><span>Avis au total</span></div>
      <div class="stat"><b><?= (int)$stats['publies'] ?></b><span>Publiés</span></div>
      <div class="stat"><b><?= (int)$stats['masques'] ?></b><span>Masqués</span></div>
      <div class="stat"><b><?= $stats['moyenne'] !== null ? round($stats['moyenne'], 1) . '/5' : '–' ?></b><span>Note moyenne</span></div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Offre</th><th>Titre</th><th>Auteur</th><th>Note</th><th>Fraîcheur</th>
            <th>Commentaire</th><th>Date</th><th>Statut</th><th>Signal.</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$liste): ?>
          <tr><td class="vide" colspan="11">Aucun avis.</td></tr>
        <?php endif; ?>

        <?php foreach ($liste as $a): ?>
          <tr>
            <td><?= $a['id'] ?></td>
            <td><?= e($a['offre']) ?></td>
            <td><?= e($a['titre']) ?></td>
            <td><?= e($a['auteur']) ?></td>
            <td><?= $a['note'] ?>/5</td>
            <td><?= $a['fraicheur'] ?>/5</td>
            <td class="cell-texte"><?= e($a['commentaire']) ?></td>
            <td><?= e($a['date_avis']) ?></td>
            <td><span class="badge <?= e($a['statut']) ?>"><?= $a['statut'] === 'publie' ? 'Publié' : 'Masqué' ?></span></td>
            <td><?= $a['nb_signalements'] ?></td>
            <td>
              <form method="post" class="actions-cell" onsubmit="return confirm('Confirmer ?');">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <?php if ($a['statut'] === 'publie'): ?>
                  <button class="btn sec" name="action" value="masquer">Masquer</button>
                <?php else: ?>
                  <button class="btn sec" name="action" value="publier">Publier</button>
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

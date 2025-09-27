<?php
global $pdo;

try {
  $tableExists = false;
  $stmt = $pdo->query("SHOW TABLES LIKE 'villes'");
  if ($stmt && $stmt->fetchColumn()) { $tableExists = true; }
} catch (Throwable $e) {
  $tableExists = false;
}
if (!$tableExists) {
  echo '<div class="alert alert-warning">La base de données n\'est pas encore initialisée. Veuillez exécuter <a href="/Vogie2/public/migrate.php">migrate.php</a> pour créer les tables ou <a href="/Vogie2/public/import_sql.php">import_sql.php</a> pour importer le dump SQL.</div>';
  return;
}

if (($_POST['action'] ?? '') === 'create_ville') {
  $nom = trim($_POST['nom'] ?? '');
  if ($nom !== '') {
    try {
      $stmt = $pdo->prepare('INSERT IGNORE INTO villes(nom) VALUES(:nom)');
      $stmt->execute([':nom'=>$nom]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de l\'ajout de la ville: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'delete_ville') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try { $pdo->prepare('DELETE FROM villes WHERE id=?')->execute([$id]); }
    catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}
try {
  $villes = $pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des villes: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $villes = [];
}
?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_ville">
  <div class="col-auto"><input name="nom" class="form-control" placeholder="Ajouter une ville"></div>
  <div class="col-auto"><button class="btn btn-primary">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php foreach ($villes as $v): ?>
      <tr>
        <td><?= (int)$v['id'] ?></td>
        <td><?= htmlspecialchars($v['nom']) ?></td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cette ville ?');">
            <input type="hidden" name="action" value="delete_ville">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
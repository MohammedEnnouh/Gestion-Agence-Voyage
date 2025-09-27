<?php

global $pdo;

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS clients (id INT UNSIGNED NOT NULL AUTO_INCREMENT, nom VARCHAR(150) NOT NULL, email VARCHAR(150) NULL, telephone VARCHAR(50) NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur d\'initialisation de la table clients: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

if (($_POST['action'] ?? '') === 'create_client') {
  $nom = trim($_POST['nom'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telephone = trim($_POST['telephone'] ?? '');
  if ($nom) {
    try {
      $pdo->prepare('INSERT INTO clients(nom,email,telephone) VALUES(?,?,?)')->execute([$nom,$email,$telephone]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de l\'ajout du client: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'delete_client') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try { $pdo->prepare('DELETE FROM clients WHERE id=?')->execute([$id]); }
    catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}

try {
  $clients = $pdo->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur lors du chargement des clients: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $clients = [];
}
?>

<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_client">
  <div class="col-md-3"><input name="nom" class="form-control" placeholder="Nom" required></div>
  <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email"></div>
  <div class="col-md-3"><input name="telephone" class="form-control" placeholder="Téléphone"></div>
  <div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php if (!$clients): ?>
      <tr><td colspan="5" class="text-center text-muted">Aucun client</td></tr>
    <?php else: foreach ($clients as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['telephone']) ?></td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce client ?');">
            <input type="hidden" name="action" value="delete_client">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>
</div>
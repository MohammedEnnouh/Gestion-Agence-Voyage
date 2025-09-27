<?php

global $pdo;

try {
  $hasUsers = (bool)($pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn());
} catch (Throwable $e) { $hasUsers = false; }
if (!$hasUsers) {
  echo '<div class="alert alert-warning">Table <code>users</code> introuvable. Veuillez exécuter <a href="/Vogie2/public/import_sql.php">import_sql.php</a> pour importer <code>vogie_web.sql</code>.</div>';
  return;
}

try {
  $clients = $pdo->query("SELECT id, full_name, email, phone, is_active, created_at FROM users WHERE role='user' ORDER BY full_name")
                 ->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des utilisateurs: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $clients = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Users (role = user)</h5>
</div>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th>
        <th>Nom</th>
        <th>Email</th>
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Créé</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$clients): ?>
        <tr><td colspan="6" class="text-center text-muted">Aucun utilisateur (role user)</td></tr>
      <?php else: foreach ($clients as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['phone']) ?></td>
          <td><span class="badge <?= !empty($u['is_active']) ? 'bg-success' : 'bg-secondary' ?>"><?= !empty($u['is_active']) ? 'Actif' : 'Inactif' ?></span></td>
          <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
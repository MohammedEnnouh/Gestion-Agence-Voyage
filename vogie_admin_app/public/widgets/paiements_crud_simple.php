<?php

global $pdo;

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS paiements (id INT UNSIGNED NOT NULL AUTO_INCREMENT, client_id INT UNSIGNED NOT NULL, trajet_id INT UNSIGNED NOT NULL, montant DECIMAL(10,2) NOT NULL, statut VARCHAR(20) NOT NULL DEFAULT 'en_attente', date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, date_confirmation DATETIME NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur d\'initialisation de la table paiements: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

try { $clients = $pdo->query('SELECT id, nom FROM clients ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC); }
catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de lecture des clients: ' . htmlspecialchars($e->getMessage()) . '</div>'; $clients=[]; }

try { $trajets = $pdo->query('SELECT t.id, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC); }
catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de lecture des trajets: ' . htmlspecialchars($e->getMessage()) . '</div>'; $trajets=[]; }

if (($_POST['action'] ?? '') === 'create_paiement') {
  $client_id = (int)($_POST['client_id'] ?? 0);
  $trajet_id = (int)($_POST['trajet_id'] ?? 0);
  $montant = (float)($_POST['montant'] ?? 0);
  if ($client_id && $trajet_id && $montant>0) {
    try {
      $pdo->prepare("INSERT INTO paiements(client_id,trajet_id,montant,statut) VALUES(?,?,?,'en_attente')")->execute([$client_id,$trajet_id,$montant]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de la création du paiement: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'confirm_paiement') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try { $pdo->prepare("UPDATE paiements SET statut='confirme', date_confirmation=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]); }
    catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de confirmation: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}

if (($_POST['action'] ?? '') === 'delete_paiement') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try { $pdo->prepare('DELETE FROM paiements WHERE id=?')->execute([$id]); }
    catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}

try {
  $rows = $pdo->query("SELECT p.*, c.nom as client, vd.nom as depart, va.nom as arrivee FROM paiements p JOIN clients c ON c.id=p.client_id JOIN trajets t ON t.id=p.trajet_id JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des paiements: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $rows = [];
}
?>
<?php if (!$clients || !$trajets): ?>
  <div class="alert alert-info">Veuillez d'abord ajouter au moins un client et un trajet (via les pages <a href="/Vogie2/public/index.php?page=clients">Clients</a> et <a href="/Vogie2/public/index.php?page=trajets">Trajets</a>) pour créer un paiement.</div>
<?php else: ?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_paiement">
  <div class="col-md-3">
    <select name="client_id" class="form-select" required>
      <option value="">Client</option>
      <?php foreach ($clients as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <select name="trajet_id" class="form-select" required>
      <option value="">Trajet</option>
      <?php foreach ($trajets as $t): ?><option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['depart']) ?> → <?= htmlspecialchars($t['arrivee']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input name="montant" type="number" step="0.5" min="1" class="form-control" placeholder="Montant (MAD)" required></div>
  <div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div>
</form>
<?php endif; ?>
<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>
  <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="8" class="text-center text-muted">Aucun paiement</td></tr>
    <?php else: foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['client']) ?></td>
        <td><?= htmlspecialchars($r['depart']) ?> → <?= htmlspecialchars($r['arrivee']) ?></td>
        <td><?= number_format((float)$r['montant'], 2, ',', ' ') ?></td>
        <td><span class="badge <?= $r['statut']==='confirme'?'bg-success':'bg-warning text-dark' ?>"><?= htmlspecialchars($r['statut']) ?></span></td>
        <td><?= htmlspecialchars($r['date_creation']) ?></td>
        <td><?= htmlspecialchars($r['date_confirmation'] ?? '') ?></td>
        <td class="text-nowrap">
          <?php if ($r['statut'] !== 'confirme'): ?>
          <form method="post" class="d-inline">
            <input type="hidden" name="action" value="confirm_paiement">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-success">Confirmer</button>
          </form>
          <?php endif; ?>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?');">
            <input type="hidden" name="action" value="delete_paiement">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>
</div>
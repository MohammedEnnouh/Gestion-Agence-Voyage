<?php
global $pdo;

try {
  $hasVilles = (bool)($pdo->query("SHOW TABLES LIKE 'villes'")->fetchColumn());
  $hasTrajets = (bool)($pdo->query("SHOW TABLES LIKE 'trajets'")->fetchColumn());
} catch (Throwable $e) { $hasVilles = $hasTrajets = false; }
if (!$hasVilles || !$hasTrajets) {
  echo '<div class="alert alert-warning">La base de données n\'est pas initialisée (tables manquantes). Veuillez exécuter <a href="/Vogie2/public/migrate.php">migrate.php</a> ou <a href="/Vogie2/public/import_sql.php">import_sql.php</a>.</div>';
  return;
}

try {
  $villes = $pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des villes: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $villes = [];
}

if (($_POST['action'] ?? '') === 'create_trajet') {
  $vd = (int)($_POST['ville_depart_id'] ?? 0);
  $va = (int)($_POST['ville_arrivee_id'] ?? 0);
  $hd = trim($_POST['heure_depart'] ?? '');
  $ha = trim($_POST['heure_arrivee'] ?? '');
  $prix = (float)($_POST['prix'] ?? 0);
  if ($vd && $va && $hd && $ha && $prix>0) {
    try {
      $stmt = $pdo->prepare('INSERT INTO trajets(ville_depart_id,ville_arrivee_id,heure_depart,heure_arrivee,prix) VALUES(?,?,?,?,?)');
      $stmt->execute([$vd,$va,$hd,$ha,$prix]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de l\'ajout du trajet: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'update_trajet') {
  $id = (int)($_POST['id'] ?? 0);
  $hd = trim($_POST['heure_depart'] ?? '');
  $ha = trim($_POST['heure_arrivee'] ?? '');
  $prix = (float)($_POST['prix'] ?? 0);
  if ($id && $hd && $ha && $prix>0) {
    try {
      $stmt = $pdo->prepare('UPDATE trajets SET heure_depart=?, heure_arrivee=?, prix=? WHERE id=?');
      $stmt->execute([$hd,$ha,$prix,$id]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de la modification: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'delete_trajet') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try { $pdo->prepare('DELETE FROM trajets WHERE id=?')->execute([$id]); }
    catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}

$q = trim($_GET['q'] ?? '');
try {
  if ($q !== '') {
    $stmt = $pdo->prepare('SELECT t.*, vd.nom as depart, va.nom as arrivee
                           FROM trajets t
                           JOIN villes vd ON vd.id=t.ville_depart_id
                           JOIN villes va ON va.id=t.ville_arrivee_id
                           WHERE vd.nom LIKE ? OR va.nom LIKE ?
                           ORDER BY t.id DESC');
    $like = "%$q%";
    $stmt->execute([$like,$like]);
    $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $trajets = $pdo->query('SELECT t.*, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des trajets: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $trajets = [];
}
?>
<form method="get" class="row g-2 mb-3 align-items-center">
  <div class="col-md-4">
    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by city (departure/arrival)">
  </div>
  <div class="col-md-2">
    <button class="btn btn-outline-secondary w-100">Search</button>
  </div>
</form>

<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_trajet">
  <div class="col-md-3">
    <select name="ville_depart_id" class="form-select" required>
      <option value="">Ville de départ</option>
      <?php foreach ($villes as $v): ?><option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="ville_arrivee_id" class="form-select" required>
      <option value="">Ville d'arrivée</option>
      <?php foreach ($villes as $v): ?><option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input name="heure_depart" type="time" class="form-control" required></div>
  <div class="col-md-2"><input name="heure_arrivee" type="time" class="form-control" required></div>
  <div class="col-md-1"><input name="prix" type="number" min="1" step="0.5" class="form-control" placeholder="MAD" required></div>
  <div class="col-md-1"><button class="btn btn-primary w-100">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php foreach ($trajets as $t): ?>
      <tr>
        <td><?= (int)$t['id'] ?></td>
        <td><?= htmlspecialchars($t['depart']) ?></td>
        <td><?= htmlspecialchars($t['arrivee']) ?></td>
        <td><?= htmlspecialchars($t['heure_depart']) ?></td>
        <td><?= htmlspecialchars($t['heure_arrivee']) ?></td>
        <td><?= number_format((float)$t['prix'], 2, ',', ' ') ?></td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTrajet(<?= (int)$t['id'] ?>, '<?= htmlspecialchars($t['heure_depart']) ?>', '<?= htmlspecialchars($t['heure_arrivee']) ?>', '<?= (float)$t['prix'] ?>')">Modifier</button>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce trajet ?');">
            <input type="hidden" name="action" value="delete_trajet">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editTrajetModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le trajet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="action" value="update_trajet">
          <input type="hidden" name="id" id="edit-trajet-id">
          <div class="mb-3">
            <label class="form-label">Heure départ</label>
            <input type="time" class="form-control" name="heure_depart" id="edit-heure-depart" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Heure arrivée</label>
            <input type="time" class="form-control" name="heure_arrivee" id="edit-heure-arrivee" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prix (MAD)</label>
            <input type="number" step="0.5" min="1" class="form-control" name="prix" id="edit-prix" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
 </div>

<script>
function editTrajet(id, hd, ha, prix) {
  document.getElementById('edit-trajet-id').value = id;
  document.getElementById('edit-heure-depart').value = hd;
  document.getElementById('edit-heure-arrivee').value = ha;
  document.getElementById('edit-prix').value = prix;
  new bootstrap.Modal(document.getElementById('editTrajetModal')).show();
}
</script>
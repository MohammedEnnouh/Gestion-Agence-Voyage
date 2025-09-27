<?php

if (($_POST['action'] ?? '') === 'cleanup_empty_clients') {
  $pdo->exec("DELETE FROM clients WHERE (email IS NULL OR email='') AND (telephone IS NULL OR telephone='')");
}
if (($_POST['action'] ?? '') === 'confirm_payment') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare("UPDATE paiements SET statut='confirme', date_confirmation=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
}
if (($_POST['action'] ?? '') === 'delete_payment') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM paiements WHERE id=?')->execute([$id]);
}

$recent = [];
try {
  $recent = $pdo->query("SELECT p.id, c.nom as client, p.montant, p.statut, p.date_creation
                         FROM paiements p JOIN clients c ON c.id=p.client_id
                         ORDER BY p.date_creation DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $recent = []; }
?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="dashboard-card">
      <h6 class="card-title mb-3"><i class="fas fa-clock me-2"></i>Accès rapide</h6>
      <div class="d-grid gap-2">
        <a class="btn btn-primary" href="/Vogie2/public/index.php?page=horaires"><i class="fas fa-calendar-alt me-2"></i>Gérer les horaires</a>
        <a class="btn btn-outline-secondary" href="/Vogie2/public/index.php?page=trajets"><i class="fas fa-route me-2"></i>Gérer les trajets</a>
        <a class="btn btn-outline-secondary" href="/Vogie2/public/index.php?page=paiements"><i class="fas fa-receipt me-2"></i>Gérer les paiements</a>
      </div>
    </div>
    <div class="dashboard-card mt-3">
      <h6 class="card-title mb-3"><i class="fas fa-broom me-2"></i>Maintenance</h6>
      <form method="post">
        <input type="hidden" name="action" value="cleanup_empty_clients">
        <button class="btn btn-outline-danger"><i class="fas fa-user-slash me-2"></i>Supprimer clients vides</button>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="dashboard-card">
      <h6 class="card-title mb-3">Paiements récents</h6>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>
          <tbody>
            <?php if (!$recent): ?>
              <tr><td colspan="6" class="text-center text-muted">Aucun paiement</td></tr>
            <?php else: foreach ($recent as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['client']) ?></td>
                <td>MAD <?= number_format((float)$r['montant'], 2, ',', ' ') ?></td>
                <td><span class="badge <?= $r['statut']==='confirme'?'bg-success':'bg-warning text-dark' ?>"><?= htmlspecialchars($r['statut']) ?></span></td>
                <td><?= htmlspecialchars($r['date_creation']) ?></td>
                <td class="text-nowrap">
                  <?php if ($r['statut']!=='confirme'): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="confirm_payment">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-success">Confirmer</button>
                  </form>
                  <?php endif; ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?');">
                    <input type="hidden" name="action" value="delete_payment">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
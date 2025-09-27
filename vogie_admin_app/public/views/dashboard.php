<?php global $pdo; ?>
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
  <h3 class="m-0">Tableau de bord</h3>
  <div class="d-flex flex-wrap gap-2">
    <a href="/Vogie2/public/index.php?page=trajets" class="btn btn-outline-secondary"><i class="fas fa-route me-2"></i>Trajets</a>
    <a href="/Vogie2/public/index.php?page=clients" class="btn btn-outline-secondary"><i class="fas fa-user-friends me-2"></i>Clients</a>
    <a href="/Vogie2/public/index.php?page=paiements" class="btn btn-outline-secondary"><i class="fas fa-receipt me-2"></i>Paiements</a>
  </div>

</div>
<?php include __DIR__ . "/../widgets/dashboard_kpis.php"; ?>
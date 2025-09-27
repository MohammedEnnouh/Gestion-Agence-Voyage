<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

if (!isset($conn) || !$conn) {
    $conn = getDBConnection();
}
$auth = new AuthController($conn, $config);
$auth->requireAuth();
$user = $auth->getCurrentUser();

try {

    if (!($conn instanceof mysqli)) { $conn = getDBConnection(); }

    $cols = [];
    if ($res = $conn->query("SHOW COLUMNS FROM bookings")) {
        while ($r = $res->fetch_assoc()) { $cols[$r['Field']] = true; }
        $res->close();
    }

    $hasBookingsEmail = isset($cols['email']);

    if ($hasBookingsEmail) {
        $stmt = $conn->prepare("UPDATE bookings b SET b.user_id = ? WHERE (b.user_id IS NULL OR b.user_id = 0) AND b.email = ?");
        $stmt->bind_param('is', $user['id'], $user['email']);
        $stmt->execute();
        $stmt->close();
    }

    else if (isset($cols['phone']) && !empty($user['phone'])) {
        $stmt = $conn->prepare("UPDATE bookings b SET b.user_id = ? WHERE (b.user_id IS NULL OR b.user_id = 0) AND b.phone = ?");
        $stmt->bind_param('is', $user['id'], $user['phone']);
        $stmt->execute();
        $stmt->close();
    }

} catch (Exception $e) {

}

$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$conditions = 'b.user_id = ?';
$params = [$user['id']];
$types = 'i';

if ($filter === 'upcoming') {
    $conditions .= ' AND (b.departure_date >= CURDATE())';
} elseif ($filter === 'completed') {
    $conditions .= ' AND (b.departure_date < CURDATE())';
}

$total = 0;
try {
    $conn = getDBConnection();

    $sqlCount = "SELECT COUNT(*) AS cnt FROM bookings b WHERE $conditions";
    $stmt = $conn->prepare($sqlCount);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $total = (int)$row['cnt']; }
    $stmt->close();

    $sql = "
        SELECT b.*,
               fc.name AS from_city_name,
               tc.name AS to_city_name
        FROM bookings b
        LEFT JOIN cities fc ON fc.id = b.from_city_id
        LEFT JOIN cities tc ON tc.id = b.to_city_id
        WHERE $conditions
        ORDER BY COALESCE(b.departure_date, b.booking_date) DESC, b.id DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);

    $types2 = $types . 'ii';
    $params2 = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($types2, ...$params2);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) { $bookings[] = $row; }
    $stmt->close();
} catch (Exception $e) {
    $error = 'Erreur lors du chargement de vos réservations.';
}

$totalPages = max(1, (int)ceil($total / $perPage));
$pageTitle = 'Mes Réservations - ' . $config['app_name'];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <h2 class="mb-3 mb-md-0"><i class="fas fa-suitcase me-2 text-primary"></i>Mes Réservations</h2>
    <div class="btn-group" role="group" aria-label="Filtre des réservations">
      <a href="my-bookings.php" class="btn btn-outline-primary<?= $filter==='all'?' active':'' ?>">Toutes</a>
      <a href="my-bookings.php?filter=upcoming" class="btn btn-outline-primary<?= $filter==='upcoming'?' active':'' ?>">À venir</a>
      <a href="my-bookings.php?filter=completed" class="btn btn-outline-primary<?= $filter==='completed'?' active':'' ?>">Terminées</a>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (empty($bookings)): ?>
    <div class="text-center p-5 bg-light rounded">
      <i class="fas fa-suitcase-rolling fa-3x text-muted mb-3"></i>
      <h5 class="mb-1">Aucune réservation trouvée</h5>
      <p class="text-muted">Vous n'avez pas encore de réservations dans cette section.</p>
      <a href="booking.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Faire une réservation</a>
    </div>
  <?php else: ?>
    <div class="table-responsive shadow-sm rounded">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Référence</th>
            <th>Trajet</th>
            <th>Date/Heure</th>
            <th>Passagers</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <?php
              $trajet = '';
              if (!empty($b['from_city_name']) && !empty($b['to_city_name'])) {
                $trajet = $b['from_city_name'] . ' → ' . $b['to_city_name'];
              } elseif (!empty($b['destination'])) {
                $trajet = $b['destination'];
              }
              $status = strtolower($b['payment_status'] ?? ($b['status'] ?? 'pending'));
              $badge = 'secondary';
              if ($status === 'confirmed' || $status === 'paid') $badge = 'success';
              elseif ($status === 'pending') $badge = 'warning';
              elseif ($status === 'cancelled') $badge = 'danger';
            ?>
            <tr>
              <td>
                <a href="confirmation.php?booking_id=<?php echo (int)$b['id']; ?>" class="text-decoration-none">
                  <code><?php echo htmlspecialchars($b['booking_reference'] ?? ('#'.$b['id'])); ?></code>
                </a>
              </td>
              <td>
                <a href="confirmation.php?booking_id=<?php echo (int)$b['id']; ?>" class="text-decoration-none">
                  <?php echo htmlspecialchars($trajet ?: '—'); ?>
                </a>
              </td>
              <td>
                <?php echo htmlspecialchars($b['departure_date'] ?? ''); ?>
                <?php if (!empty($b['departure_time'])): ?>
                  <small class="text-muted"><?php echo htmlspecialchars($b['departure_time']); ?></small>
                <?php endif; ?>
              </td>
              <td><?php echo (int)($b['passenger_count'] ?? $b['travelers_count'] ?? 1); ?></td>
              <td><strong><?php echo number_format((float)($b['total_amount'] ?? 0), 2, ',', ' '); ?> MAD</strong></td>
              <td><span class="badge bg-<?php echo $badge; ?> bg-opacity-10 text-<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span></td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <a href="confirmation.php?booking_id=<?php echo (int)$b['id']; ?>" class="btn btn-outline-secondary">
                    <i class="far fa-eye me-1"></i> Détails
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="mt-4" aria-label="Pagination des réservations">
        <ul class="pagination justify-content-center">
          <li class="page-item<?= $page<=1?' disabled':'' ?>">
            <a class="page-link" href="my-bookings.php?filter=<?= urlencode($filter) ?>&page=<?= $page-1 ?>" aria-label="Précédent">&laquo;</a>
          </li>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item<?= $p===$page?' active':'' ?>">
              <a class="page-link" href="my-bookings.php?filter=<?= urlencode($filter) ?>&page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item<?= $page>=$totalPages?' disabled':'' ?>">
            <a class="page-link" href="my-bookings.php?filter=<?= urlencode($filter) ?>&page=<?= $page+1 ?>" aria-label="Suivant">&raquo;</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
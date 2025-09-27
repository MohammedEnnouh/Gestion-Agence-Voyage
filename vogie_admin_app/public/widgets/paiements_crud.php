<?php

try {
  $hasBookings = (bool)($pdo->query("SHOW TABLES LIKE 'bookings'")->fetchColumn());
  $hasUsers    = (bool)($pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn());
  $hasVilles   = (bool)($pdo->query("SHOW TABLES LIKE 'villes'")->fetchColumn());
  $hasTrajets  = (bool)($pdo->query("SHOW TABLES LIKE 'trajets'")->fetchColumn());
} catch (Throwable $e) { $hasBookings=$hasUsers=$hasVilles=$hasTrajets=false; }
if (!$hasBookings || !$hasUsers || !$hasVilles || !$hasTrajets) {
  echo '<div class="alert alert-warning">Tables manquantes. Assurez-vous d\'avoir importé le dump SQL et exécuté la migration (bookings, users, villes, trajets).</div>';
  return;
}

try { $clients = $pdo->query("SELECT id, full_name FROM users WHERE role='user' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC); }
catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de lecture des utilisateurs: ' . htmlspecialchars($e->getMessage()) . '</div>'; $clients=[]; }
try { $trajets = $pdo->query('SELECT t.id, t.prix, vd.id as from_id, va.id as to_id, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC); }
catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de lecture des trajets: ' . htmlspecialchars($e->getMessage()) . '</div>'; $trajets=[]; }

function getAvatarColor($name) {
  $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe'];
  return $colors[crc32($name) % count($colors)];
}

if (($_POST['action'] ?? '') === 'create_booking') {
  $user_id = (int)($_POST['client_id'] ?? 0);
  $client_name = trim($_POST['client_name'] ?? '');
  $trajet_id = (int)($_POST['trajet_id'] ?? 0);
  $passengers = max(1, (int)($_POST['passenger_count'] ?? 1));
  $dep_date = trim($_POST['departure_date'] ?? '');
  $dep_time = trim($_POST['departure_time'] ?? '');
  $mode = in_array(($_POST['mode'] ?? 'cash'), ['cash','stripe'], true) ? $_POST['mode'] : 'cash';
  $stripe_payment_id = trim($_POST['stripe_payment_id'] ?? '');

  if (!$user_id && $client_name !== '') {
    try {
      $find = $pdo->prepare('SELECT id FROM users WHERE full_name=? LIMIT 1');
      $find->execute([$client_name]);
      $existing = $find->fetch(PDO::FETCH_ASSOC);
      if ($existing) {
        $user_id = (int)$existing['id'];
      } else {
        $insUser = $pdo->prepare("INSERT INTO users(full_name, role) VALUES(?, 'user')");
        $insUser->execute([$client_name]);
        $user_id = (int)$pdo->lastInsertId();
      }
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur création client: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }

  if ($user_id && $trajet_id && $dep_date && $dep_time) {
    try {
      $stmt = $pdo->prepare('SELECT prix, ville_depart_id, ville_arrivee_id FROM trajets WHERE id=?');
      $stmt->execute([$trajet_id]);
      $t = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($t) {
        $amount = (float)$t['prix'] * $passengers;

        try { $ref = 'VOGIE-' . strtoupper(bin2hex(random_bytes(6))); } catch (Throwable $e) { $ref = 'VOGIE-' . strtoupper(dechex(time())); }
        $ins = $pdo->prepare("INSERT INTO bookings(user_id, from_city_id, to_city_id, departure_date, departure_time, passenger_count, payment_method, payment_status, stripe_payment_id, total_amount, booking_reference) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        $ins->execute([$user_id, (int)$t['ville_depart_id'], (int)$t['ville_arrivee_id'], $dep_date, $dep_time, $passengers, $mode, 'pending', $mode==='stripe' && $stripe_payment_id!=='' ? $stripe_payment_id : null, $amount, $ref]);
      }
    } catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la création de la réservation: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  } else if (!$user_id && $client_name === '') {
    echo '<div class="alert alert-warning">Veuillez sélectionner un client existant ou entrer un nouveau nom.</div>';
  }
}

if (($_POST['action'] ?? '') === 'confirm_booking') {
  $id = (int)($_POST['id'] ?? 0);
  $newStripeId = trim($_POST['stripe_payment_id'] ?? '');
  if ($id) {
    try {
      $st = $pdo->prepare("SELECT payment_method, payment_status, stripe_payment_id FROM bookings WHERE id=?");
      $st->execute([$id]);
      $b = $st->fetch(PDO::FETCH_ASSOC);
      if ($b && $b['payment_status']!=='completed') {
        if ($b['payment_method'] === 'stripe') {

          if ($newStripeId !== '' && $newStripeId !== $b['stripe_payment_id']) {
            $upd = $pdo->prepare("UPDATE bookings SET payment_status='completed', stripe_payment_id=? WHERE id=?");
            $upd->execute([$newStripeId, $id]);
          } else {
            $pdo->prepare("UPDATE bookings SET payment_status='completed' WHERE id=?")->execute([$id]);
          }
        } else {

          $pdo->prepare("UPDATE bookings SET payment_status='completed' WHERE id=?")->execute([$id]);
        }
      }
    } catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur de confirmation: ' . htmlspecialchars($e->getMessage()) . '</div>'; }
  }
}

if (($_POST['action'] ?? '') === 'delete_booking') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) { try { $pdo->prepare('DELETE FROM bookings WHERE id=?')->execute([$id]); } catch (Throwable $e) { echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>'; } }
}

try {
  $rows = $pdo->query("SELECT b.*, u.full_name as client, vd.nom as depart, va.nom as arrivee FROM bookings b JOIN users u ON u.id=b.user_id JOIN villes vd ON vd.id=b.from_city_id JOIN villes va ON va.id=b.to_city_id ORDER BY b.id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-danger">Erreur de lecture des réservations: ' . htmlspecialchars($e->getMessage()) . '</div>';
  $rows = [];
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="mb-1">Bookings Management</h2>
    <p class="text-muted mb-0">Manage travel bookings and payments</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary">
      <i class="fas fa-download"></i> Export
    </button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookingModal">
      <i class="fas fa-plus"></i> New Booking
    </button>
  </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-primary">
            <i class="fas fa-calendar-check"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= count($rows) ?></div>
          <div class="dashboard-label">Total Bookings</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +15% This month
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-success">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= count(array_filter($rows, fn($r) => $r['payment_status'] === 'completed')) ?></div>
          <div class="dashboard-label">Completed</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +8% This week
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-warning">
            <i class="fas fa-clock"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= count(array_filter($rows, fn($r) => $r['payment_status'] === 'pending')) ?></div>
          <div class="dashboard-label">Pending</div>
          <div class="dashboard-growth negative">
            <i class="fas fa-arrow-down"></i> -5% This week
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-info">
            <i class="fas fa-coins"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= number_format(array_sum(array_map(fn($r) => $r['payment_status'] === 'completed' ? $r['total_amount'] : 0, $rows)), 0) ?></div>
          <div class="dashboard-label">Revenue (MAD)</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +22% This month
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bookings Table -->
<div class="dashboard-card">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="card-title mb-0">All Bookings</h6>
    <div class="d-flex gap-2">
      <div class="input-group" style="width: 250px;">
        <input type="text" class="form-control" placeholder="Search bookings...">
        <button class="btn btn-outline-secondary">
          <i class="fas fa-search"></i>
        </button>
      </div>
      <button class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Reference</th>
          <th>Customer</th>
          <th>Route</th>
          <th>Date & Time</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No bookings found</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td>
              <span class="fw-medium">
            </td>
            <td>
              <code class="text-primary"><?= htmlspecialchars($r['booking_reference'] ?? '') ?></code>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar-sm me-2" style="background: <?= getAvatarColor($r['client']) ?>">
                  <?= strtoupper(substr($r['client'], 0, 2)) ?>
                </div>
                <div>
                  <div class="fw-medium"><?= htmlspecialchars($r['client']) ?></div>
                  <small class="text-muted"><?= $r['passenger_count'] ?> passenger<?= $r['passenger_count'] > 1 ? 's' : '' ?></small>
                </div>
              </div>
            </td>
            <td>
              <div>
                <div class="fw-medium"><?= htmlspecialchars($r['depart']) ?> → <?= htmlspecialchars($r['arrivee']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($r['payment_method']) ?></small>
              </div>
            </td>
            <td>
              <div>
                <div class="fw-medium"><?= date('d/m/Y', strtotime($r['departure_date'])) ?></div>
                <small class="text-muted"><?= date('H:i', strtotime($r['departure_time'])) ?></small>
              </div>
            </td>
            <td>
              <span class="fw-medium"><?= number_format((float)$r['total_amount'], 2) ?> MAD</span>
            </td>
            <td>
              <?php
                $statusClass = $r['payment_status'] === 'completed' ? 'bg-success' :
                              ($r['payment_status'] === 'failed' ? 'bg-danger' : 'bg-warning text-dark');
                $statusText = ucfirst($r['payment_status']);
              ?>
              <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
            </td>
            <td>
              <div class="btn-group">
                <?php if ($r['payment_status'] !== 'completed'): ?>
                  <button class="btn btn-sm btn-success" onclick="confirmBooking(<?= (int)$r['id'] ?>, '<?= $r['payment_method'] ?>', '<?= htmlspecialchars($r['stripe_payment_id'] ?? '') ?>')">
                    <i class="fas fa-check"></i>
                  </button>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-secondary" href="/Vogie2/public/receipt.php?id=<?= (int)$r['id'] ?>" target="_blank">
                  <i class="fas fa-receipt"></i>
                </a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this booking?');">
                  <input type="hidden" name="action" value="delete_booking">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="action" value="create_booking">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Client</label>
              <select name="client_id" class="form-select">
                <option value="">Select existing client</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted d-block mt-1">Or type a new client name</small>
              <input name="client_name" class="form-control mt-2" placeholder="Enter client full name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Route</label>
              <select name="trajet_id" class="form-select" required>
                <option value="">Select route</option>
                <?php foreach ($trajets as $t): ?>
                  <option value="<?= (int)$t['id'] ?>">
                    <?= htmlspecialchars($t['depart']) ?> → <?= htmlspecialchars($t['arrivee']) ?>
                    (<?= number_format((float)$t['prix'], 2) ?> MAD)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Departure Date</label>
              <input name="departure_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Departure Time</label>
              <input name="departure_time" type="time" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Passengers</label>
              <input name="passenger_count" type="number" min="1" class="form-control" value="1" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment Method</label>
              <select name="mode" class="form-select" onchange="toggleStripeField(this.value)">
                <option value="cash">Cash</option>
                <option value="stripe">Stripe</option>
              </select>
            </div>
            <div class="col-12" id="stripeField" style="display: none;">
              <label class="form-label">Stripe Payment ID (Optional)</label>
              <input name="stripe_payment_id" class="form-control" placeholder="Enter Stripe Payment ID">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Booking</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Booking Modal -->
<div class="modal fade" id="confirmBookingModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="action" value="confirm_booking">
          <input type="hidden" name="id" id="confirm-booking-id">
          <div id="stripePaymentField" style="display: none;">
            <label class="form-label">Stripe Payment ID</label>
            <input name="stripe_payment_id" id="confirm-stripe-id" class="form-control" placeholder="Enter Stripe Payment ID">
          </div>
          <p>Are you sure you want to confirm this payment?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleStripeField(paymentMethod) {
  const stripeField = document.getElementById('stripeField');
  stripeField.style.display = paymentMethod === 'stripe' ? 'block' : 'none';
}

function confirmBooking(id, paymentMethod, currentStripeId) {
  document.getElementById('confirm-booking-id').value = id;
  const stripeField = document.getElementById('stripePaymentField');
  const stripeInput = document.getElementById('confirm-stripe-id');

  if (paymentMethod === 'stripe') {
    stripeField.style.display = 'block';
    stripeInput.value = currentStripeId;
  } else {
    stripeField.style.display = 'none';
  }

  new bootstrap.Modal(document.getElementById('confirmBookingModal')).show();
}
</script>
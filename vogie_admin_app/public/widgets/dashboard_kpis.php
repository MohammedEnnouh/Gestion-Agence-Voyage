<?php
global $pdo;
try {
  $villesCount = (int)$pdo->query('SELECT COUNT(*) FROM villes')->fetchColumn();
} catch (Throwable $e) { $villesCount = 0; }
try {
  $trajetsCount = (int)$pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
} catch (Throwable $e) { $trajetsCount = 0; }
try {
  $clientsCount = (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
} catch (Throwable $e) { $clientsCount = 0; }
try {
  $totalEncaisse = (float)$pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut='confirme'")->fetchColumn();
} catch (Throwable $e) { $totalEncaisse = 0; }

$villesGrowth = "+8.5%";
$trajetsGrowth = "-0.5%";
$clientsGrowth = "+15.3%";
$encaisseGrowth = "+12.5%";

$rows = [];
$bookingsCount = 0;
try {
  $hasBookings = (bool)($pdo->query("SHOW TABLES LIKE 'bookings'")->fetchColumn());
  if ($hasBookings) {

    $bookingsCount = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    $rows = $pdo->query("SELECT b.id,
                               u.full_name AS client,
                               b.booking_reference AS ref,
                               CONCAT(b.departure_date, ' ', b.departure_time) AS order_dt,
                               b.payment_status AS statut,
                               b.total_amount AS montant
                        FROM bookings b
                        JOIN users u ON u.id=b.user_id
                        ORDER BY b.id DESC
                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Throwable $e) { $rows = []; $bookingsCount = 0; }

if (!$rows) {

  try {
    $bookingsCount = (int)$pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
    $rows = $pdo->query("SELECT p.id,
                               c.nom AS client,
                               NULL AS ref,
                               p.date_creation AS order_dt,
                               p.statut,
                               p.montant
                        FROM paiements p JOIN clients c ON c.id=p.client_id
                        ORDER BY p.date_creation DESC
                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) { $rows = []; $bookingsCount = 0; }
}
?>

<!-- KPI Cards Row -->
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-primary">
            <i class="fas fa-city"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= number_format($villesCount) ?></div>
          <div class="dashboard-label">Villes</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> <?= $villesGrowth ?> Since last week
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-primary">
            <i class="fas fa-calendar-check"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= number_format($bookingsCount) ?></div>
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
          <div class="dashboard-icon bg-warning">
            <i class="fas fa-percentage"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number">85%</div>
          <div class="dashboard-label">Booking Rate</div>
          <div class="dashboard-growth negative">
            <i class="fas fa-arrow-down"></i> <?= $trajetsGrowth ?> Since last week
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
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= number_format($clientsCount) ?></div>
          <div class="dashboard-label">Total Clients</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> <?= $clientsGrowth ?> Since last week
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Charts Row -->

<?php

function getAvatarColor($name) {
  $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe'];
  return $colors[crc32($name) % count($colors)];
}
?>

<!-- Recent Bookings Table -->
<div class="dashboard-card">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="card-title mb-0">Recent Bookings</h6>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-light">
        <i class="fas fa-filter"></i> Filter
      </button>
      <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="fas fa-ellipsis-h"></i>
        </button>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Booking Ref</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No bookings found</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td>
              <span class="fw-medium">
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar-sm me-2" style="background: <?= getAvatarColor($r['client']) ?>">
                  <?= strtoupper(substr($r['client'], 0, 2)) ?>
                </div>
                <span class="fw-medium"><?= htmlspecialchars($r['client']) ?></span>
              </div>
            </td>
            <td>
              <code class="text-muted"><?= htmlspecialchars($r['ref'] ?? 'N/A') ?></code>
            </td>
            <td>
              <span class="text-muted"><?= htmlspecialchars($r['order_dt'] ?? '') ?></span>
            </td>
            <td>
              <?php
              $s = strtolower((string)($r['statut'] ?? ''));
              if (in_array($s, ['completed','paid','confirme'], true)) { $statusClass='bg-success'; $statusText='Paid'; }
              elseif (in_array($s, ['failed','cancelled'], true)) { $statusClass='bg-danger'; $statusText='Failed'; }
              else { $statusClass='bg-warning text-dark'; $statusText='Pending'; }
              ?>
              <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
            </td>
            <td>
              <span class="fw-bold">MAD <?= number_format((float)($r['montant'] ?? 0), 2, ',', ' ') ?></span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const bookingCtx = document.getElementById('bookingChart').getContext('2d');
new Chart(bookingCtx, {
  type: 'doughnut',
  data: {
    labels: ['Clients', 'Routes', 'Pending'],
    datasets: [{
      data: [<?= $clientsCount ?>, <?= $trajetsCount ?>, <?= max(0, $clientsCount - $trajetsCount) ?>],
      backgroundColor: ['#667eea', '#6c757d', '#e9ecef'],
      borderWidth: 0,
      cutout: '70%'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.8)',
        titleColor: '#fff',
        bodyColor: '#fff',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1
      }
    }
  }
});

const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Revenue',
      data: [12000, 15000, 18000, 14000, 22000, 25000, 28000, 24000, 26000, 30000, 27000, 32000],
      borderColor: '#667eea',
      backgroundColor: 'rgba(102, 126, 234, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#667eea',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.8)',
        titleColor: '#fff',
        bodyColor: '#fff',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { color: '#6c757d' }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#6c757d' }
      }
    }
  }
});
</script>
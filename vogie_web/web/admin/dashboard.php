<?php
require_once 'auth.php';
requireAdmin();

require_once '../config/config.php';

try {
	$conn = getDBConnection();

	$stats = [];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['bookings'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE payment_method = 'cash' AND payment_status = 'pending'");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['pending_cash'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE payment_method = 'stripe' AND payment_status = 'completed'");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['online_completed'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM cities");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['cities'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM routes");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['routes'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM route_times");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['times'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
	$stmt->execute();
	$result = $stmt->get_result();
	$stats['users'] = $result->fetch_assoc()['total'];

	$stmt = $conn->prepare("SELECT id, name, description FROM cities ORDER BY name LIMIT 10");
	$stmt->execute();
	$cities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT r.id, r.price_per_person, c1.name as from_city, c2.name as to_city FROM routes r JOIN cities c1 ON r.from_city_id=c1.id JOIN cities c2 ON r.to_city_id=c2.id ORDER BY c1.name, c2.name LIMIT 10");
	$stmt->execute();
	$routes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT rt.id, rt.time, rt.is_active, r.id as route_id, c1.name as from_city, c2.name as to_city FROM route_times rt JOIN routes r ON rt.route_id=r.id JOIN cities c1 ON r.from_city_id=c1.id JOIN cities c2 ON r.to_city_id=c2.id ORDER BY c1.name, c2.name, rt.time LIMIT 10");
	$stmt->execute();
	$times = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT id, full_name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC LIMIT 10");
	$stmt->execute();
	$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT b.id, b.booking_reference, b.total_amount, b.payment_status, b.departure_date, b.departure_time, u.full_name, c1.name as from_city, c2.name as to_city FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN cities c1 ON b.from_city_id=c1.id LEFT JOIN cities c2 ON b.to_city_id=c2.id WHERE b.payment_method='cash' ORDER BY b.booking_date DESC LIMIT 10");
	$stmt->execute();
	$cash_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT b.id, b.booking_reference, b.total_amount, b.payment_status, b.stripe_payment_id, b.booking_date, u.full_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id WHERE b.payment_method='stripe' ORDER BY b.booking_date DESC LIMIT 10");
	$stmt->execute();
	$online_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$stmt->close();
} catch (Exception $e) {
	$error = "Erreur: " . $e->getMessage();
} finally {
	if (isset($conn)) {
		$conn->close();
	}
}

$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Dashboard - Vogie</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		.stat-card {
			background: white;
			border-radius: 15px;
			padding: 1.5rem;
			box-shadow: 0 5px 15px rgba(0,0,0,0.1);
			margin-bottom: 1rem;
		}
		.sidebar {
			background:
			min-height: 100vh;
			color: white;
		}
		.section-card { background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.06); margin-bottom: 1rem; }
		.section-card .card-header { background:
		.badge-soft { background:
	</style>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<!-- Sidebar -->
			<div class="col-md-3 col-lg-2 px-0">
				<div class="sidebar p-3">
					<h4 class="text-center mb-4">VOGIE Admin</h4>
					<nav class="nav flex-column">
						<a class="nav-link text-white active" href="dashboard.php">
							<i class="fas fa-tachometer-alt me-2"></i>Dashboard
						</a>
						<a class="nav-link text-white-50" href="cities.php">
							<i class="fas fa-city me-2"></i>Villes
						</a>
						<a class="nav-link text-white-50" href="routes.php">
							<i class="fas fa-route me-2"></i>Trajets
						</a>
						<a class="nav-link text-white-50" href="cash-payments.php">
							<i class="fas fa-money-bill-wave me-2"></i>Paiements Espèces
						</a>
						<a class="nav-link text-white-50" href="logout.php">
							<i class="fas fa-sign-out-alt me-2"></i>Déconnexion
						</a>
					</nav>
				</div>
			</div>

			<!-- Main Content -->
			<div class="col-md-9 col-lg-10 p-4">
				<div class="d-flex justify-content-between align-items-center mb-4">
					<h2>Dashboard Administrateur</h2>
					<div class="text-muted">
						<i class="fas fa-user me-2"></i>
						<?php echo htmlspecialchars($admin['full_name']); ?>
					</div>
				</div>

				<?php if (isset($error)): ?>
					<div class="alert alert-danger"><?php echo $error; ?></div>
				<?php endif; ?>

				<!-- Statistics -->
				<div class="row">
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-primary mb-1"><?php echo $stats['bookings'] ?? 0; ?></h4>
							<small class="text-muted">Réservations</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-warning mb-1"><?php echo $stats['pending_cash'] ?? 0; ?></h4>
							<small class="text-muted">Espèces en attente</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-success mb-1"><?php echo $stats['online_completed'] ?? 0; ?></h4>
							<small class="text-muted">Paiements en ligne</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-info mb-1"><?php echo $stats['cities'] ?? 0; ?></h4>
							<small class="text-muted">Villes</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-secondary mb-1"><?php echo $stats['routes'] ?? 0; ?></h4>
							<small class="text-muted">Trajets</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="stat-card">
							<h4 class="text-dark mb-1"><?php echo $stats['times'] ?? 0; ?></h4>
							<small class="text-muted">Horaires</small>
						</div>
					</div>
				</div>

				<!-- One-page management sections -->
				<div class="row mt-3">
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fas fa-city me-2"></i>Villes</h5>
								<a class="btn btn-sm btn-primary" href="cities.php"><i class="fas fa-plus me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($cities)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($cities as $c): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span><?php echo htmlspecialchars($c['name']); ?></span>
												<small class="text-muted">ID: <?php echo $c['id']; ?></small>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucune ville</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fas fa-route me-2"></i>Trajets</h5>
								<a class="btn btn-sm btn-primary" href="routes.php"><i class="fas fa-plus me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($routes)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($routes as $r): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span><?php echo htmlspecialchars($r['from_city'] . ' → ' . $r['to_city']); ?></span>
												<small class="text-muted"><?php echo number_format($r['price_per_person'], 2); ?> MAD</small>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucun trajet</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Paiements Espèces</h5>
								<a class="btn btn-sm btn-warning" href="cash-payments.php"><i class="fas fa-wrench me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($cash_payments)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($cash_payments as $p): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span>
													<strong><?php echo htmlspecialchars($p['booking_reference'] ?? 'N/A'); ?></strong>
													<small class="text-muted ms-2"><?php echo htmlspecialchars(($p['from_city'] ?? 'N/A') . ' → ' . ($p['to_city'] ?? 'N/A')); ?></small>
												</span>
												<span class="badge <?php echo $p['payment_status'] === 'completed' ? 'bg-success' : 'bg-warning'; ?>"><?php echo ucfirst($p['payment_status']); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucun paiement espèces</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fab fa-cc-stripe me-2"></i>Paiements en Ligne</h5>
								<a class="btn btn-sm btn-info text-white" href="bookings.php"><i class="fas fa-wrench me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($online_payments)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($online_payments as $op): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span>
													<strong><?php echo htmlspecialchars($op['booking_reference'] ?? 'N/A'); ?></strong>
													<small class="text-muted ms-2"><?php echo htmlspecialchars($op['full_name'] ?? 'N/A'); ?></small>
												</span>
												<small class="text-success"><?php echo number_format($op['total_amount'], 2); ?> MAD</small>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucun paiement en ligne</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fas fa-clock me-2"></i>Horaires</h5>
								<a class="btn btn-sm btn-secondary" href="times.php"><i class="fas fa-wrench me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($times)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($times as $t): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span><?php echo htmlspecialchars($t['from_city'] . ' → ' . $t['to_city']); ?> <small class="text-muted ms-2"><?php echo substr($t['time'], 0, 5); ?></small></span>
												<span class="badge bg-<?php echo ($t['is_active'] ? 'success' : 'secondary'); ?>"><?php echo $t['is_active'] ? 'Actif' : 'Inactif'; ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucun horaire</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="card section-card">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="mb-0"><i class="fas fa-users me-2"></i>Utilisateurs</h5>
								<a class="btn btn-sm btn-dark" href="users.php"><i class="fas fa-wrench me-1"></i>Gérer</a>
							</div>
							<div class="card-body">
								<?php if (!empty($users)): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($users as $u): ?>
											<li class="list-group-item d-flex justify-content-between align-items-center">
												<span><?php echo htmlspecialchars($u['full_name']); ?> <small class="text-muted ms-2"><?php echo htmlspecialchars($u['email']); ?></small></span>
												<span class="badge bg-<?php echo ($u['is_active'] ? 'success' : 'secondary'); ?>"><?php echo $u['role']; ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else: ?>
									<p class="text-muted mb-0">Aucun utilisateur</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
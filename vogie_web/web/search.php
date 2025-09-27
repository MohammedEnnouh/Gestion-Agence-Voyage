<?php
require_once 'config/config.php';

$fromId = isset($_GET['from_id']) ? (int)$_GET['from_id'] : 0;
$toId = isset($_GET['to_id']) ? (int)$_GET['to_id'] : 0;
$date = $_GET['date'] ?? '';
$pax = isset($_GET['pax']) ? max(1, (int)$_GET['pax']) : 1;

if ($fromId <= 0 || $toId <= 0 || empty($date)) {
	header('Location: index.php');
	exit;
}

$conn = getDBConnection();
$fromName = $toName = '';
$stmt = $conn->prepare('SELECT name FROM cities WHERE id = ?');
$stmt->bind_param('i', $fromId);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) { $fromName = $row['name']; }
$stmt = $conn->prepare('SELECT name FROM cities WHERE id = ?');
$stmt->bind_param('i', $toId);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) { $toName = $row['name']; }
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Résultats - Vogie</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<div class="container">
			<a class="navbar-brand" href="index.php">Vogie</a>
		</div>
	</nav>
	<div class="container my-4">
		<h3 class="mb-3">Trajet: <?php echo htmlspecialchars($fromName . ' → ' . $toName); ?> | Date: <?php echo htmlspecialchars($date); ?> | Passagers: <?php echo $pax; ?></h3>
		<div id="results" class="row g-3"></div>
	</div>
	<script>
		async function loadTrips() {
			const params = new URLSearchParams({ from_id: '<?php echo $fromId; ?>', to_id: '<?php echo $toId; ?>', date: '<?php echo $date; ?>', pax: '<?php echo $pax; ?>' });
			const res = await fetch('api/search_trips.php?' + params.toString());
			const json = await res.json();
			const container = document.getElementById('results');
			if (!json?.success || !Array.isArray(json.data) || json.data.length === 0) {
				container.innerHTML = '<div class="col-12"><div class="alert alert-info">Aucun départ disponible pour cette date.</div></div>';
				return;
			}
			container.innerHTML = json.data.map(item => {
				const total = parseFloat(item.total);
				const time = item.time;
				return `
				<div class="col-md-4">
					<div class="card h-100">
						<div class="card-body d-flex flex-column">
							<h5 class="card-title">Départ ${time}</h5>
							<p class="mb-2">Prix par personne: <strong>${parseFloat(item.price_per_person).toFixed(2)} MAD</strong></p>
							<p class="mb-3">Total: <strong>${total.toFixed(2)} MAD</strong></p>
							<a class="btn btn-primary mt-auto" href="booking.php?from=<?php echo urlencode($fromName); ?>&to=<?php echo urlencode($toName); ?>&from_id=<?php echo $fromId; ?>&to_id=<?php echo $toId; ?>&date=<?php echo urlencode($date); ?>&pax=<?php echo $pax; ?>&time=${encodeURIComponent(time)}&price=${encodeURIComponent(total)}">
								Choisir ${time}
							</a>
						</div>
					</div>
				</div>`;
			}).join('');
		}
		loadTrips();
	</script>
</body>
</html>
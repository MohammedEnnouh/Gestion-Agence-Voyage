<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$fromId = isset($_GET['from_id']) ? (int)$_GET['from_id'] : 0;
$toId = isset($_GET['to_id']) ? (int)$_GET['to_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';
$pax = isset($_GET['pax']) ? max(1, (int)$_GET['pax']) : 1;

if ($fromId <= 0 || $toId <= 0 || empty($date)) {
	echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
	exit;
}

try {
	$conn = getDBConnection();

	$stmt = $conn->prepare('SELECT r.id as route_id, r.price_per_person FROM routes r WHERE r.from_city_id = ? AND r.to_city_id = ? LIMIT 1');
	$stmt->bind_param('ii', $fromId, $toId);
	$stmt->execute();
	$route = $stmt->get_result()->fetch_assoc();
	if (!$route) {
		echo json_encode(['success' => true, 'data' => []]);
		exit;
	}
	$price = (float)$route['price_per_person'];

	$stmt = $conn->prepare('SELECT time FROM route_times WHERE route_id = ? AND is_active = 1 ORDER BY time');
	$stmt->bind_param('i', $route['route_id']);
	$stmt->execute();
	$res = $stmt->get_result();
	$times = [];
	while ($row = $res->fetch_assoc()) {
		$times[] = [
			'time' => substr($row['time'], 0, 5),
			'price_per_person' => $price,
			'total' => $price * $pax
		];
	}
	echo json_encode(['success' => true, 'data' => $times]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
	if (isset($conn)) { $conn->close(); }
}
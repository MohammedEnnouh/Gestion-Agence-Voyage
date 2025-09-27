<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$from = isset($_GET['from_city_id']) ? (int)$_GET['from_city_id'] : 0;
$to = isset($_GET['to_city_id']) ? (int)$_GET['to_city_id'] : 0;
$passengers = isset($_GET['passengers']) ? max(1, (int)$_GET['passengers']) : 1;

if ($from <= 0 || $to <= 0) {
	echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
	exit;
}

try {
	$conn = getDBConnection();
	$stmt = $conn->prepare('SELECT price_per_person FROM routes WHERE from_city_id = ? AND to_city_id = ? LIMIT 1');
	$stmt->bind_param('ii', $from, $to);
	$stmt->execute();
	$res = $stmt->get_result();
	$pricePerPerson = 150.00;
	if ($row = $res->fetch_assoc()) {
		$pricePerPerson = (float)$row['price_per_person'];
	}
	$total = $pricePerPerson * $passengers;
	echo json_encode([
		'success' => true,
		'price_per_person' => $pricePerPerson,
		'passengers' => $passengers,
		'total' => $total
	]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
	if (isset($conn)) { $conn->close(); }
}
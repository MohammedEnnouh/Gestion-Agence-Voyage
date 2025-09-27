<?php
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once '../config/config.php';

try {
	$conn = getDBConnection();

	$stmt = $conn->query("
		SELECT
			r.id,
			r.from_city_id,
			r.to_city_id,
			r.price_per_person,
			from_city.name as from_city_name,
			to_city.name as to_city_name
		FROM routes r
		JOIN cities from_city ON r.from_city_id = from_city.id
		JOIN cities to_city ON r.to_city_id = to_city.id
		ORDER BY from_city.name, to_city.name
	");

	$routes = [];
	while ($row = $stmt->fetch_assoc()) {
		$routes[] = [
			'id' => (int)$row['id'],
			'from_city_id' => (int)$row['from_city_id'],
			'to_city_id' => (int)$row['to_city_id'],
			'from_city_name' => $row['from_city_name'],
			'to_city_name' => $row['to_city_name'],
			'price_per_person' => (float)$row['price_per_person']
		];
	}

	$response = [
		'success' => true,
		'data' => $routes
	];

	if (!headers_sent()) {
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	} else {
		return $response;
	}
} catch (Exception $e) {
	$errorResponse = [
		'success' => false,
		'message' => 'Erreur lors de la récupération des routes: ' . $e->getMessage()
	];

	if (!headers_sent()) {
		http_response_code(500);
		echo json_encode($errorResponse);
	} else {
		return $errorResponse;
	}
} finally {
	if (isset($conn)) {
		$conn->close();
	}
}
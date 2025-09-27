<?php
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once '../config/config.php';

try {
	$conn = getDBConnection();

	$stmt = $conn->query("SELECT id, name, description, image_url, base_price FROM destinations ORDER BY name");
	$destinations = [];
	while ($row = $stmt->fetch_assoc()) {
		$destinations[] = [
			'id' => (int)$row['id'],
			'name' => $row['name'],
			'description' => $row['description'] ?? 'Découvrez les merveilles de ' . $row['name'],
			'image_url' => $row['image_url'] ?? 'assets/images/v.png',
			'base_price' => (float)$row['base_price']
		];
	}

	$response = [
		'success' => true,
		'data' => $destinations
	];

	if (!headers_sent()) {
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	} else {
		return $response;
	}
} catch (Exception $e) {
	$errorResponse = [
		'success' => false,
		'message' => 'Erreur lors de la récupération des destinations: ' . $e->getMessage()
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
?>
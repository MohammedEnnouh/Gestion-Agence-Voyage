<?php
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once '../config/config.php';

try {
	$conn = getDBConnection();

	$stmt = $conn->query("SELECT id, name, description FROM cities ORDER BY name");
	$cities = [];
	while ($row = $stmt->fetch_assoc()) {
		$cities[] = [
			'id' => (int)$row['id'],
			'name' => $row['name'],
			'description' => $row['description'] ?? 'Découvrez les merveilles de ' . $row['name']
		];
	}

	$response = [
		'success' => true,
		'data' => $cities
	];

	if (!headers_sent()) {
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	} else {
		return $response;
	}
} catch (Exception $e) {
	$errorResponse = [
		'success' => false,
		'message' => 'Erreur lors de la récupération des villes: ' . $e->getMessage()
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
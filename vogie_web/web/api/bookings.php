<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $fromCityId = (int)($_POST['from_city_id'] ?? 0);
    $toCityId = (int)($_POST['to_city_id'] ?? 0);
    $departureDate = $_POST['departure_date'] ?? '';
    $departureTime = $_POST['departure_time'] ?? '';
    $passengerCount = (int)($_POST['passenger_count'] ?? 1);
    $paymentMethod = $_POST['payment_method'] ?? 'stripe';

    if (empty($fullName) || empty($email) || empty($phone) || $fromCityId <= 0 || $toCityId <= 0 || empty($departureDate) || empty($departureTime) || $passengerCount < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tous les champs sont obligatoires'
        ]);
        exit();
    }

    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullName, $email, $phone);
            $stmt->execute();
            $userId = $conn->insert_id;
        }

        $stmt = $conn->prepare("SELECT price_per_person FROM routes WHERE from_city_id = ? AND to_city_id = ?");
        $stmt->bind_param("ii", $fromCityId, $toCityId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Route non trouvée'
            ]);
            exit();
        }

        $route = $result->fetch_assoc();
        $totalAmount = $route['price_per_person'] * $passengerCount;

        $bookingReference = 'VOG' . date('Ymd') . rand(1000, 9999);
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, from_city_id, to_city_id, departure_date, departure_time, passenger_count, payment_method, total_amount, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissisds", $userId, $fromCityId, $toCityId, $departureDate, $departureTime, $passengerCount, $paymentMethod, $totalAmount, $bookingReference);
        $stmt->execute();

        $bookingId = $conn->insert_id;

        $stmt = $conn->prepare("SELECT c1.name as from_city, c2.name as to_city FROM cities c1, cities c2 WHERE c1.id = ? AND c2.id = ?");
        $stmt->bind_param("ii", $fromCityId, $toCityId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cities = $result->fetch_assoc();

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $bookingId,
                'user_id' => $userId,
                'from_city_id' => $fromCityId,
                'to_city_id' => $toCityId,
                'from_city_name' => $cities['from_city'],
                'to_city_name' => $cities['to_city'],
                'departure_date' => $departureDate,
                'departure_time' => $departureTime,
                'passenger_count' => $passengerCount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'total_amount' => $totalAmount,
                'booking_reference' => $bookingReference,
                'booking_date' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création de la réservation: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>
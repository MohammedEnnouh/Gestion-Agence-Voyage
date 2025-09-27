<?php

require_once __DIR__ . '/../config/config.php';

try {
    $conn = getDBConnection();

    $conn->begin_transaction();

    echo "Seeding database...\n";

    $tables = ['bookings', 'password_resets', 'sessions', 'users', 'destinations'];
    foreach ($tables as $table) {
        $conn->query("DELETE FROM `$table`");
        echo "Cleared table: $table\n";
    }

    $tables = ['users', 'destinations', 'bookings'];
    foreach ($tables as $table) {
        $conn->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
    }

    $destinations = [
        [
            'name' => 'Marrakech',
            'description' => 'Découvrez la ville ocre avec ses souks animés et ses palais historiques.',
            'image_url' => 'assets/images/destinations/marrakech.jpg',
            'base_price' => 150.00
        ],
        [
            'name' => 'Chefchaouen',
            'description' => 'La ville bleue nichée dans les montagnes du Rif, un véritable paradis pour les photographes.',
            'image_url' => 'assets/images/destinations/chefchaouen.jpg',
            'base_price' => 200.00
        ],
        [
            'name' => 'Merzouga',
            'description' => 'Vivez une expérience inoubliable dans le désert du Sahara avec ses dunes à perte de vue.',
            'image_url' => 'assets/images/destinations/merzouga.jpg',
            'base_price' => 300.00
        ],
        [
            'name' => 'Fès',
            'description' => 'Explorez la plus ancienne des villes impériales et son magnifique patrimoine culturel.',
            'image_url' => 'assets/images/destinations/fes.jpg',
            'base_price' => 180.00
        ],
        [
            'name' => 'Essaouira',
            'description' => 'Détendez-vous dans cette ville côtière connue pour ses plages et son ambiance décontractée.',
            'image_url' => 'assets/images/destinations/essaouira.jpg',
            'base_price' => 220.00
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO `destinations` (name, description, image_url, base_price, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

    foreach ($destinations as $destination) {
        $stmt->bind_param('sssd',
            $destination['name'],
            $destination['description'],
            $destination['image_url'],
            $destination['base_price']
        );
        $stmt->execute();
        echo "Added destination: {$destination['name']}\n";
    }

    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO `users` (first_name, last_name, email, phone, password, role, is_active, created_at, updated_at)
                 VALUES ('Admin', 'User', 'admin@vogie.com', '0612345678', '$adminPassword', 'admin', 1, NOW(), NOW())");
    echo "Added admin user: admin@vogie.com (password: admin123)\n";

    $users = [
        ['John', 'Doe', 'john@example.com', '0612345679', 'password123'],
        ['Jane', 'Smith', 'jane@example.com', '0612345680', 'password123'],
        ['Mohammed', 'Alaoui', 'mohammed@example.com', '0612345681', 'password123']
    ];

    $userStmt = $conn->prepare("INSERT INTO `users` (first_name, last_name, email, phone, password, role, is_active, created_at, updated_at)
                              VALUES (?, ?, ?, ?, ?, 'user', 1, NOW(), NOW())");

    foreach ($users as $user) {
        $hashedPassword = password_hash($user[4], PASSWORD_DEFAULT);
        $userStmt->bind_param('sssss', $user[0], $user[1], $user[2], $user[3], $hashedPassword);
        $userStmt->execute();
        echo "Added user: {$user[2]} (password: {$user[4]})\n";
    }

    $bookingStmt = $conn->prepare("INSERT INTO `bookings`
        (user_id, destination_id, passenger_count, departure_date, return_date,
         full_name, email, phone, total_amount, payment_method, payment_status,
         status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    $userIds = [];
    $result = $conn->query("SELECT id FROM `users`");
    while ($row = $result->fetch_assoc()) {
        $userIds[] = $row['id'];
    }

    $destinationIds = [];
    $result = $conn->query("SELECT id, base_price FROM `destinations`");
    while ($row = $result->fetch_assoc()) {
        $destinationIds[] = $row;
    }

    $bookingCount = 10;
    $paymentMethods = ['stripe', 'cash'];
    $paymentStatuses = ['pending', 'paid', 'failed'];
    $bookingStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];

    $firstNames = ['John', 'Jane', 'Mohammed', 'Fatima', 'Ahmed', 'Amina', 'Youssef', 'Leila'];
    $lastNames = ['Smith', 'Johnson', 'Alaoui', 'Benali', 'Khalid', 'Nasser', 'Hassan', 'Rahman'];
    $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];

    for ($i = 0; $i < $bookingCount; $i++) {
        $userId = $userIds[array_rand($userIds)];
        $destination = $destinationIds[array_rand($destinationIds)];
        $destinationId = $destination['id'];
        $basePrice = $destination['base_price'];

        $passengerCount = rand(1, 6);
        $totalAmount = $basePrice * $passengerCount;

        if ($passengerCount >= 4) {
            $totalAmount *= 0.9;
        }

        $departureDate = date('Y-m-d', strtotime('+' . rand(1, 90) . ' days'));
        $returnDate = date('Y-m-d', strtotime($departureDate . ' + ' . rand(1, 7) . ' days'));

        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $email = strtolower($firstName . '.' . $lastName . rand(1, 100) . '@' . $domains[array_rand($domains)]);
        $phone = '06' . rand(10000000, 99999999);

        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
        $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
        $status = $bookingStatuses[array_rand($bookingStatuses)];

        if ($status === 'completed' && $paymentStatus !== 'paid') {
            $paymentStatus = 'paid';
        } elseif ($status === 'cancelled' && $paymentStatus === 'paid') {
            $paymentStatus = 'refunded';
        }

        $fullName = "$firstName $lastName";

        $bookingStmt->bind_param(
            'iiissssdsss',
            $userId,
            $destinationId,
            $passengerCount,
            $departureDate,
            $returnDate,
            $fullName,
            $email,
            $phone,
            $totalAmount,
            $paymentMethod,
            $paymentStatus,
            $status
        );

        $bookingStmt->execute();
        echo "Added booking for: $fullName ($email) - " . number_format($totalAmount, 2) . " MAD\n";
    }

    $conn->commit();

    echo "\nDatabase seeding completed successfully!\n";

} catch (Exception $e) {

    if (isset($conn)) {
        $conn->rollback();
    }

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

function getDBConnection() {
    static $conn = null;

    if ($conn === null) {

        $dotenv = parse_ini_file(__DIR__ . '/../.env');

        if ($dotenv === false) {
            throw new Exception('Could not load .env file');
        }

        $host = $dotenv['DB_HOST'] ?? 'localhost';
        $dbname = $dotenv['DB_DATABASE'] ?? 'vogie_web';
        $username = $dotenv['DB_USERNAME'] ?? 'root';
        $password = $dotenv['DB_PASSWORD'] ?? '';
        $port = $dotenv['DB_PORT'] ?? '3306';

        $conn = new mysqli($host, $username, $password, $dbname, $port);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}
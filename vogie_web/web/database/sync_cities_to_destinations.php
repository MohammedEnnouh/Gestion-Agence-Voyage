<?php
require_once '../config/config.php';
$conn = getDBConnection();

$cities = [];
$res = $conn->query("SELECT name FROM cities");
while ($row = $res->fetch_assoc()) {
    $cities[] = $row['name'];
}

$destinations = [];
$res = $conn->query("SELECT name FROM destinations");
while ($row = $res->fetch_assoc()) {
    $destinations[] = $row['name'];
}

$added = 0;
foreach ($cities as $city) {
    if (!in_array($city, $destinations)) {
        $stmt = $conn->prepare("INSERT INTO destinations (name, description, image_url, base_price) VALUES (?, '', '', 0)");
        $stmt->bind_param("s", $city);
        $stmt->execute();
        $added++;
    }
}
echo "Ajouté $added destinations manquantes.";
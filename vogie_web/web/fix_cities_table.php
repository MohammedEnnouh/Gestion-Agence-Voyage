<?php
require_once 'config/config.php';

try {
    $conn = getDBConnection();

    $result = $conn->query("SHOW COLUMNS FROM cities LIKE 'description'");
    if ($result->num_rows == 0) {

        $alterSQL = "ALTER TABLE cities ADD COLUMN description TEXT AFTER name";
        if ($conn->query($alterSQL) === TRUE) {
            echo "Description column added successfully<br>";
        } else {
            echo "Error adding description column: " . $conn->error . "<br>";
        }
    } else {
        echo "Description column already exists<br>";
    }

    $cities = [
        'Marrakech' => 'Marrakech, une ancienne ville impériale dans l\'ouest du Maroc, est un centre économique majeur et abrite des mosquées, palais et jardins.',
        'Chefchaouen' => 'Chefchaouen est une ville dans les montagnes du Rif au nord-ouest du Maroc. Elle est connue pour les bâtiments bleus de sa vieille ville.',
        'Fes' => 'Fes est une ville du nord-est du Maroc souvent appelée la capitale culturelle du pays.',
        'Casablanca' => 'Casablanca est une ville portuaire et un centre commercial dans l\'ouest du Maroc, face à l\'océan Atlantique.',
        'Rabat' => 'Rabat, la capitale du Maroc, s\'étend le long des rives de la rivière Bouregreg et de l\'océan Atlantique.',
        'Tangier' => 'Tanger est une ville portuaire au Maroc située à l\'entrée ouest du détroit de Gibraltar.',
        'Essaouira' => 'Essaouira est une ville portuaire et une station balnéaire sur la côte atlantique du Maroc.',
        'Meknes' => 'Meknes est une ville du nord du Maroc connue pour son passé impérial, avec des monuments historiques.',
        'Ouarzazate' => 'Ouarzazate est une ville dans la région Drâa-Tafilalet du centre-sud du Maroc, connue comme une porte d\'entrée vers le désert du Sahara.',
        'Agadir' => 'Agadir est une ville sur la côte atlantique sud du Maroc, connue pour ses plages et ses stations balnéaires.',
        'Tetouan' => 'Tétouan est une ville du nord du Maroc, connue pour son architecture influencée par l\'Andalousie.',
        'Dakhla' => 'Dakhla est une ville dans le territoire contesté du Sahara occidental, actuellement administré par le Maroc.'
    ];

    $stmt = $conn->prepare("UPDATE cities SET description = ? WHERE name = ?");

    foreach ($cities as $cityName => $description) {
        $stmt->bind_param("ss", $description, $cityName);
        if ($stmt->execute()) {
            echo "Updated description for: " . $cityName . "<br>";
        } else {
            echo "Error updating " . $cityName . ": " . $stmt->error . "<br>";
        }
    }

    $stmt->close();

    echo "<h3>Current cities in database:</h3>";
    $result = $conn->query("SELECT id, name, description FROM cities ORDER BY name LIMIT 20");
    while ($row = $result->fetch_assoc()) {
        echo "<strong>" . $row['name'] . "</strong>: " . ($row['description'] ?? 'No description') . "<br><br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
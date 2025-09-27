<?php
require_once 'config/config.php';

try {
    $conn = getDBConnection();

    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($createTableSQL) === TRUE) {
        echo "Cities table created successfully or already exists<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    $result = $conn->query("SELECT COUNT(*) as count FROM cities");
    $row = $result->fetch_assoc();

    if ($row['count'] == 0) {

        $cities = [
            ['Marrakech', 'Marrakech, une ancienne ville impériale dans l\'ouest du Maroc, est un centre économique majeur et abrite des mosquées, palais et jardins.'],
            ['Chefchaouen', 'Chefchaouen est une ville dans les montagnes du Rif au nord-ouest du Maroc. Elle est connue pour les bâtiments bleus de sa vieille ville.'],
            ['Fes', 'Fes est une ville du nord-est du Maroc souvent appelée la capitale culturelle du pays.'],
            ['Casablanca', 'Casablanca est une ville portuaire et un centre commercial dans l\'ouest du Maroc, face à l\'océan Atlantique.'],
            ['Rabat', 'Rabat, la capitale du Maroc, s\'étend le long des rives de la rivière Bouregreg et de l\'océan Atlantique.'],
            ['Tangier', 'Tanger est une ville portuaire au Maroc située à l\'entrée ouest du détroit de Gibraltar.'],
            ['Essaouira', 'Essaouira est une ville portuaire et une station balnéaire sur la côte atlantique du Maroc.'],
            ['Meknes', 'Meknes est une ville du nord du Maroc connue pour son passé impérial, avec des monuments historiques.'],
            ['Ouarzazate', 'Ouarzazate est une ville dans la région Drâa-Tafilalet du centre-sud du Maroc, connue comme une porte d\'entrée vers le désert du Sahara.'],
            ['Agadir', 'Agadir est une ville sur la côte atlantique sud du Maroc, connue pour ses plages et ses stations balnéaires.'],
            ['Tetouan', 'Tétouan est une ville du nord du Maroc, connue pour son architecture influencée par l\'Andalousie.'],
            ['Dakhla', 'Dakhla est une ville dans le territoire contesté du Sahara occidental, actuellement administré par le Maroc.']
        ];

        $stmt = $conn->prepare("INSERT INTO cities (name, description) VALUES (?, ?)");

        foreach ($cities as $city) {
            $stmt->bind_param("ss", $city[0], $city[1]);
            if ($stmt->execute()) {
                echo "Added city: " . $city[0] . "<br>";
            } else {
                echo "Error adding city " . $city[0] . ": " . $stmt->error . "<br>";
            }
        }

        $stmt->close();
        echo "Cities populated successfully!<br>";
    } else {
        echo "Cities table already has " . $row['count'] . " cities<br>";
    }

    echo "<h3>Current cities in database:</h3>";
    $result = $conn->query("SELECT id, name, description FROM cities ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        echo "<strong>" . $row['name'] . "</strong>: " . $row['description'] . "<br><br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
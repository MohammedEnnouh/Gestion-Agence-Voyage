<?php

header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/db.php';

function line($msg){ echo $msg . "\n"; }

try {

  $pdo->exec("CREATE TABLE IF NOT EXISTS villes (id INT UNSIGNED NOT NULL AUTO_INCREMENT, nom VARCHAR(100) NOT NULL UNIQUE, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS trajets (id INT UNSIGNED NOT NULL AUTO_INCREMENT, ville_depart_id INT UNSIGNED NOT NULL, ville_arrivee_id INT UNSIGNED NOT NULL, heure_depart TIME NOT NULL, heure_arrivee TIME NOT NULL, prix DECIMAL(10,2) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS clients (id INT UNSIGNED NOT NULL AUTO_INCREMENT, nom VARCHAR(150) NOT NULL, email VARCHAR(150) NULL, telephone VARCHAR(50) NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS paiements (id INT UNSIGNED NOT NULL AUTO_INCREMENT, client_id INT UNSIGNED NOT NULL, trajet_id INT UNSIGNED NOT NULL, montant DECIMAL(10,2) NOT NULL, statut VARCHAR(20) NOT NULL DEFAULT 'en_attente', date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, date_confirmation DATETIME NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  $hasCities  = (bool)($pdo->query("SHOW TABLES LIKE 'cities'")->fetchColumn());
  $hasRoutes  = (bool)($pdo->query("SHOW TABLES LIKE 'routes'")->fetchColumn());
  $hasBookings= (bool)($pdo->query("SHOW TABLES LIKE 'bookings'")->fetchColumn());

  if (!$hasCities && !$hasRoutes && !$hasBookings) {
    line("No source tables (cities/routes/bookings) found. Make sure import_sql.php succeeded.");
    exit(1);
  }

  if ($hasCities) {
    $countBefore = (int)$pdo->query('SELECT COUNT(*) FROM villes')->fetchColumn();
    $pdo->exec("INSERT IGNORE INTO villes(id, nom) SELECT id, name FROM cities");
    $countAfter = (int)$pdo->query('SELECT COUNT(*) FROM villes')->fetchColumn();
    line("Villes imported: " . ($countAfter - $countBefore));
  } else {
    line("Source table 'cities' not found; skipping villes import.");
  }

  if ($hasRoutes) {
    $countBefore = (int)$pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
    $pdo->exec("INSERT IGNORE INTO trajets(id, ville_depart_id, ville_arrivee_id, heure_depart, heure_arrivee, prix)
                SELECT r.id, r.from_city_id, r.to_city_id, '08:00:00', '12:00:00', r.price_per_person FROM routes r");
    $countAfter = (int)$pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
    line("Trajets imported: " . ($countAfter - $countBefore));
  } else {
    line("Source table 'routes' not found; skipping trajets import.");
  }

  line("Clients and Paiements not auto-imported (no compatible data in dump). You can create clients manually in the app.");

  line("Mapping import completed.");
} catch (Throwable $e) {
  http_response_code(500);
  line('Error: ' . $e->getMessage());
}
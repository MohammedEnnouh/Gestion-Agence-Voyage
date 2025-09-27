<?php

require __DIR__ . '/db.php';

$queries = [

  "CREATE TABLE IF NOT EXISTS villes (\n    id INT UNSIGNED NOT NULL AUTO_INCREMENT,\n    nom VARCHAR(100) NOT NULL UNIQUE,\n    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

  "CREATE TABLE IF NOT EXISTS trajets (\n    id INT UNSIGNED NOT NULL AUTO_INCREMENT,\n    ville_depart_id INT UNSIGNED NOT NULL,\n    ville_arrivee_id INT UNSIGNED NOT NULL,\n    heure_depart TIME NOT NULL,\n    heure_arrivee TIME NOT NULL,\n    prix DECIMAL(10,2) NOT NULL,\n    PRIMARY KEY (id),\n    CONSTRAINT fk_trajet_vd FOREIGN KEY (ville_depart_id) REFERENCES villes(id) ON DELETE RESTRICT ON UPDATE CASCADE,\n    CONSTRAINT fk_trajet_va FOREIGN KEY (ville_arrivee_id) REFERENCES villes(id) ON DELETE RESTRICT ON UPDATE CASCADE\n  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

  "CREATE TABLE IF NOT EXISTS clients (\n    id INT UNSIGNED NOT NULL AUTO_INCREMENT,\n    nom VARCHAR(150) NOT NULL,\n    email VARCHAR(150) NULL,\n    telephone VARCHAR(50) NULL,\n    PRIMARY KEY (id)\n  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

  "CREATE TABLE IF NOT EXISTS paiements (\n    id INT UNSIGNED NOT NULL AUTO_INCREMENT,\n    client_id INT UNSIGNED NOT NULL,\n    trajet_id INT UNSIGNED NOT NULL,\n    montant DECIMAL(10,2) NOT NULL,\n    statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',\n    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n    date_confirmation DATETIME NULL,\n    PRIMARY KEY (id),\n    CONSTRAINT fk_paiement_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE,\n    CONSTRAINT fk_paiement_trajet FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE RESTRICT ON UPDATE CASCADE\n  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($queries as $sql) {
  $pdo->exec($sql);
}

$villes = [
  'Casablanca','Rabat','Fès','Marrakech','Tanger','Agadir','Meknès','Oujda','Tétouan','Safi',
  'Khouribga','El Jadida','Béni Mellal','Nador','Kénitra','Taza','Mohammedia','Laâyoune','Ksar El Kebir','Larache'
];

$stmt = $pdo->prepare('INSERT IGNORE INTO villes(nom) VALUES(:nom)');
foreach ($villes as $v) {
  $stmt->execute([':nom' => $v]);
}

echo "Migration terminée avec succès.\n";
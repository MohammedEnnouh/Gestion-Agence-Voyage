CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `from_city_id` int(11) DEFAULT NULL,
  `to_city_id` int(11) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `passenger_count` int(11) NOT NULL,
  `payment_method` enum('stripe','cash') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `stripe_payment_id` varchar(100) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `booking_reference` varchar(50) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `from_city_id` (`from_city_id`),
  KEY `to_city_id` (`to_city_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_city_id` int(11) NOT NULL,
  `to_city_id` int(11) NOT NULL,
  `price_per_person` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from_to_unique` (`from_city_id`,`to_city_id`),
  KEY `idx_routes_from_city` (`from_city_id`),
  KEY `idx_routes_to_city` (`to_city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `destinations` (`name`, `description`, `image_url`, `base_price`) VALUES
('Marrakech', 'The red city with beautiful palaces and gardens', 'images/destinations/marrakech.jpg', 150.00),
('Casablanca', 'Economic capital with modern architecture', 'images/destinations/casablanca.jpg', 200.00),
('Fes', 'Ancient city with rich cultural heritage', 'images/destinations/fes.jpg', 180.00),
('Tangier', 'Gateway to Africa with stunning views', 'images/destinations/tangier.jpg', 220.00),
('Chefchaouen', 'The blue pearl of Morocco', 'images/destinations/chefchaouen.jpg', 190.00),
('Essaouira', 'Coastal city with beautiful beaches', 'images/destinations/essaouira.jpg', 170.00);

CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `cities` (`name`) VALUES
('Agadir'),
('Al Hoceima'),
('Azrou'),
('Beni Mellal'),
('Berrechid'),
('Boujdour'),
('Casablanca'),
('Chefchaouen'),
('Dakhla'),
('El Jadida'),
('Errachidia'),
('Essaouira'),
('Fes'),
('Fnideq'),
('Guelmim'),
('Khemisset'),
('Khenifra'),
('Khouribga'),
('Ksar El Kebir'),
('Larache'),
('Laayoune'),
('Marrakech'),
('Martil'),
('Meknes'),
('Midelt'),
('Mohammedia'),
('Nador'),
('Ouarzazate'),
('Oued Zem'),
('Oujda'),
('Oulad Teima'),
('Rabat'),
('Safi'),
('Salé'),
('Sefrou'),
('Settat'),
('Sidi Bennour'),
('Sidi Kacem'),
('Sidi Slimane'),
('Skhirat'),
('Tangier'),
('Tarfaya'),
('Taroudant'),
('Taza'),
('Temara'),
('Tetouan'),
('Tiflet'),
('Tinghir'),
('Tiznit');

ALTER TABLE routes DROP FOREIGN KEY fk_routes_from_city;
ALTER TABLE routes DROP FOREIGN KEY fk_routes_to_city;

ALTER TABLE routes DROP FOREIGN KEY routes_ibfk_1;
ALTER TABLE routes DROP FOREIGN KEY routes_ibfk_2;

INSERT IGNORE INTO `routes` (`from_city_id`, `to_city_id`, `price_per_person`)
SELECT c1.id, c2.id, 90.00 FROM cities c1, cities c2 WHERE c1.name='Casablanca' AND c2.name='Rabat' LIMIT 1;
INSERT IGNORE INTO `routes` (`from_city_id`, `to_city_id`, `price_per_person`)
SELECT c1.id, c2.id, 140.00 FROM cities c1, cities c2 WHERE c1.name='Casablanca' AND c2.name='Marrakech' LIMIT 1;
INSERT IGNORE INTO `routes` (`from_city_id`, `to_city_id`, `price_per_person`)
SELECT c1.id, c2.id, 200.00 FROM cities c1, cities c2 WHERE c1.name='Rabat' AND c2.name='Tangier' LIMIT 1;

ALTER TABLE `routes`
  ADD CONSTRAINT `fk_routes_from_city` FOREIGN KEY (`from_city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_routes_to_city` FOREIGN KEY (`to_city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_from_city` FOREIGN KEY (`from_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_to_city` FOREIGN KEY (`to_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'routes' AND index_name = 'idx_routes_from_city') = 0,
		'ALTER TABLE `routes` ADD INDEX `idx_routes_from_city` (`from_city_id`)',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'routes' AND index_name = 'idx_routes_to_city') = 0,
		'ALTER TABLE `routes` ADD INDEX `idx_routes_to_city` (`to_city_id`)',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'bookings' AND index_name = 'idx_bookings_from_city') = 0,
		'ALTER TABLE `bookings` ADD INDEX `idx_bookings_from_city` (`from_city_id`)',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'bookings' AND index_name = 'idx_bookings_to_city') = 0,
		'ALTER TABLE `bookings` ADD INDEX `idx_bookings_to_city` (`to_city_id`)',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'fk_routes_from_city'
	), 'ALTER TABLE `routes` DROP FOREIGN KEY `fk_routes_from_city`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'fk_routes_to_city'
	), 'ALTER TABLE `routes` DROP FOREIGN KEY `fk_routes_to_city`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'routes_ibfk_1'
	), 'ALTER TABLE `routes` DROP FOREIGN KEY `routes_ibfk_1`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'routes_ibfk_2'
	), 'ALTER TABLE `routes` DROP FOREIGN KEY `routes_ibfk_2`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'fk_bookings_from_city'
	), 'ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_from_city`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'fk_bookings_to_city'
	), 'ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_to_city`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'bookings_ibfk_2'
	), 'ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_ibfk_2`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(EXISTS(
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS 
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'bookings_ibfk_3'
	), 'ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_ibfk_3`', 'SELECT 1')
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.REFERENTIAL_CONSTRAINTS 
		 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'fk_routes_from_city_routes_1') = 0,
		'ALTER TABLE `routes` ADD CONSTRAINT `fk_routes_from_city_routes_1` FOREIGN KEY (`from_city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.REFERENTIAL_CONSTRAINTS 
		 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'routes' AND CONSTRAINT_NAME = 'fk_routes_to_city_routes_1') = 0,
		'ALTER TABLE `routes` ADD CONSTRAINT `fk_routes_to_city_routes_1` FOREIGN KEY (`to_city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.REFERENTIAL_CONSTRAINTS 
		 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'fk_bookings_from_city_bookings_1') = 0,
		'ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_from_city_bookings_1` FOREIGN KEY (`from_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL',
		'SELECT 1'
	)
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt := (
	SELECT IF(
		(SELECT COUNT(1) FROM information_schema.REFERENTIAL_CONSTRAINTS 
		 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND CONSTRAINT_NAME = 'fk_bookings_to_city_bookings_1') = 0,
		'ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_to_city_bookings_1` FOREIGN KEY (`to_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL',
		'SELECT 1'
	)
);
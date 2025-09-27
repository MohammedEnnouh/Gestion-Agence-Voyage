CREATE TABLE IF NOT EXISTS route_times (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    time TIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

INSERT IGNORE INTO cities (name) VALUES
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
('Settat'),
('Sidi Ifni'),
('Tanger'),
('Tan-Tan'),
('Taourirt'),
('Taroudant'),
('Taza'),
('Témara'),
('Tetouan'),
('Tiznit');

INSERT IGNORE INTO routes (from_city_id, to_city_id, price_per_person) SELECT c1.id, c2.id, 120.00
FROM cities c1, cities c2 WHERE c1.id <> c2.id AND c1.name IN ('Casablanca','Marrakech','Fes','Tanger','Agadir','Rabat') AND c2.name IN ('Casablanca','Marrakech','Fes','Tanger','Agadir','Rabat');

INSERT IGNORE INTO `routes` (`from_city_id`, `to_city_id`, `price_per_person`)
SELECT c1.id, c2.id, 100.00
FROM cities c1, cities c2
WHERE c1.id <> c2.id;
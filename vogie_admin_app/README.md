# Gestion de Voyages (PHP + MySQL)

Application simple (UI en français) pour gérer:

- Villes (Maroc)
- Trajets (départ, arrivée, horaires, prix)
- Clients
- Paiements (création, confirmation, suppression)
- Tableau de bord avec KPIs et derniers paiements

## Prérequis

- XAMPP (Apache + PHP + MySQL) installé
- Extensions PHP activées: `pdo_mysql`

## Installation

1. Copiez ce dossier dans `c:/xampp/htdocs/Vogie2`.
2. Ouvrez le fichier de configuration `public/config.php` et ajustez les paramètres MySQL si nécessaire:

   ```php
   'db' => [
     'driver' => 'mysql',
     'host' => '127.0.0.1',
     'port' => 3306,
     'database' => 'vogie_web',
     'username' => 'root',
     'password' => '',
     'charset' => 'utf8mb4',
   ]
   ```

3. Allez sur `http://localhost/Vogie2/public/migrate.php` pour créer la base et les tables, puis peupler les villes du Maroc.
4. Accédez à l'application: `http://localhost/Vogie2/public/`.

### Importer la base exemple (vogie_web.sql)

Si vous disposez du fichier SQL `vogie_web.sql` (fourni à la racine du projet), vous pouvez l'importer automatiquement:

1. Vérifiez/ajustez le nom de base dans `public/config.php` (par défaut: `vogie_web`).
2. Ouvrez l'URL: `http://localhost/Vogie2/public/import_sql.php`.
3. Le script exécute le dump dans la base configurée. En cas d'erreur, le message et la requête fautive s'affichent.

## Notes

- La base MySQL est créée automatiquement si elle n'existe pas (nom par défaut: `vogie_web`).
- Styles/JS via Bootstrap 5 CDN.
- Cette application est un point de départ. Sécurisation (authentification, CSRF), validations avancées et rôles ne sont pas inclus.

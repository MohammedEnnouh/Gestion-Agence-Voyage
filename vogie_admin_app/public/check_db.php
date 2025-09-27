<?php

header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/db.php';

try {
  $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
  $dbName = $config['db']['database'] ?? '';

  echo '<h2>Database connection OK</h2>';
  echo '<ul>';
  echo '<li><strong>Driver</strong>: MySQL (PDO)</li>';
  echo '<li><strong>Server version</strong>: ' . htmlspecialchars($serverVersion) . '</li>';
  echo '<li><strong>Configured DB</strong>: ' . htmlspecialchars($dbName) . '</li>';
  echo '</ul>';

  echo '<h3>Tables in database</h3>';
  echo '<div style="padding:.5rem; background:#f7f7f9; border:1px solid #e1e1e8; border-radius:8px">';
  $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  if (!$tables) {
    echo '<em>No tables found.</em>';
  } else {
    echo '<ul>';
    foreach ($tables as $t) echo '<li>' . htmlspecialchars($t) . '</li>';
    echo '</ul>';
  }
  echo '</div>';

  echo '<p>Need to initialize the schema? Run <a href="/Vogie2/public/migrate.php">migrate.php</a> or <a href="/Vogie2/public/import_sql.php">import_sql.php</a>.</p>';
  echo '<p>Go to app: <a href="/Vogie2/public/index.php">Dashboard</a></p>';
} catch (Throwable $e) {
  http_response_code(500);
  echo '<h2 style="color:#b00">Database connection FAILED</h2>';
  echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
  echo '<p>Check credentials in <code>/Vogie2/public/config.php</code> and ensure MySQL (XAMPP) is running.</p>';
}
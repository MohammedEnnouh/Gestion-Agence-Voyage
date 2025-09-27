<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

require __DIR__ . '/db.php';

$sqlFile = realpath(__DIR__ . '/../vogie_web.sql');
if (!$sqlFile || !file_exists($sqlFile)) {
  http_response_code(404);
  echo "Fichier SQL introuvable: ../vogie_web.sql";
  exit;
}

$pdo->exec("SET NAMES utf8mb4");
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

$handle = fopen($sqlFile, 'r');
if (!$handle) {
  die('Impossible d\'ouvrir le fichier SQL.');
}

$buffer = '';
$lineNumber = 0;
$executed = 0;

while (($line = fgets($handle)) !== false) {
  $lineNumber++;

  $trim = ltrim($line);
  if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '')) {
    continue;
  }

  if (preg_match('/^--\s*Base de données\s*:\s*`([^`]+)`/i', $line)) {
    continue;
  }

  $buffer .= $line;

  if (preg_match('/;\s*$/', $line)) {
    $sql = trim($buffer);
    $buffer = '';
    if ($sql === '') continue;

    if (stripos($sql, 'CREATE DATABASE') !== false) {
      continue;
    }

    $dbName = $config['db']['database'];
    $sql = str_replace('`vogie_web`', "`{$dbName}`", $sql);

    try {
      $pdo->exec($sql);
      $executed++;
    } catch (PDOException $e) {
      echo "<div style=\"color:#b00\">Erreur à la ligne {$lineNumber}: " . htmlspecialchars($e->getMessage()) . "</div>\n";
      echo "<pre>" . htmlspecialchars($sql) . "</pre>\n";

    }
  }
}

fclose($handle);
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "Importation terminée. Instructions exécutées: {$executed}.";
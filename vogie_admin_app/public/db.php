<?php
$config = require __DIR__ . '/config.php';

$db = $config['db'];
$host = $db['host'];
$port = (int)$db['port'];
$dbname = $db['database'];
$user = $db['username'];
$pass = $db['password'];
$charset = $db['charset'];

try {

  $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbname, $charset);
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {

  try {
    $dsnNoDb = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);
    $pdoTmp = new PDO($dsnNoDb, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdoTmp->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
    $pdoTmp = null;

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbname, $charset);
    $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
  } catch (PDOException $e2) {
    die('Erreur de connexion à la base MySQL: ' . htmlspecialchars($e2->getMessage()));
  }
}
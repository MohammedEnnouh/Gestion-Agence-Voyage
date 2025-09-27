<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    $rows = $pdo->query('SELECT id, nom FROM villes ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
    json_ok($rows);
  }
  if ($method === 'POST') {
    require_admin();
    $body = read_json_body();
    $nom = trim($body['nom'] ?? '');
    if ($nom === '') json_err('nom required', 422);
    $st = $pdo->prepare('INSERT IGNORE INTO villes(nom) VALUES(?)');
    $st->execute([$nom]);
    json_ok(['id' => (int)$pdo->lastInsertId(), 'nom' => $nom], 201);
  }
  if ($method === 'DELETE') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $pdo->prepare('DELETE FROM villes WHERE id=?')->execute([$id]);
    json_ok(['deleted' => $id]);
  }
  json_err('Method not allowed', 405);
} catch (Throwable $e) {
  json_err('Server error', 500, ['detail' => $e->getMessage()]);
}
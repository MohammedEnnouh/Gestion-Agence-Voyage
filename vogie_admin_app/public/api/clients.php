<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {

    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
      $stmt = $pdo->prepare("SELECT id, full_name, email, phone, is_active FROM users WHERE role='user' AND (full_name LIKE ? OR email LIKE ?) ORDER BY full_name");
      $like = "%$q%";
      $stmt->execute([$like, $like]);
      json_ok($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
      $rows = $pdo->query("SELECT id, full_name, email, phone, is_active FROM users WHERE role='user' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
      json_ok($rows);
    }
  }

  if ($method === 'POST') {
    require_admin();
    $b = read_json_body();
    $name = trim($b['full_name'] ?? '');
    $email = trim($b['email'] ?? '');
    if ($name === '') json_err('full_name required', 422);
    $st = $pdo->prepare("INSERT INTO users(full_name, email, phone, role, is_active) VALUES(?, ?, ?, 'user', 1)");
    $phone = trim($b['phone'] ?? '');
    $st->execute([$name, $email !== '' ? $email : null, $phone !== '' ? $phone : null]);
    json_ok(['id' => (int)$pdo->lastInsertId()], 201);
  }

  if ($method === 'PUT') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $b = read_json_body();
    $name = trim($b['full_name'] ?? '');
    $email = trim($b['email'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $is_active = isset($b['is_active']) ? (int)!!$b['is_active'] : null;
    $fields = [];
    $params = [];
    if ($name !== '') { $fields[] = 'full_name=?'; $params[] = $name; }
    if ($email !== '') { $fields[] = 'email=?'; $params[] = $email; }
    if (isset($b['phone'])) { $fields[] = 'phone=?'; $params[] = $phone !== '' ? $phone : null; }
    if ($is_active !== null) { $fields[] = 'is_active=?'; $params[] = $is_active; }
    if (!$fields) json_err('no fields to update', 422);
    $params[] = $id;
    $sql = 'UPDATE users SET ' . implode(',', $fields) . ' WHERE id=?';
    $pdo->prepare($sql)->execute($params);
    json_ok(['updated' => $id]);
  }

  if ($method === 'DELETE') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $pdo->prepare('DELETE FROM users WHERE id=? AND role=\'user\'')->execute([$id]);
    json_ok(['deleted' => $id]);
  }

  json_err('Method not allowed', 405);
} catch (Throwable $e) {
  json_err('Server error', 500, ['detail' => $e->getMessage()]);
}
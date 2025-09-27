<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_err('Method not allowed', 405);
}

$body = read_json_body();
$email = trim($body['email'] ?? '');
$password = (string)($body['password'] ?? '');
if ($email === '') { json_err('Email required', 422); }

try {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) { json_err('Invalid credentials', 401); }
  $ok = ($user['password'] === '' && $password === '') || password_verify($password, (string)$user['password']);
  if (!$ok) { json_err('Invalid credentials', 401); }
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role'],
  ];
  json_ok([
    'id' => (int)$user['id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role'],
  ]);
} catch (Throwable $e) {
  json_err('Login error', 500, ['detail' => $e->getMessage()]);
}
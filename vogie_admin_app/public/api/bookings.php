<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {

    $status = trim($_GET['status'] ?? '');
    if ($status !== '') {
      $st = $pdo->prepare("SELECT b.id, b.booking_reference, b.user_id, u.full_name AS client,
                                   b.from_city_id, cd.name AS depart, b.to_city_id, ca.name AS arrivee,
                                   b.departure_date, b.departure_time, b.passenger_count,
                                   b.payment_method, b.payment_status, b.total_amount, b.stripe_payment_id,
                                   b.booking_date as created_at
                            FROM bookings b
                            JOIN users u ON u.id=b.user_id
                            JOIN cities cd ON cd.id=b.from_city_id
                            JOIN cities ca ON ca.id=b.to_city_id
                            WHERE b.payment_status=?
                            ORDER BY b.id DESC");
      $st->execute([$status]);
      json_ok($st->fetchAll(PDO::FETCH_ASSOC));
    } else {
      $rows = $pdo->query("SELECT b.id, b.booking_reference, b.user_id, u.full_name AS client,
                                   b.from_city_id, cd.name AS depart, b.to_city_id, ca.name AS arrivee,
                                   b.departure_date, b.departure_time, b.passenger_count,
                                   b.payment_method, b.payment_status, b.total_amount, b.stripe_payment_id,
                                   b.booking_date as created_at
                            FROM bookings b
                            JOIN users u ON u.id=b.user_id
                            JOIN cities cd ON cd.id=b.from_city_id
                            JOIN cities ca ON ca.id=b.to_city_id
                            ORDER BY b.id DESC")->fetchAll(PDO::FETCH_ASSOC);
      json_ok($rows);
    }
  }

  if ($method === 'POST') {
    require_admin();
    $b = read_json_body();
    $user_id = (int)($b['user_id'] ?? 0);
    $client_name = trim($b['client_name'] ?? '');
    $trajet_id = (int)($b['trajet_id'] ?? 0);
    $dep_date = trim($b['departure_date'] ?? '');
    $dep_time = trim($b['departure_time'] ?? '');
    $passengers = max(1, (int)($b['passenger_count'] ?? 1));
    $mode = in_array(($b['payment_method'] ?? 'cash'), ['cash','stripe'], true) ? $b['payment_method'] : 'cash';
    $stripe_payment_id = trim($b['stripe_payment_id'] ?? '');

    if (!$user_id && $client_name !== '') {

      $find = $pdo->prepare('SELECT id FROM users WHERE full_name=? LIMIT 1');
      $find->execute([$client_name]);
      $existing = $find->fetch(PDO::FETCH_ASSOC);
      if ($existing) $user_id = (int)$existing['id'];
      else {
        $insU = $pdo->prepare("INSERT INTO users(full_name, role, is_active) VALUES(?, 'user', 1)");
        $insU->execute([$client_name]);
        $user_id = (int)$pdo->lastInsertId();
      }
    }

    if (!$trajet_id || !$user_id || $dep_date==='' || $dep_time==='') {
      json_err('Missing fields', 422);
    }

    $st = $pdo->prepare('SELECT prix, ville_depart_id, ville_arrivee_id FROM trajets WHERE id=?');
    $st->execute([$trajet_id]);
    $t = $st->fetch(PDO::FETCH_ASSOC);
    if (!$t) json_err('Invalid trajet_id', 422);

    $amount = (float)$t['prix'] * $passengers;
    try { $ref = 'VOGIE-' . strtoupper(bin2hex(random_bytes(6))); } catch (Throwable $e) { $ref = 'VOGIE-' . strtoupper(dechex(time())); }

    $ins = $pdo->prepare("INSERT INTO bookings(user_id, from_city_id, to_city_id, departure_date, departure_time, passenger_count, payment_method, payment_status, stripe_payment_id, total_amount, booking_reference) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
    $ins->execute([$user_id, (int)$t['ville_depart_id'], (int)$t['ville_arrivee_id'], $dep_date, $dep_time, $passengers, $mode, 'pending', $mode==='stripe' && $stripe_payment_id!=='' ? $stripe_payment_id : null, $amount, $ref]);

    json_ok(['id' => (int)$pdo->lastInsertId(), 'booking_reference' => $ref], 201);
  }

  if ($method === 'PUT') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $b = read_json_body();

    $status = trim($b['payment_status'] ?? '');
    $stripe_payment_id = array_key_exists('stripe_payment_id', $b) ? trim((string)$b['stripe_payment_id']) : null;

    $fields = [];
    $params = [];
    if ($status !== '') { $fields[] = 'payment_status=?'; $params[] = $status; }
    if ($stripe_payment_id !== null) { $fields[] = 'stripe_payment_id=?'; $params[] = ($stripe_payment_id === '' ? null : $stripe_payment_id); }
    if (!$fields) json_err('no fields to update', 422);
    $params[] = $id;

    $pdo->prepare('UPDATE bookings SET ' . implode(',', $fields) . ' WHERE id=?')->execute($params);
    json_ok(['updated' => $id]);
  }

  if ($method === 'DELETE') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $pdo->prepare('DELETE FROM bookings WHERE id=?')->execute([$id]);
    json_ok(['deleted' => $id]);
  }

  json_err('Method not allowed', 405);
} catch (Throwable $e) {
  json_err('Server error', 500, ['detail' => $e->getMessage()]);
}
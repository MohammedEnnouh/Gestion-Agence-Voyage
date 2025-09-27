<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
      $stmt = $pdo->prepare("SELECT t.id, vd.nom AS depart, va.nom AS arrivee, t.heure_depart, t.heure_arrivee, t.prix
                             FROM trajets t
                             JOIN villes vd ON vd.id=t.ville_depart_id
                             JOIN villes va ON va.id=t.ville_arrivee_id
                             WHERE vd.nom LIKE ? OR va.nom LIKE ?
                             ORDER BY t.id DESC");
      $like = "%$q%";
      $stmt->execute([$like,$like]);
      json_ok($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
      $rows = $pdo->query("SELECT t.id, vd.nom AS depart, va.nom AS arrivee, t.heure_depart, t.heure_arrivee, t.prix
                           FROM trajets t
                           JOIN villes vd ON vd.id=t.ville_depart_id
                           JOIN villes va ON va.id=t.ville_arrivee_id
                           ORDER BY t.id DESC")->fetchAll(PDO::FETCH_ASSOC);
      json_ok($rows);
    }
  }

  if ($method === 'POST') {
    require_admin();
    $b = read_json_body();
    $vd = (int)($b['ville_depart_id'] ?? 0);
    $va = (int)($b['ville_arrivee_id'] ?? 0);
    $hd = trim($b['heure_depart'] ?? '');
    $ha = trim($b['heure_arrivee'] ?? '');
    $prix = (float)($b['prix'] ?? 0);
    if (!$vd || !$va || $hd==='' || $ha==='' || $prix<=0) json_err('Invalid payload', 422);
    $st = $pdo->prepare('INSERT INTO trajets(ville_depart_id,ville_arrivee_id,heure_depart,heure_arrivee,prix) VALUES(?,?,?,?,?)');
    $st->execute([$vd,$va,$hd,$ha,$prix]);
    json_ok(['id' => (int)$pdo->lastInsertId()], 201);
  }

  if ($method === 'PUT') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $b = read_json_body();
    $hd = trim($b['heure_depart'] ?? '');
    $ha = trim($b['heure_arrivee'] ?? '');
    $prix = (float)($b['prix'] ?? 0);
    if ($hd==='' || $ha==='' || $prix<=0) json_err('Invalid payload', 422);
    $st = $pdo->prepare('UPDATE trajets SET heure_depart=?, heure_arrivee=?, prix=? WHERE id=?');
    $st->execute([$hd,$ha,$prix,$id]);
    json_ok(['updated' => $id]);
  }

  if ($method === 'DELETE') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('id required', 422);
    $pdo->prepare('DELETE FROM trajets WHERE id=?')->execute([$id]);
    json_ok(['deleted' => $id]);
  }

  json_err('Method not allowed', 405);
} catch (Throwable $e) {
  json_err('Server error', 500, ['detail' => $e->getMessage()]);
}
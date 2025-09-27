<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
  header('Location: /Vogie2/public/login.php');
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { die('Booking ID manquant'); }

try {
  $stmt = $pdo->prepare("SELECT b.*, u.full_name, u.email, u.phone, vd.nom AS depart, va.nom AS arrivee
                         FROM bookings b
                         JOIN users u ON u.id=b.user_id
                         JOIN villes vd ON vd.id=b.from_city_id
                         JOIN villes va ON va.id=b.to_city_id
                         WHERE b.id=?");
  $stmt->execute([$id]);
  $b = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$b) { die('Réservation introuvable'); }
} catch (Throwable $e) {
  die('Erreur: ' . htmlspecialchars($e->getMessage()));
}

$ref = $b['booking_reference'] ?: ('VOGIE-' . strtoupper(dechex($b['id'])));
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reçu
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none !important; }
      .card { box-shadow: none !important; border: none !important; }
    }
    body { background:
  </style>
</head>
<body>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="/Vogie2/public/index.php?page=paiements" class="btn btn-outline-secondary">← Retour</a>
    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer</button>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <h4 class="mb-1">Reçu de réservation</h4>
          <div class="text-muted">Référence: <code><?= htmlspecialchars($ref) ?></code></div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <div id="qrcode" class="d-inline-block border rounded p-2"></div>
        </div>
      </div>
      <hr>
      <div class="row g-3">
        <div class="col-md-6">
          <h6 class="text-muted">Client</h6>
          <div><strong><?= htmlspecialchars($b['full_name']) ?></strong></div>
          <div><?= htmlspecialchars($b['email']) ?></div>
          <div><?= htmlspecialchars($b['phone']) ?></div>
        </div>
        <div class="col-md-6">
          <h6 class="text-muted">Détails</h6>
          <div>Trajet: <strong><?= htmlspecialchars($b['depart']) ?> → <?= htmlspecialchars($b['arrivee']) ?></strong></div>
          <div>Date/Heure: <strong><?= htmlspecialchars($b['departure_date']) ?> <?= htmlspecialchars($b['departure_time']) ?></strong></div>
          <div>Passagers: <strong><?= (int)$b['passenger_count'] ?></strong></div>
          <div>Mode: <strong><?= htmlspecialchars($b['payment_method']) ?></strong></div>
          <div>Statut: <strong><?= htmlspecialchars($b['payment_status']) ?></strong></div>
          <?php if (!empty($b['stripe_payment_id'])): ?>
          <div>Stripe ID: <code><?= htmlspecialchars($b['stripe_payment_id']) ?></code></div>
          <?php endif; ?>
        </div>
      </div>
      <hr>
      <div class="d-flex justify-content-between">
        <div class="text-muted">Créé le <?= htmlspecialchars($b['booking_date']) ?></div>
        <div class="fs-5">Total: <strong>MAD <?= number_format((float)$b['total_amount'], 2, ',', ' ') ?></strong></div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>

  const payload = {
    id: <?= (int)$b['id'] ?>,
    ref: <?= json_encode($ref) ?>,
    client: <?= json_encode($b['full_name']) ?>,
    route: <?= json_encode($b['depart'] . ' -> ' . $b['arrivee']) ?>,
    date: <?= json_encode($b['departure_date'] . ' ' . $b['departure_time']) ?>,
    amount: <?= json_encode(number_format((float)$b['total_amount'], 2, '.', '')) ?>,
    status: <?= json_encode($b['payment_status']) ?>
  };
  new QRCode(document.getElementById('qrcode'), {
    text: JSON.stringify(payload),
    width: 128,
    height: 128,
    colorDark : "#000000",
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.M
  });
</script>
</body>
</html>
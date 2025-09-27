<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION['booking_data'])) {
    header("Location: booking.php");
    exit();
}

$booking_data = $_SESSION['booking_data'];
$error = '';
$stripe_public_key = STRIPE_PUBLIC_KEY;

$requiredFields = ['user_id', 'from_city_id', 'to_city_id', 'departure_date', 'departure_time', 'passenger_count', 'payment_method', 'total_amount', 'full_name', 'email', 'phone'];
foreach ($requiredFields as $field) {
    if (!isset($booking_data[$field])) {
        header("Location: booking.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stripeToken'])) {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    try {

        if (!isset($_SESSION['used_tokens'])) {
            $_SESSION['used_tokens'] = [];
        }
        $incomingToken = trim($_POST['stripeToken']);
        if (in_array($incomingToken, $_SESSION['used_tokens'], true)) {
            throw new Exception('Ce paiement a déjà été soumis. Veuillez recharger la page pour réessayer.');
        }

        $idempotencyKey = bin2hex(random_bytes(16));

        $charge = \Stripe\Charge::create([
            'amount' => $booking_data['total_amount'] * 100,
            'currency' => 'mad',
            'description' => 'Paiement pour votre voyage',
            'source' => $_POST['stripeToken'],
            'metadata' => [
                'user_id' => $booking_data['user_id'],
                'from_city_id' => $booking_data['from_city_id'],
                'to_city_id' => $booking_data['to_city_id'],
                'passenger_count' => $booking_data['passenger_count'],
'departure_time' => $booking_data['departure_time'] ?? null
            ]
        ], [ 'idempotency_key' => $idempotencyKey ]);

        $conn = getDBConnection();

        $conn->begin_transaction();

        try {

            $userId = isset($booking_data['user_id']) ? (int)$booking_data['user_id'] : 0;
            if ($userId <= 0) {

                $email = trim($booking_data['email'] ?? '');
                $fullName = trim($booking_data['full_name'] ?? 'Client Vogie');
                $phone = trim($booking_data['phone'] ?? '');

                if ($email === '') {

                    $email = 'guest+' . time() . '@vogie.local';
                }

                $us = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $us->bind_param("s", $email);
                $us->execute();
                $usRes = $us->get_result();
                if ($row = $usRes->fetch_assoc()) {
                    $userId = (int)$row['id'];
                } else {

                    $uc = $conn->prepare("INSERT INTO users (full_name, email, phone) VALUES (?, ?, ?)");
                    $uc->bind_param("sss", $fullName, $email, $phone);
                    $uc->execute();
                    $userId = $conn->insert_id;
                    $uc->close();
                }
                $us->close();
            }

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, from_city_id, to_city_id, departure_date, departure_time, passenger_count, payment_method, payment_status, stripe_payment_id, total_amount) VALUES (?, ?, ?, ?, ?, ?, 'stripe', 'completed', ?, ?)");
            $stmt->bind_param("iiissisd",
                $userId,
                $booking_data['from_city_id'],
                $booking_data['to_city_id'],
                $booking_data['departure_date'],
                $booking_data['departure_time'],
                $booking_data['passenger_count'],
                $charge->id,
                $booking_data['total_amount']
            );

            $stmt->execute();
            $stmt->close();

            $booking_id = $conn->insert_id;

            $conn->commit();

            unset($_SESSION['booking_data']);

            header("Location: confirmation.php?booking_id=" . $booking_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Erreur lors de l'enregistrement de la réservation: " . $e->getMessage();
        }

        $_SESSION['used_tokens'][] = $incomingToken;

    } catch (\Stripe\Exception\CardException $e) {
        $error = "Erreur de paiement: " . $e->getError()->message;
    } catch (Exception $e) {
        $error = "Une erreur est survenue: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement sécurisé - Vogie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>

        body { background:
        .crumbs { background:
        .crumbs .breadcrumb { margin:0; padding:.75rem 0; }
        .card-clean { background:
        .summary-card .label { color:
        .summary-price { font-size:1.5rem; font-weight:800; color:

        .stripe-box { border:1px solid
        .stripe-box:focus-within { border-color:

        .StripeElement { width:100%; }
        .StripeElement--focus { outline: none; }
        .StripeElement--invalid { }
        .StripeElement--webkit-autofill { background-color: transparent !important; }
        .secure-note { color:
        .pay-button { border-radius:10px; font-weight:700; padding:.75rem 1.25rem; background:linear-gradient(90deg,
        .pay-button:hover { filter:brightness(.98); }
        .sticky-lg-top { top: 24px; }
        .section-title { font-weight:600; color:
        .small-muted { font-size:.85rem; color:
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Vogie</a>
        </div>
    </nav>

    <!-- Simple breadcrumbs -->
    <div class="crumbs">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small text-muted">
                    <li class="breadcrumb-item"><a href="index.php">Recherche</a></li>
                    <li class="breadcrumb-item"><a href="booking.php">Réservation</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container my-4">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card-clean p-4 h-100">
                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <h5 class="mb-3 section-title"><i class="fa-regular fa-credit-card text-primary me-2"></i>Informations de paiement</h5>
                    <form action="" method="POST" id="payment-form">
                        <div class="mb-3">
                            <label class="form-label" for="card-element">Carte bancaire</label>
                            <div id="card-element" class="stripe-box" aria-label="Champ carte"></div>
                            <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                            <div id="card-status" class="small-muted mt-1"></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="secure-note"><i class="fa-solid fa-lock me-2"></i>Nous ne stockons pas vos informations de carte.</div>
                            <button type="submit" class="btn btn-primary pay-button" id="submit-button">
                                <i class="fa-solid fa-shield-check me-2"></i>Payer <?php echo number_format($booking_data['total_amount'], 2); ?> MAD
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <img src="assets/images/powered-by-stripe.png" alt="Powered by Stripe" class="img-fluid opacity-75" style="max-width: 40px;">
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card-clean p-4 summary-card mb-4 sticky-lg-top">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0 section-title"><i class="fa-solid fa-receipt text-primary me-2"></i>Résumé</h5>
                        <a href="booking.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen-to-square me-1"></i>Modifier</a>
                    </div>
                    <div class="mb-2">
                        <div class="label">Trajet</div>
                        <div>
                            <?php
                            $conn = getDBConnection();
                            $fromName = $toName = '';
                            $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
                            $stmt->bind_param("i", $booking_data['from_city_id']);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($row = $res->fetch_assoc()) { $fromName = $row['name']; }
                            $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
                            $stmt->bind_param("i", $booking_data['to_city_id']);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($row = $res->fetch_assoc()) { $toName = $row['name']; }
                            echo '<strong>' . htmlspecialchars($fromName . ' → ' . $toName) . '</strong>';
                            ?>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="label">Date</div>
                            <div><strong><?php echo htmlspecialchars($booking_data['departure_date']); ?></strong></div>
                        </div>
                        <div class="col-6">
                            <div class="label">Heure</div>
                            <div><strong><?php echo htmlspecialchars($booking_data['departure_time']); ?></strong></div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="label">Passagers</div>
                            <div><strong><?php echo (int)$booking_data['passenger_count']; ?></strong></div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="label">Total</div>
                            <div class="summary-price"><?php echo number_format($booking_data['total_amount'], 2); ?> <span class="text-muted">MAD</span></div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="label mb-1">Client</div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <div>
                                <div><strong><?php echo htmlspecialchars($booking_data['full_name'] ?? ''); ?></strong></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($booking_data['email'] ?? ''); ?> · <?php echo htmlspecialchars($booking_data['phone'] ?? ''); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-clean p-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-lock text-success"></i>
                        <div class="small text-muted">Transactions sécurisées via Stripe. Aucune donnée sensible n'est conservée par Vogie.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Vogie. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function runWhenReady(fn){ if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn);} else {fn();} })(function(){

        var pk = <?php echo json_encode($stripe_public_key); ?>;
        if (!pk) {
            console.warn('Stripe public key is missing. Set STRIPE_PUBLIC_KEY in config.');
            var err = document.getElementById('card-errors');
            if (err) err.textContent = 'Clé publique Stripe absente. Veuillez configurer STRIPE_PUBLIC_KEY.';
            return;
        }

        var stripe;
        try { stripe = Stripe(pk); } catch(e) {
            console.error('Stripe init failed:', e);
            var err1 = document.getElementById('card-errors');
            if (err1) err1.textContent = "Impossible d'initialiser Stripe. Vérifiez la clé publique.";
            return;
        }

        var elements = stripe.elements();

        var style = {
            base: {
                color: '#000',
                fontFamily: 'Arial, sans-serif',
                fontSize: '16px',
                '::placeholder': { color: '#888' }
            },
            invalid: { color: '#b91c1c' }
        };

        var card;
        try {
            card = elements.create('card');
        } catch(e) {
            console.error('Stripe Elements creation failed:', e);
            var err2 = document.getElementById('card-errors');
            if (err2) err2.textContent = 'Erreur lors du chargement du champ carte.';
            return;
        }

        try {
            card.mount('#card-element');
        } catch(e) {
            console.error('Stripe mount failed:', e);
            var err3 = document.getElementById('card-errors');
            if (err3) err3.textContent = 'Impossible d\'afficher le champ carte.';
            return;
        }

        function onChange(event) {
            var displayError = document.getElementById('card-errors');
            var status = document.getElementById('card-status');
            if (event.error) {
                displayError.textContent = event.error.message;
                if (status) status.textContent = '';
            } else {
                displayError.textContent = '';
                if (status) status.textContent = event.complete ? 'Champ carte prêt — Saisie complète.' : 'Champ carte prêt — En attente de saisie.';
            }
        }
        card.on('change', onChange);

        card.on('ready', function(){
            var status = document.getElementById('card-status');
            if (status) status.textContent = 'Champ carte prêt — vous pouvez saisir votre numéro.';
        });

        var form = document.getElementById('payment-form');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                var submitBtn = document.getElementById('submit-button');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
                }
                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        var errorElement = document.getElementById('card-errors');
                        if (errorElement) errorElement.textContent = result.error.message;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Payer <?php echo number_format($booking_data['total_amount'], 2); ?> MAD';
                        }
                    } else {
                        stripeTokenHandler(result.token);
                    }
                });
            });
        }

        function stripeTokenHandler(token) {
            var form = document.getElementById('payment-form');
            if (!form) return;
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            form.submit();
        }
        });
    </script>
  </body>
  </html>
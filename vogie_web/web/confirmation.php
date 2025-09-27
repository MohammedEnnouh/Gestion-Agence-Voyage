<?php
require_once 'config/config.php';

$is_cash_payment = isset($_GET['method']) && $_GET['method'] === 'cash';

if (!$is_cash_payment && !isset($_GET['booking_id'])) {
    header("Location: index.php");
    exit();
}

$booking = [];
$qr_code_path = '';
$booking_reference = '';

function generateQRCodeWithAPI($data, $filename) {
    $api_key = '6SgvU3SycViatw70eWucMybcBcqu2JZIS4X5aHAOU0d5wzNywESbVq9M1e90kWCw';
    $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($data) . '&api_key=' . $api_key;

    $qr_dir = 'uploads/qr_codes';
    if (!file_exists($qr_dir)) {
        mkdir($qr_dir, 0777, true);
    }

    $filepath = $qr_dir . '/' . $filename;

    $qr_image = file_get_contents($qr_url);
    if ($qr_image !== false) {
        file_put_contents($filepath, $qr_image);
        return $filename;
    }

    return false;
}

if ($is_cash_payment) {

    if (!isset($_SESSION['booking_data'])) {
        header("Location: booking.php");
        exit();
    }

    $booking_data = $_SESSION['booking_data'];

    $booking_reference = 'VOGIE-' . strtoupper(uniqid());

    try {
        $conn = getDBConnection();
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->bind_param("i", $booking_data['to_city_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $to_city_name = ($row = $res->fetch_assoc()) ? $row['name'] : '';

        $userId = isset($booking_data['user_id']) ? (int)$booking_data['user_id'] : 0;
        if ($userId <= 0) {
            $email = trim($booking_data['email'] ?? '');
            $fullName = trim($booking_data['full_name'] ?? 'Client Vogie');
            $phone = trim($booking_data['phone'] ?? '');
            if ($email === '') { $email = 'guest+' . time() . '@vogie.local'; }

            $us = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $us->bind_param("s", $email);
            $us->execute();
            $resUser = $us->get_result();
            if ($row = $resUser->fetch_assoc()) {
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

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, destination, from_city_id, to_city_id, departure_date, departure_time, passenger_count, payment_method, payment_status, booking_reference, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, 'cash', 'pending', ?, ?)");

        $stmt->bind_param("isiissisd",
            $userId,
            $to_city_name,
            $booking_data['from_city_id'],
            $booking_data['to_city_id'],
            $booking_data['departure_date'],
            $booking_data['departure_time'],
            $booking_data['passenger_count'],
            $booking_reference,
            $booking_data['total_amount']
        );
        $stmt->execute();
        $booking_id = $conn->insert_id;

        $qr_content = json_encode([
            'booking_id' => $booking_id,
            'reference' => $booking_reference,
            'user_id' => $booking_data['user_id'],
            'user_name' => $booking_data['full_name'],
            'from_city_id' => $booking_data['from_city_id'],
            'to_city_id' => $booking_data['to_city_id'],
            'passenger_count' => $booking_data['passenger_count'],
            'total_amount' => $booking_data['total_amount'],
            'timestamp' => time()
        ]);

        $qr_filename = 'vogie_qr_' . $booking_id . '.png';
        $qr_code_path = generateQRCodeWithAPI($qr_content, $qr_filename);

        if ($qr_code_path) {

            $stmt = $conn->prepare("UPDATE bookings SET qr_code_path = ? WHERE id = ?");
            $stmt->bind_param("si", $qr_code_path, $booking_id);
            $stmt->execute();
        }

        $conn->commit();

        $destName = $to_city_name;
        if (!empty($booking_data['destination_id'])) {
            $stmt = $conn->prepare("SELECT name FROM destinations WHERE id = ?");
            $stmt->bind_param("i", $booking_data['destination_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $destName = $row['name'];
            }
        }

        $booking = [
            'id' => $booking_id,
            'reference' => $booking_reference,
            'destination' => $destName,
            'from_city_id' => $booking_data['from_city_id'],
            'to_city_id' => $booking_data['to_city_id'],
            'departure_date' => $booking_data['departure_date'],
            'passenger_count' => $booking_data['passenger_count'],
            'total_amount' => $booking_data['total_amount'],
            'payment_method' => 'cash',
            'payment_status' => 'En attente de paiement',
            'qr_code_path' => $qr_code_path,
            'full_name' => $booking_data['full_name'],
            'email' => $booking_data['email'],
            'phone' => $booking_data['phone']
        ];

        unset($_SESSION['booking_data']);

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        $error = "Une erreur est survenue lors de la confirmation de votre réservation: " . $e->getMessage();
    }
} else {

    $booking_id = (int)$_GET['booking_id'];

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT b.*, d.name as destination_name, u.email, u.full_name, u.phone
            FROM bookings b
            LEFT JOIN destinations d ON b.destination = d.name
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            header("Location: index.php");
            exit();
        }

        $db_booking = $result->fetch_assoc();

        if (empty($db_booking['qr_code_path'])) {

            if (empty($db_booking['booking_reference'])) {
                $booking_reference = 'VOGIE-' . strtoupper(uniqid());

                $stmt = $conn->prepare("UPDATE bookings SET booking_reference = ? WHERE id = ?");
                $stmt->bind_param("si", $booking_reference, $booking_id);
                $stmt->execute();
            } else {
                $booking_reference = $db_booking['booking_reference'];
            }

            $qr_content = json_encode([
                'booking_id' => $booking_id,
                'reference' => $booking_reference,
                'user_id' => $db_booking['user_id'],
                'user_name' => $db_booking['full_name'],
                'from_city_id' => $db_booking['from_city_id'],
                'to_city_id' => $db_booking['to_city_id'],
                'passenger_count' => $db_booking['passenger_count'],
                'total_amount' => $db_booking['total_amount'],
                'timestamp' => time()
            ]);

            $qr_filename = 'vogie_qr_' . $booking_id . '.png';
            $qr_code_path = generateQRCodeWithAPI($qr_content, $qr_filename);

            if ($qr_code_path) {

                $stmt = $conn->prepare("UPDATE bookings SET qr_code_path = ? WHERE id = ?");
                $stmt->bind_param("si", $qr_code_path, $booking_id);
                $stmt->execute();
            }
        } else {
            $qr_code_path = $db_booking['qr_code_path'];
            $booking_reference = $db_booking['booking_reference'];
        }

        $booking = [
            'id' => $db_booking['id'],
            'reference' => $booking_reference ?? 'N/A',
            'destination' => $db_booking['destination'],
            'from_city_id' => $db_booking['from_city_id'],
            'to_city_id' => $db_booking['to_city_id'],
            'departure_date' => $db_booking['departure_date'],
            'departure_time' => $db_booking['departure_time'],
            'passenger_count' => $db_booking['passenger_count'],
            'total_amount' => $db_booking['total_amount'],
            'payment_method' => $db_booking['payment_method'],
            'payment_status' => ucfirst($db_booking['payment_status']),
            'booking_date' => date('d/m/Y H:i', strtotime($db_booking['booking_date'])),
            'email' => $db_booking['email'] ?? 'N/A',
            'full_name' => $db_booking['full_name'] ?? 'N/A',
            'phone' => $db_booking['phone'] ?? 'N/A',
            'qr_code_path' => $qr_code_path
        ];

    } catch (Exception $e) {
        $error = "Erreur lors de la récupération des détails de la réservation: " . $e->getMessage();
    }
}

$from_city_name = '';
$to_city_name = '';
if (isset($booking['from_city_id']) && isset($booking['to_city_id'])) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->bind_param("i", $booking['from_city_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) { $from_city_name = $row['name']; }

        $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->bind_param("i", $booking['to_city_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) { $to_city_name = $row['name']; }

        $stmt->close();
    } catch (Exception $e) {

    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Vogie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .receipt-header {
            background: linear-gradient(135deg,
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .receipt-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            padding: 10px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .receipt-logo img {
            max-width: 100%;
            max-height: 100%;
        }
        .receipt-body {
            background: white;
            padding: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .info-card {
            background:
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid
        }
        .qr-section {
            background: linear-gradient(135deg,
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        .qr-code-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            display: inline-block;
            margin: 1rem 0;
        }
        .reference-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin: 0.5rem;
        }
        .action-buttons {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-modern {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .status-success {
            background: linear-gradient(135deg,
            color: white;
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
    <style>

        .receipt-card { background:
        .receipt-head { display:flex; align-items:flex-start; justify-content:space-between; padding:16px 20px 8px; }
        .receipt-title { font-size:1.25rem; font-weight:600; margin:0; color:
        .receipt-ref { font-size:.9rem; color:
        .receipt-ref .ref-val { color:
        .receipt-qr { padding:8px; }
        .receipt-qr .qr-code-container { background:
        .receipt-body-compact { padding:8px 20px 0; }
        .receipt-section-title { font-weight:600; color:
        .receipt-item { margin-bottom:6px; }
        .receipt-item .label { color:
        .receipt-hr { border:0; border-top:1px solid
        .receipt-footer { display:flex; align-items:center; justify-content:space-between; padding:6px 20px 16px; }
        .receipt-total { font-weight:700; }
    </style>
    <style>
        .receipt-header {
            background: linear-gradient(135deg,
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .receipt-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            padding: 10px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .receipt-logo img {
            max-width: 100%;
            max-height: 100%;
        }
        .receipt-body {
            background: white;
            padding: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .info-card {
            background:
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid
        }
        .qr-section {
            background: linear-gradient(135deg,
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        .qr-code-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            display: inline-block;
            margin: 1rem 0;
        }
        .reference-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin: 0.5rem;
        }
        .action-buttons {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-modern {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .status-success {
            background: linear-gradient(135deg,
            color: white;
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Vogie</a>
        </div>
    </nav>

    <!-- Confirmation Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="booking.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la réservation
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Compact receipt card -->
                    <div class="receipt-card" id="receipt-card">
                        <div class="receipt-head">
                            <div>
                                <h3 class="receipt-title">Reçu de réservation</h3>
                                <div class="receipt-ref">Référence: <span class="ref-val"><?php echo htmlspecialchars($booking['reference']); ?></span></div>
                            </div>
                            <div class="receipt-qr">
                                <div class="qr-code-container">
                                    <?php if (!empty($booking['qr_code_path']) && file_exists('uploads/qr_codes/' . $booking['qr_code_path'])): ?>
                                        <img src="uploads/qr_codes/<?php echo htmlspecialchars($booking['qr_code_path']); ?>" alt="QR" style="max-width:140px;">
                                    <?php else: ?>
                                        <div id="qr-container"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="receipt-body-compact">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="receipt-section-title">Client</div>
                                    <div class="receipt-item"><strong><?php echo htmlspecialchars($booking['full_name'] ?? 'N/A'); ?></strong></div>
                                    <div class="receipt-item"><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></div>
                                    <div class="receipt-item"><?php echo htmlspecialchars($booking['phone'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="receipt-section-title">Détails</div>
                                    <?php if (!empty($from_city_name) && !empty($to_city_name)): ?>
                                    <div class="receipt-item"><span class="label">Trajet:</span><strong><?php echo htmlspecialchars($from_city_name . ' → ' . $to_city_name); ?></strong></div>
                                    <?php endif; ?>
                                    <div class="receipt-item"><span class="label">Date/Heure:</span><strong><?php echo htmlspecialchars(($booking['departure_date'] ?? '')); ?><?php echo !empty($booking['departure_time']) ? ' ' . htmlspecialchars($booking['departure_time']) : ''; ?></strong></div>
                                    <div class="receipt-item"><span class="label">Passagers:</span> <?php echo (int)($booking['passenger_count'] ?? 0); ?></div>
                                    <div class="receipt-item"><span class="label">Mode:</span> <?php echo $booking['payment_method'] === 'stripe' ? 'Carte bancaire' : 'Espèces'; ?></div>
                                    <div class="receipt-item"><span class="label">Statut:</span> <?php echo htmlspecialchars(strtolower($booking['payment_status'] ?? 'pending')); ?></div>
                                </div>
                            </div>
                            <hr class="receipt-hr">
                        </div>
                        <div class="receipt-footer">
                            <div class="text-muted">Créé le <?php echo isset($booking['booking_date']) ? $booking['booking_date'] : date('Y-m-d H:i:s'); ?></div>
                            <div class="receipt-total">Total: <span class="text-dark">MAD <?php echo number_format((float)($booking['total_amount'] ?? 0), 2, ',', ' '); ?></span></div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <div class="row justify-content-center">
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-secondary btn-modern w-100"><i class="fas fa-home me-2"></i>Accueil</a>
                            </div>
                            <div class="col-md-3">
                                <button id="download-pdf" type="button" class="btn btn-success btn-modern w-100"><i class="fas fa-file-pdf me-2"></i>Télécharger PDF</button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary btn-modern w-100" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimer</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Vogie. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function runWhenReady(fn){
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn();
    })(function(){

        const qrDiv = document.getElementById('qr-container');
        if (qrDiv) {
            const qrData = JSON.stringify({
                booking_id: <?php echo json_encode($booking['id'] ?? ''); ?>,
                reference: <?php echo json_encode($booking['reference'] ?? ''); ?>,
                user: <?php echo json_encode($booking['full_name'] ?? ''); ?>,
                from: <?php echo json_encode($from_city_name ?? ''); ?>,
                to: <?php echo json_encode($to_city_name ?? ''); ?>,
                pax: <?php echo json_encode($booking['passenger_count'] ?? 0); ?>,
                total: <?php echo json_encode($booking['total_amount'] ?? 0); ?>
            });
            try {
                new QRCode(qrDiv, { text: qrData, width: 140, height: 140, correctLevel: QRCode.CorrectLevel.H });
            } catch (e) {
                qrDiv.innerHTML = '<small class="text-muted">QR indisponible</small>';
            }
        }

        const btn = document.getElementById('download-pdf');
        if (btn) {
            btn.addEventListener('click', async function(event){
                event.preventDefault();
                if (!window.jspdf) { alert('PDF library not loaded.'); return; }
                const { jsPDF } = window.jspdf;
                const card = document.getElementById('receipt-card');
                if (!card) { alert('Reçu introuvable'); return; }
                const original = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Génération...';
                try {

                    const clone = card.cloneNode(true);
                    clone.style.width = card.offsetWidth + 'px';
                    clone.style.position = 'absolute'; clone.style.left = '-9999px';
                    document.body.appendChild(clone);
                    const canvas = await html2canvas(clone, { scale: 2, useCORS: true, logging: false });
                    document.body.removeChild(clone);
                    const img = canvas.toDataURL('image/png');
                    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                    const props = pdf.getImageProperties(img);
                    const pageW = pdf.internal.pageSize.getWidth() - 20; const pageH = (props.height * pageW) / props.width;
                    pdf.addImage(img, 'PNG', 10, 10, pageW, pageH);
                    pdf.save('recu_reservation_' + <?php echo json_encode($booking['reference'] ?? 'N_A'); ?> + '.pdf');
                } catch (e) {
                    console.error(e); alert('Erreur lors de la génération du PDF');
                } finally {
                    btn.disabled = false; btn.innerHTML = original;
                }
            });
        }
    });
    </script>
</body>
</html>
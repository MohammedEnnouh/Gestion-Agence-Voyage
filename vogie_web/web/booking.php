<?php
require_once 'config/config.php';

$from_city_id = $_GET['from_id'] ?? ($_GET['from'] ?? '');
$to_city_id = $_GET['to_id'] ?? ($_GET['to'] ?? '');
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$passengers = $_GET['pax'] ?? 1;
$price = $_GET['price'] ?? 0;

$from_city_name = '';
$to_city_name = '';
$route_details = null;

if ($from_city_id && $to_city_id) {
    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->bind_param("i", $from_city_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $from_city_name = $row['name'];
        }

        $stmt = $conn->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->bind_param("i", $to_city_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $to_city_name = $row['name'];
        }

        $stmt = $conn->prepare("
            SELECT r.*, from_city.name as from_city_name, to_city.name as to_city_name
            FROM routes r
            JOIN cities from_city ON r.from_city_id = from_city.id
            JOIN cities to_city ON r.to_city_id = to_city.id
            WHERE r.from_city_id = ? AND r.to_city_id = ?
        ");
        $stmt->bind_param("ii", $from_city_id, $to_city_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $route_details = $row;
        }

        $stmt->close();
    } catch (Exception $e) {

    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

if ((float)$price <= 0 && $route_details) {
    $price = (float)$route_details['price_per_person'] * (int)$passengers;
}

$cash_reference = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (isset($_POST['price'])) {
        $price = (float)$_POST['price'];
    }

    $total_amount = (float)$price;

    if ($payment_method === 'card') {

        if (isset($_POST['from_id'])) { $from_city_id = (int)$_POST['from_id']; }
        if (isset($_POST['to_id'])) { $to_city_id = (int)$_POST['to_id']; }
        $_SESSION['booking_data'] = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'from_city_id' => (int)$from_city_id,
            'to_city_id' => (int)$to_city_id,
            'departure_date' => $date,
            'departure_time' => $time,
            'passenger_count' => (int)$passengers,
            'payment_method' => 'stripe',
            'total_amount' => $total_amount,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'id_number' => $id_number,

            'idempotency_key' => bin2hex(random_bytes(16)),
            'created_at' => time(),
        ];
        header('Location: payment.php');
        exit;
    }

    if ($payment_method === 'cash') {

        $cash_reference = 'CASH-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

        if (isset($_POST['from_id'])) { $from_city_id = (int)$_POST['from_id']; }
        if (isset($_POST['to_id'])) { $to_city_id = (int)$_POST['to_id']; }
        if (isset($_POST['date'])) { $date = $_POST['date']; }
        if (isset($_POST['time'])) { $time = $_POST['time']; }
        if (isset($_POST['pax'])) { $passengers = (int)$_POST['pax']; }

        $_SESSION['booking_data'] = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'from_city_id' => (int)$from_city_id,
            'to_city_id' => (int)$to_city_id,
            'departure_date' => $date,
            'departure_time' => $time,
            'passenger_count' => (int)$passengers,
            'payment_method' => 'cash',
            'total_amount' => $total_amount,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,

        ];

        header('Location: confirmation.php?method=cash');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Vogie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .booking-summary { background:
        .route-info { background: white; border: 2px solid
        .price-highlight { font-size: 2rem; font-weight: bold; color:
        .form-section { background: white; border-radius: 10px; padding: 25px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .qr-box { border: 1px dashed

        .receipt-card { background:
        .receipt-head { display:flex; align-items:flex-start; justify-content:space-between; padding: 16px 20px 8px 20px; }
        .receipt-title { font-size: 1.25rem; font-weight: 600; margin: 0; color:
        .receipt-ref { font-size: 0.9rem; color:
        .receipt-ref .ref-val { color:
        .receipt-qr { padding: 8px; }
        .receipt-qr .qr-code-container { background:
        .receipt-body { padding: 8px 20px 0 20px; }
        .receipt-section-title { font-weight:600; color:
        .receipt-item { margin-bottom: 6px; }
        .receipt-item .label { color:
        .receipt-hr { border:0; border-top:1px solid
        .receipt-footer { display:flex; align-items:center; justify-content:space-between; padding: 6px 20px 16px 20px; }
        .receipt-total { font-weight:700; }
        .action-buttons { background: white; border-radius: 10px; padding: 1rem 1.25rem; margin-top: 1.25rem; box-shadow: 0 5px 15px rgba(0,0,0,0.06); }
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
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo 'search.php?from_id=' . urlencode($from_city_id) . '&to_id=' . urlencode($to_city_id) . '&date=' . urlencode($date) . '&pax=' . urlencode($passengers); ?>">Résultats</a></li>
                        <li class="breadcrumb-item active">Réservation</li>
                    </ol>
                </nav>

            <?php $hasSelection = !empty($from_city_id) && !empty($to_city_id) && !empty($date); ?>
            <?php if (!$hasSelection && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                <!-- Planning card (same experience as index) -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="assets/images/v.png" alt="Vogie Logo" height="80" class="mb-3">
                            <h1 class="fw-bold mb-3 text-center" style="color:black;">Planifiez votre trajet</h1>
                            <p class="text-center mb-4" style="color: var(--primary-color);">Choisissez vos villes, la date et le nombre de passagers</p>
                        </div>
                        <form id="route-search-form" class="row g-3" method="get" action="search.php">
                            <div class="col-md-6">
                                <label for="from-city" class="form-label">Ville de départ</label>
                                <select id="from-city" class="form-select" required></select>
                                <input type="hidden" id="from_id" name="from_id" value="">
                            </div>
                            <div class="col-md-6">
                                <label for="to-city" class="form-label">Ville d'arrivée</label>
                                <select id="to-city" class="form-select" required></select>
                                <input type="hidden" id="to_id" name="to_id" value="">
                            </div>
                            <div class="col-md-6">
                                <label for="travel-date" class="form-label">Date de départ</label>
                                <input type="date" id="travel-date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="passengers" class="form-label">Nombre de passagers</label>
                                <select class="form-select" id="passengers" name="pax" required>
                                    <option value="1">1 passager</option>
                                    <option value="2">2 passagers</option>
                                    <option value="3">3 passagers</option>
                                    <option value="4">4 passagers</option>
                                    <option value="5">5 passagers</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div id="price-display" class="text-center p-3 border rounded bg-light">
                                    <small class="text-muted">Sélectionnez les villes pour voir le prix</small>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-search me-2"></i>Recherche
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>

                document.addEventListener('DOMContentLoaded', function() {
                    const fromSelect = document.getElementById('from-city');
                    const toSelect = document.getElementById('to-city');
                    const fromIdInput = document.getElementById('from_id');
                    const toIdInput = document.getElementById('to_id');
                    const form = document.getElementById('route-search-form');
                    function syncHiddenIds() {
                        const fromId = fromSelect?.options[fromSelect.selectedIndex]?.getAttribute('data-id') || '';
                        const toId = toSelect?.options[toSelect.selectedIndex]?.getAttribute('data-id') || '';
                        fromIdInput.value = fromId; toIdInput.value = toId;
                    }
                    fromSelect && fromSelect.addEventListener('change', syncHiddenIds);
                    toSelect && toSelect.addEventListener('change', syncHiddenIds);
                    form && form.addEventListener('submit', function(e){
                        syncHiddenIds();
                        if (!fromIdInput.value || !toIdInput.value) {
                            e.preventDefault(); alert("Veuillez sélectionner les villes de départ et d'arrivée");
                        }
                    });
                });
                </script>
            <?php else: ?>
            <h1 class="mb-4"><i class="fas fa-ticket-alt text-primary me-2"></i>Confirmation de réservation</h1>

                <div class="route-info">
                    <!-- Static trajet display -->
                    <div class="row g-3 mb-2">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-route me-2 text-primary"></i>
                                <span class="fw-semibold">Trajet:</span>
                                <span class="ms-2 text-primary fw-bold"><?php echo htmlspecialchars(($from_city_name ?: '—') . ' → ' . ($to_city_name ?: '—')); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-calendar text-success me-2"></i>Date:</strong><br>
                                    <?php echo $date ? date('d/m/Y', strtotime($date)) : 'Non spécifiée'; ?>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fas fa-clock text-info me-2"></i>Heure:</strong><br>
                                <?php echo htmlspecialchars($time ?: 'Non spécifiée'); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fas fa-users text-warning me-2"></i>Passagers:</strong><br>
                                <?php echo (int)$passengers; ?>
                            </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                        <div class="price-highlight"><?php echo number_format((float)$price, 2); ?> MAD</div>
                            <small class="text-muted">Total du trajet</small>
                    </div>
                    </div>
                </div>

            <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($cash_reference === '' && ($_POST['payment_method'] ?? '') !== 'cash')): ?>
            <form id="booking-form" class="form-section" method="post">
                <input type="hidden" name="from_id" value="<?php echo htmlspecialchars($from_city_id); ?>">
                <input type="hidden" name="to_id" value="<?php echo htmlspecialchars($to_city_id); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="pax" value="<?php echo htmlspecialchars($passengers); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">

                <h4 class="mb-4"><i class="fas fa-user text-primary me-2"></i>Informations personnelles</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Nom complet *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_number" class="form-label">Numéro de carte d'identité *</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" required>
                        </div>
                    </div>

                    <hr class="my-4">

                <h4 class="mb-4"><i class="fas fa-credit-card text-primary me-2"></i>Paiement</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Méthode de paiement *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Choisir...</option>
                                <option value="cash">Espèces</option>
                                <option value="card">Carte bancaire</option>
                            </select>
                        </div>
                </div>

                <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i><strong>Prix total:</strong> <?php echo number_format((float)$price, 2); ?> MAD pour <?php echo (int)$passengers; ?> passager(s)</div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5"><i class="fas fa-check me-2"></i>Continuer</button>
                    <a href="<?php echo 'search.php?from_id=' . urlencode($from_city_id) . '&to_id=' . urlencode($to_city_id) . '&date=' . urlencode($date) . '&pax=' . urlencode($passengers); ?>" class="btn btn-outline-secondary btn-lg px-5 ms-3"><i class="fas fa-arrow-left me-2"></i>Retour</a>
                </div>
            </form>
            <?php else: ?>
                <!-- Cash payment confirmation receipt (compact design) -->
                <div class="receipt-card" id="receipt-card">
                    <div class="receipt-head">
                        <div>
                            <h3 class="receipt-title">Reçu de réservation</h3>
                            <div class="receipt-ref">Référence: <span class="ref-val"><?php echo htmlspecialchars($cash_reference); ?></span></div>
                        </div>
                        <div class="receipt-qr">
                            <div id="qr-container" class="qr-code-container"><!-- QR --></div>
                        </div>
                    </div>
                    <div class="receipt-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="receipt-section-title">Client</div>
                                <div class="receipt-item"><strong><?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?></strong></div>
                                <div class="receipt-item"><?php echo htmlspecialchars($_POST['email'] ?? ''); ?></div>
                                <div class="receipt-item"><?php echo htmlspecialchars($_POST['phone'] ?? ''); ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="receipt-section-title">Détails</div>
                                <div class="receipt-item"><span class="label">Trajet:</span><strong><?php echo htmlspecialchars($from_city_name . ' → ' . $to_city_name); ?></strong></div>
                                <div class="receipt-item"><span class="label">Date/Heure:</span><strong><?php echo !empty($date) ? date('Y-m-d', strtotime($date)) : ''; ?><?php echo !empty($time) ? ' ' . htmlspecialchars($time) : ''; ?></strong></div>
                                <div class="receipt-item"><span class="label">Passagers:</span> <?php echo (int)($_POST['pax'] ?? $passengers); ?></div>
                                <div class="receipt-item"><span class="label">Mode:</span> Espèces</div>
                                <div class="receipt-item"><span class="label">Statut:</span> pending</div>
                            </div>
                        </div>
                        <hr class="receipt-hr">
                    </div>
                    <div class="receipt-footer">
                        <div class="text-muted">Créé le <?php echo date('Y-m-d H:i:s'); ?></div>
                        <div class="receipt-total">Total: <span class="text-dark">MAD <?php echo number_format((float)($_POST['price'] ?? $price), 2, ',', ' '); ?></span></div>
                    </div>
                </div>
                <div class="action-buttons">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-modern w-100">
                                <i class="fas fa-home me-2"></i>Accueil
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button id="download-pdf" type="button" class="btn btn-success btn-modern w-100">
                                <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-modern w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Imprimer
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>

    <?php if (!empty($cash_reference)): ?>
    (function runWhenReady(fn){
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    })(function() {
        const fullName = <?php echo json_encode($_POST['full_name'] ?? ''); ?>;
        const email = <?php echo json_encode($_POST['email'] ?? ''); ?>;
        const phone = <?php echo json_encode($_POST['phone'] ?? ''); ?>;

        const qrContainer = document.getElementById('qr-container');
        if (qrContainer) {

            qrContainer.innerHTML = '';

            const qrData = JSON.stringify({
                reference: <?php echo json_encode($cash_reference); ?>,
                name: fullName,
                email: email,
                phone: phone,
                from: <?php echo json_encode($from_city_name); ?>,
                to: <?php echo json_encode($to_city_name); ?>
            });

            if (typeof QRCode !== 'undefined') {
                try {
                    new QRCode(qrContainer, {
                        text: qrData,
                        width: 140,
                        height: 140,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } catch (e) {
                    console.error('qrcode.js error:', e);
                }
            }

            if (!qrContainer.firstChild) {
                console.log('qrcode.js missing, using image fallback with data URL');
                const qrImg = document.createElement('img');
                qrImg.alt = 'QR Code';
                qrImg.style.maxWidth = '100%';
                qrImg.style.height = 'auto';
                qrImg.style.border = '1px solid #e5e7eb';
                qrContainer.appendChild(qrImg);

                (async () => {
                    try {
                        const url = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' + encodeURIComponent(qrData);
                        const resp = await fetch(url, { mode: 'cors' });
                        if (!resp.ok) throw new Error('QR fetch failed');
                        const blob = await resp.blob();
                        const reader = new FileReader();
                        reader.onload = () => { qrImg.src = reader.result; };
                        reader.onerror = () => { throw new Error('FileReader failed'); };
                        reader.readAsDataURL(blob);
                    } catch (e) {
                        console.warn('Primary QR service failed, trying Google Charts', e);
                        const url2 = 'https://chart.googleapis.com/chart?cht=qr&chs=140x140&chld=M|0&chl=' + encodeURIComponent(qrData);
                        try {
                            const resp2 = await fetch(url2, { mode: 'cors' });
                            if (!resp2.ok) throw new Error('Google QR fetch failed');
                            const blob2 = await resp2.blob();
                            const reader2 = new FileReader();
                            reader2.onload = () => { qrImg.src = reader2.result; };
                            reader2.onerror = () => { qrContainer.innerHTML = '<small class="text-muted">QR indisponible</small>'; };
                            reader2.readAsDataURL(blob2);
                        } catch (e2) {
                            console.error('All QR fallbacks failed', e2);
                            qrContainer.innerHTML = '<small class="text-muted">QR indisponible</small>';
                        }
                    }
                })();
            }

            const referenceDiv = document.createElement('div');
            referenceDiv.className = 'text-center mt-3';
            referenceDiv.innerHTML = `
                <div class="reference-badge">
                    <i class="fas fa-hashtag me-2"></i>
                    ${<?php echo json_encode($cash_reference); ?>}
                </div>`;
            qrContainer.parentNode.insertBefore(referenceDiv, qrContainer.nextSibling);
        } else {
            console.error('QR container not found');
        }

        const downloadBtn = document.getElementById('download-pdf');
        if (downloadBtn) {
            console.log('Download button found');

            downloadBtn.style.cursor = 'pointer';
            downloadBtn.title = 'Cliquez pour télécharger le PDF';

            downloadBtn.addEventListener('click', async function(event) {

                event && event.preventDefault && event.preventDefault();
                console.log('Download button clicked');

                const originalText = downloadBtn.innerHTML;
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Génération du PDF...';

                try {

                    const receipt = document.querySelector('#receipt-card');
                    if (!receipt) {
                        throw new Error('Receipt element not found');
                    }

                    const receiptClone = receipt.cloneNode(true);
                    receiptClone.style.width = receipt.offsetWidth + 'px';
                    receiptClone.style.position = 'absolute';
                    receiptClone.style.left = '-9999px';
                    document.body.appendChild(receiptClone);

                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF({
                        orientation: 'portrait',
                        unit: 'mm',
                        format: 'a4'
                    });

                    const canvas = await (window.html2canvas || html2canvas)(receiptClone, {
                        scale: 2,
                        logging: false,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    });

                    document.body.removeChild(receiptClone);

                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                    pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth, pdfHeight);

                    pdf.save('recu_reservation_' + <?php echo json_encode($cash_reference); ?> + '.pdf');

                } catch (error) {
                    console.error('Error generating PDF:', error);
                    alert('Erreur lors de la génération du PDF. Veuillez réessayer ou contacter le support.');
                } finally {

                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalText;
                }
            });
        } else {
            console.error('Download button not found');
        }
    })();
    <?php else: ?>
    console.log('Not in cash payment mode');
    <?php endif; ?>
    </script>

    <?php include 'includes/footer.php'; ?>
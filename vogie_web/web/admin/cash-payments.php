<?php
require_once "auth.php";
requireAdmin();
require_once "../config/config.php";

$page_title = "Paiements Espèces - Vogie Admin";

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT b.*, u.full_name, c1.name as from_city, c2.name as to_city FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN cities c1 ON b.from_city_id=c1.id LEFT JOIN cities c2 ON b.to_city_id=c2.id WHERE b.payment_method=
cash ORDER BY b.booking_date DESC");
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Erreur lors du chargement des paiements: " . $e->getMessage();
    $payments = [];
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

include "includes/header.php";
?>

<div class="page-header">
    <h2><i class="fas fa-money-bill-wave me-2"></i>Paiements Espèces</h2>
</div>

<div class="card section-card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Paiements Espèces</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Client</th>
                            <th>Trajet</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment["booking_reference"] ?? "N/A"); ?></td>
                                <td><?php echo htmlspecialchars($payment["full_name"] ?? "N/A"); ?></td>
                                <td><?php echo htmlspecialchars(($payment["from_city"] ?? "N/A") . "  " . ($payment["to_city"] ?? "N/A")); ?></td>
                                <td><?php echo number_format($payment["total_amount"], 2); ?> MAD</td>
                                <td>
                                    <span class="badge bg-<?php echo $payment["payment_status"] === "completed" ? "success" : "warning"; ?>">
                                        <?php echo ucfirst($payment["payment_status"]); ?>
                                    </span>
                                </td>
                                <td><?php echo date("d/m/Y H:i", strtotime($payment["booking_date"])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun paiement espèces trouvé</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
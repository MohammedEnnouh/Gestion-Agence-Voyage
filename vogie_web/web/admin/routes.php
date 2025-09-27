<?php
require_once "auth.php";
requireAdmin();
require_once "../config/config.php";

$page_title = "Gestion des Trajets - Vogie Admin";

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.*, c1.name as from_city, c2.name as to_city FROM routes r JOIN cities c1 ON r.from_city_id=c1.id JOIN cities c2 ON r.to_city_id=c2.id ORDER BY c1.name, c2.name");
    $stmt->execute();
    $routes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Erreur lors du chargement des trajets: " . $e->getMessage();
    $routes = [];
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

include "includes/header.php";
?>

<div class="page-header">
    <h2><i class="fas fa-route me-2"></i>Gestion des Trajets</h2>
</div>

<div class="card section-card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Trajets</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($routes)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>De</th>
                            <th>Vers</th>
                            <th>Prix (MAD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td><?php echo $route["id"]; ?></td>
                                <td><?php echo htmlspecialchars($route["from_city"]); ?></td>
                                <td><?php echo htmlspecialchars($route["to_city"]); ?></td>
                                <td><?php echo number_format($route["price_per_person"], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-route fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun trajet trouvé</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
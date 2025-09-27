<?php
require_once "auth.php";
requireAdmin();
require_once "../config/config.php";

$page_title = "Gestion des Villes - Vogie Admin";

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM cities ORDER BY name");
    $stmt->execute();
    $cities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Erreur lors du chargement des villes: " . $e->getMessage();
    $cities = [];
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

include "includes/header.php";
?>

<div class="page-header">
    <h2><i class="fas fa-city me-2"></i>Gestion des Villes</h2>
</div>

<div class="card section-card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Villes</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($cities)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cities as $city): ?>
                            <tr>
                                <td><?php echo $city["id"]; ?></td>
                                <td><?php echo htmlspecialchars($city["name"]); ?></td>
                                <td><?php echo htmlspecialchars($city["description"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune ville trouvée</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
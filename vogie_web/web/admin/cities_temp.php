<?php
require_once "auth.php";
requireAdmin();

require_once "../config/config.php";
$page_title = "Gestion des Villes - Vogie Admin";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        $conn = getDBConnection();

        switch ($action) {
            case 'add':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if (!empty($name)) {
                    $stmt = $conn->prepare("INSERT INTO cities (name, description) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $description);
                    $stmt->execute();
                    $success = "Ville ajoutée avec succès";
                 else {
                    $error = "Le nom de la ville est obligatoire";

                break;

            case 'edit':
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if ($id > 0 && !empty($name)) {
                    $stmt = $conn->prepare("UPDATE cities SET name = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $name, $description, $id);
                    $stmt->execute();
                    $success = "Ville mise à jour avec succès";
                 else {
                    $error = "Données invalides";

                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);

                if ($id > 0) {

                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM routes WHERE from_city_id = ? OR to_city_id = ?");
                    $stmt->bind_param("ii", $id, $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'];

                    if ($count > 0) {
                        $error = "Impossible de supprimer cette ville car elle est utilisée dans des trajets";
                     else {
                        $stmt = $conn->prepare("DELETE FROM cities WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Ville supprimée avec succès";

                break;

        $stmt->close();
     catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
     finally {
        if (isset($conn)) {
            $conn->close();

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT * FROM cities ORDER BY name");
    $stmt->execute();
    $cities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
 catch (Exception $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
 finally {
    if (isset($conn)) {
        $conn->close();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Villes - Admin Vogie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg,

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);

        .city-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">VOGIE Admin</h4>
                        <small class="text-white-50">Panel d'administration</small>
                    </div>

                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-list me-2"></i>Réservations
                        </a>
                        <a class="nav-link" href="cash-payments.php">
                            <i class="fas fa-money-bill-wave me-2"></i>Paiements Espèces
                        </a>
                        <a class="nav-link active" href="cities.php">
                            <i class="fas fa-city me-2"></i>Villes
                        </a>
                        <a class="nav-link" href="routes.php">
                            <i class="fas fa-route me-2"></i>Trajets
                        </a>
                        <a class="nav-link" href="times.php">
                            <i class="fas fa-clock me-2"></i>Horaires
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Utilisateurs
                        </a>
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-city me-2"></i>Gestion des Villes</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCityModal">
                        <i class="fas fa-plus me-2"></i>Ajouter une ville
                    </button>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Cities List -->
                <div class="row">
                    <div class="col-12">
                        <?php if (!empty($cities)): ?>
                            <?php foreach ($cities as $city): ?>
                                <div class="city-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($city['name']); ?></h5>
                                            <?php if (!empty($city['description'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($city['description']); ?></small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-4">
                                            <small class="text-muted">ID: <?php echo $city['id']; ?></small>
                                        </div>

                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-primary btn-sm"
                                                    onclick="editCity(<?php echo $city['id']; ?>, '<?php echo htmlspecialchars($city['name']); ?>', '<?php echo htmlspecialchars($city['description'] ?? ''); ?>')">
                                                <i class="fas fa-edit me-1"></i>Modifier
                                            </button>

                                            <button class="btn btn-danger btn-sm"
                                                    onclick="deleteCity(<?php echo $city['id']; ?>, '<?php echo htmlspecialchars($city['name']); ?>')">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-city fa-3x mb-3"></i>
                                <h5>Aucune ville trouvée</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add City Modal -->
    <div class="modal fade" id="addCityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une ville</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la ville *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit City Modal -->
    <div class="modal fade" id="editCityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la ville</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nom de la ville *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCity(id, name, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            new bootstrap.Modal(document.getElementById('editCityModal')).show();

        function deleteCity(id, name) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer la ville "${name" ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id">
                `;
                document.body.appendChild(form);
                form.submit();

    </script>
</body>
</html>
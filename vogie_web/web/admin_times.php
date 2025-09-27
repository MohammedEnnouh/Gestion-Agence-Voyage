<?php
require_once 'config/config.php';

$conn = getDBConnection();
$cities = $conn->query("SELECT id, name FROM cities ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['from_city_id'], $_POST['to_city_id'], $_POST['time'])) {
    $from_city_id = (int)$_POST['from_city_id'];
    $to_city_id = (int)$_POST['to_city_id'];
    $time = $_POST['time'];

    $stmt = $conn->prepare("SELECT id FROM routes WHERE from_city_id = ? AND to_city_id = ? LIMIT 1");
    $stmt->bind_param('ii', $from_city_id, $to_city_id);
    $stmt->execute();
    $route = $stmt->get_result()->fetch_assoc();
    if (!$route) {

        $stmt2 = $conn->prepare("INSERT INTO routes (from_city_id, to_city_id, price_per_person) VALUES (?, ?, 100.00)");
        $stmt2->bind_param('ii', $from_city_id, $to_city_id);
        $stmt2->execute();
        $route_id = $conn->insert_id;
    } else {
        $route_id = $route['id'];
    }
    $stmt = $conn->prepare("INSERT INTO route_times (route_id, time) VALUES (?, ?)");
    $stmt->bind_param('is', $route_id, $time);
    $stmt->execute();
    $success = 'Heure ajoutée avec succès!';
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM route_times WHERE id = $del_id");
    header('Location: admin_times.php');
    exit;
}

$times = $conn->query("SELECT rt.id, c1.name AS from_city, c2.name AS to_city, rt.time FROM route_times rt JOIN routes r ON rt.route_id = r.id JOIN cities c1 ON r.from_city_id = c1.id JOIN cities c2 ON r.to_city_id = c2.id ORDER BY from_city, to_city, rt.time")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des horaires de trajets</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .table thead { background:
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <h2 class="mb-4 text-primary"><i class="fas fa-clock me-2"></i>Ajouter une heure à un trajet</h2>
                    <?php if (!empty($success)) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
                    <form method="POST" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="from_city_id" class="form-label">Ville de départ</label>
                            <select name="from_city_id" id="from_city_id" class="form-select" required>
                                <option value="">Sélectionnez la ville de départ</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="to_city_id" class="form-label">Ville d'arrivée</label>
                            <select name="to_city_id" id="to_city_id" class="form-select" required>
                                <option value="">Sélectionnez la ville d'arrivée</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="time" class="form-label">Heure</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Ajouter</button>
                        </div>
                    </form>
                </div>
                <div class="card p-4">
                    <h3 class="mb-3">Horaires existants</h3>
                    <table class="table table-bordered table-hover">
                        <thead><tr><th>Trajet</th><th>Heure</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($times as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['from_city']) ?> → <?= htmlspecialchars($t['to_city']) ?></td>
                                    <td><?= htmlspecialchars($t['time']) ?></td>
                                    <td>
                                        <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette heure ?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
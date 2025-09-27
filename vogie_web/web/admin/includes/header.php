<?php
if (!isset($page_title)) {
    $page_title = "Admin Panel - Vogie";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .sidebar {
            background:
            min-height: 100vh;
            color: white;
        }
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.06);
            margin-bottom: 1rem;
        }
        .section-card .card-header {
            background:
            border-bottom: 1px solid
        }
        .badge-soft {
            background:
            color:
        }
        .nav-link.active {
            background-color:
            color: white !important;
            border-radius: 5px;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .main-content {
            background-color:
            min-height: 100vh;
        }
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-bus me-2"></i>VOGIE Admin
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white <?php echo (basename($_SERVER["PHP_SELF"]) == "dashboard.php") ? "active" : ""; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "cities.php") ? "active" : ""; ?>" href="cities.php">
                            <i class="fas fa-city me-2"></i>Villes
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "routes.php") ? "active" : ""; ?>" href="routes.php">
                            <i class="fas fa-route me-2"></i>Trajets
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "times.php") ? "active" : ""; ?>" href="times.php">
                            <i class="fas fa-clock me-2"></i>Horaires
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "cash-payments.php") ? "active" : ""; ?>" href="cash-payments.php">
                            <i class="fas fa-money-bill-wave me-2"></i>Paiements Espèces
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "bookings.php") ? "active" : ""; ?>" href="bookings.php">
                            <i class="fab fa-cc-stripe me-2"></i>Paiements en Ligne
                        </a>
                        <a class="nav-link text-white-50 <?php echo (basename($_SERVER["PHP_SELF"]) == "users.php") ? "active" : ""; ?>" href="users.php">
                            <i class="fas fa-users me-2"></i>Utilisateurs
                        </a>
                        <hr class="my-3" style="border-color: #6c757d;">
                        <a class="nav-link text-white-50" href="../index.php">
                            <i class="fas fa-home me-2"></i>Retour au Site
                        </a>
                        <a class="nav-link text-white-50" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
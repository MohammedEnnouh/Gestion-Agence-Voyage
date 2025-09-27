<?php

ob_start();

if (!isset($pageTitle)) {
    $pageTitle = $config['app_name'];
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Page-specific CSS -->
    <?php if (file_exists("assets/css/{$currentPage}.css")): ?>
        <link rel="stylesheet" href="/assets/css/<?php echo $currentPage; ?>.css">
    <?php endif; ?>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="visually-hidden-focusable skip-link">Skip to main content</a>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #E91E63;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php" style="color:#fff;">
                <img src="assets/images/v.png" alt="Vogie Logo" height="40" class="me-2">
                Vogie
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php" style="color:#fff;">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="booking.php" style="color:#fff;">Réserver</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php" style="color:#fff;">Contact</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:#fff;">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="../web/profile.php"><i class="fas fa-user me-2"></i> Mon Profil</a></li>
                                <li><a class="dropdown-item" href="../web/my-bookings.php"><i class="fas fa-suitcase me-2"></i> Mes Réservations</a></li>
                                <li><a class="dropdown-item" href="../web/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="login.php" style="border-color:#fff;color:#fff;"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <script>

    document.addEventListener('DOMContentLoaded', function () {
        var trigger = document.getElementById('userDropdown');
        if (!trigger) return;
        var parentLi = trigger.closest('.dropdown');
        var menu = parentLi ? parentLi.querySelector('.dropdown-menu') : null;
        if (!menu) return;

        function closeMenu() {
            parentLi.classList.remove('show');
            menu.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function (e) {

            e.preventDefault();
            var isOpen = parentLi.classList.contains('show');
            if (isOpen) {
                closeMenu();
            } else {
                parentLi.classList.add('show');
                menu.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', function (e) {
            if (!parentLi.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    });
    </script>

    <!-- Search Bar (Hidden by default) -->
    <div class="search-overlay bg-white shadow-sm" id="searchOverlay" style="display: none;">
        <div class="container py-3">
            <div class="row">
                <div class="col-12">
                    <div class="position-relative">
                        <form action="/search.php" method="get" class="d-flex">
                            <input type="text" class="form-control form-control-lg" placeholder="Search destinations, tours, experiences..." autofocus>
                            <button type="submit" class="btn btn-primary ms-2">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y me-5" id="closeSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <main id="main-content" class="flex-shrink-0">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php
                        echo htmlspecialchars($_SESSION['flash_message']);
                        unset($_SESSION['flash_message']);
                        unset($_SESSION['flash_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
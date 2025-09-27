<?php
$config = require __DIR__ . '/../config.php';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($config['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Font: Cairo -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="/Vogie2/public/assets/styles.css" rel="stylesheet">
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="/Vogie2/public/index.php" class="text-decoration-none d-flex align-items-center justify-content-center gap-2">
        <img src="/Vogie2/v.png" alt="Logo" class="brand-logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
        <div class="brand-name">Gestion de Transport</div>
      </a>

    </div>
  <ul class="menu compact">
    <li><a href="/Vogie2/public/index.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
    <li><a href="/Vogie2/public/index.php?page=villes" class="nav-link"><i class="fas fa-map-marker-alt"></i><span>Cities</span></a></li>
    <li><a href="/Vogie2/public/index.php?page=trajets" class="nav-link"><i class="fas fa-route"></i><span>Routes</span></a></li>
    <li><a href="/Vogie2/public/index.php?page=clients" class="nav-link"><i class="fas fa-user-friends"></i><span>Customers</span></a></li>
    <li><a href="/Vogie2/public/index.php?page=paiements" class="nav-link"><i class="fas fa-calendar-check"></i><span>Bookings</span></a></li>
  </ul>
  <div class="sidebar-footer">
    <?php if (!empty($_SESSION['user'])): ?>
      <a href="/Vogie2/public/logout.php" class="btn btn-outline-danger w-100"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
    <?php else: ?>
      <a href="/Vogie2/public/login.php" class="btn btn-outline-secondary w-100"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
    <?php endif; ?>
    <div class="text-center small mt-2" style="opacity:.7">© <?= date('Y') ?> Gestion de Transport</div>
  </div>

</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Top bar (for mobile + spacing) -->
<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <button class="btn btn-light d-lg-none" id="sidebarOpenBtn" aria-label="Ouvrir le menu">
      <i class="fas fa-bars"></i>
    </button>
    <a class="navbar-brand ms-2" href="/Vogie2/public/index.php">Gestion de Transport</a>
    <div class="d-none d-lg-flex align-items-center gap-2 ms-5">
      <button type="button" class="btn btn-outline-secondary" onclick="hideSidebar()" title="Hide/Show sidebar">
        <i class="fas fa-eye-slash"></i>
      </button>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin'): ?>
        <span class="text-white-50 small d-none d-md-inline">Bonjour, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin') ?></span>
        <a href="/Vogie2/public/logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-right-from-bracket me-1"></i>Déconnexion</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="content-wrapper">
  <div class="container my-4">
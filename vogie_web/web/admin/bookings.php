<?php
require_once "auth.php";
requireAdmin();
require_once "../config/config.php";
$page_title = "Paiements en Ligne - Vogie Admin";
include "includes/header.php";
?>
<div class="page-header">
    <h2><i class="fab fa-cc-stripe me-2"></i>Paiements en Ligne</h2>
</div>
<div class="card section-card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Paiements en Ligne</h5>
    </div>
    <div class="card-body">
        <p>Fonctionnalité en cours de développement...</p>
    </div>
</div>
<?php include "includes/footer.php"; ?>
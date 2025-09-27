<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vogie - Votre compagnon de voyage</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
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
                            </div>

                                <!-- Price Display Area -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div id="price-display" class="text-center p-3 border rounded bg-light">
                                            <small class="text-muted">Sélectionnez les villes pour voir le prix</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4 mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-search me-2"></i>Recherche
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Destinations Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Nos Destinations Populaires</h2>
            <div class="row" id="destinations-container">
                <!-- Destinations will be loaded here via JavaScript -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
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
            fromIdInput.value = fromId;
            toIdInput.value = toId;
        }

        if (fromSelect) fromSelect.addEventListener('change', syncHiddenIds);
        if (toSelect) toSelect.addEventListener('change', syncHiddenIds);
        if (form) form.addEventListener('submit', function(e) {
            syncHiddenIds();
            if (!fromIdInput.value || !toIdInput.value) {
                e.preventDefault();
                alert('Veuillez sélectionner les villes de départ et d\'arrivée');
            }
        });
    });
    </script>
</body>
</html>
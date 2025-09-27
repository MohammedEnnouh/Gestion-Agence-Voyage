<?php
require_once 'config/config.php';
$conn = getDBConnection();
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

if ($auth->isLoggedIn()) {
    header('Location: /');
    exit;
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'full_name' => trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '')),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];

    $result = $auth->register($formData);

    if ($result['success']) {

        header('Location: /?registered=1');
        exit;
    } else {
        $errors = $result['errors'] ?? ['Registration failed. Please try again.'];
    }
}

$pageTitle = 'Create Account - ' . $config['app_name'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #ffb6c1 0%, #ffe4ec 100%);">
                <div class="card-body p-4 p-md-5">
                    <h2 class="text-center mb-4" style="color:#d63384;">Créer un compte</h2>
                    <p class="text-center text-muted mb-4">Rejoignez-nous et commencez votre voyage avec Vogie</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" id="registerForm" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                    required
                                >
                                <div class="invalid-feedback">
                                    Veuillez entrer votre prénom
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom de famille <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                    required
                                >
                                <div class="invalid-feedback">
                                    Veuillez entrer votre nom de famille
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($formData['email']); ?>"
                                required
                            >
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse e-mail valide
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Numéro de téléphone <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+212</span>
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                    placeholder="6XX-XXXXXX"
                                    pattern="[5-7][0-9]{8}"
                                    maxlength="9"
                                    required
                                >
                                <div class="invalid-feedback">
                                    Veuillez entrer un numéro marocain valide (ex: 6XXXXXXXX)
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control password-toggle"
                                        id="password"
                                        name="password"
                                        required
                                        minlength="8"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Le mot de passe doit comporter au moins 8 caractères"
                                    >
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Le mot de passe doit comporter au moins 8 caractères
                                    </div>
                                </div>
                                <div class="form-text">
                                    <small>Au moins 8 caractères</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control password-toggle"
                                        id="confirm_password"
                                        name="confirm_password"
                                        required
                                        data-match="#password"
                                        data-match-error="Les mots de passe ne correspondent pas"
                                    >
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Les mots de passe ne correspondent pas
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="terms"
                                name="terms"
                                required
                            >
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="terms.php" target="_blank">Conditions d'utilisation</a> et
                                <a href="privacy.php" target="_blank">Politique de confidentialité</a>
                            </label>
                            <div class="invalid-feedback">
                                Vous devez accepter les termes et conditions
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="background-color:#d63384;border:none;">
                            Créer un compte
                        </button>

                        <div class="text-center mt-3">
                            <p>Déjà inscrit ? <a href="login.php" style="color:#d63384;">Se connecter</a></p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Social signup options (optional) -->
            <div class="mt-4 text-center">
                <p class="text-muted">Ou inscrivez-vous avec</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fab fa-google"></i> Google
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    const form = document.getElementById('registerForm');
    if (form) {

        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {

            let value = this.value.replace(/\D/g, '');

            value = value.substring(0, 9);
            this.value = value;
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
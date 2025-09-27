<?php
require_once 'config/config.php';
$conn = getDBConnection();
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $result = $auth->login($email, $password);

    if ($result['success']) {

        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60);

            $stmt = $conn->prepare("
                UPDATE users
                SET remember_token = ?, remember_token_expires = ?
                WHERE id = ?
            ");
            $expiresDate = date('Y-m-d H:i:s', $expires);
            $stmt->bind_param('ssi', $token, $expiresDate, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            setcookie(
                'remember_token',
                $token,
                $expires,
                '/',
                '',
                isset($_SERVER['HTTPS']),
                true
            );
        }

        header("Location: $redirect");
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login - Vogie';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #ffb6c1 0%, #ffe4ec 100%);">
                <div class="card-body p-4 p-md-5">
                    <h2 class="text-center mb-4" style="color:#d63384;">Connexion à Vogie</h2>
                    <p class="text-center mb-4 text-muted">Connectez-vous pour accéder à votre espace personnel</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['registered'])): ?>
                        <div class="alert alert-success">
                            Inscription réussie ! Connectez-vous avec vos identifiants.
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['reset'])): ?>
                        <div class="alert alert-success">
                            Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.
                        </div>
                    <?php endif; ?>
                    <form method="post" action="" id="loginForm" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input
                                type="email"
                                class="form-control <?php echo isset($error) && $error ? 'is-invalid' : ''; ?>"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                required
                            >
                            <div class="invalid-feedback">
                                Veuillez saisir une adresse email valide
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Mot de passe</label>
                                <a href="forgot-password.php" class="small">Mot de passe oublié ?</a>
                            </div>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control password-toggle <?php echo isset($error) && $error ? 'is-invalid' : ''; ?>"
                                    id="password"
                                    name="password"
                                    required
                                    minlength="8"
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre mot de passe (min 8 caractères)
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="background-color:#d63384;border:none;">
                            Se connecter
                        </button>
                        <div class="text-center mt-3">
                            <p>Pas encore de compte ? <a href="register.php" style="color:#d63384;">Créer un compte</a></p>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mt-4 text-center">
                <p class="text-muted">Ou connectez-vous avec</p>
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

    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

if ($auth->isLoggedIn()) {
    header('Location: /');
    exit;
}

$message = '';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $result = $auth->sendPasswordResetEmail($email);

        if ($result['success']) {
            $message = $result['message'];
            $email = '';
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Forgot Password - ' . $config['app_name'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Reset Your Password</h2>
                    <p class="text-muted text-center mb-4">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" id="forgotPasswordForm" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input
                                type="email"
                                class="form-control <?php echo $error && !$message ? 'is-invalid' : ''; ?>"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                required
                                autofocus
                            >
                            <div class="invalid-feedback">
                                Please enter a valid email address
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            Send Reset Link
                        </button>

                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    Don't have an account? <a href="register.php">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('forgotPasswordForm');
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
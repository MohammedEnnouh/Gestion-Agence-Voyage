<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

if ($auth->isLoggedIn()) {
    header('Location: /');
    exit;
}

$token = $_GET['token'] ?? '';
$message = '';
$error = '';
$validToken = false;
$email = '';

if (!empty($token)) {
    $stmt = $conn->prepare("
        SELECT email
        FROM password_resets
        WHERE token = ? AND created_at > NOW()
    ");

    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();

    if ($reset) {
        $validToken = true;
        $email = $reset['email'];
    } else {
        $error = 'Invalid or expired token. Please request a new password reset link.';
    }
} else {
    $error = 'No token provided. Please use the link from your email.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {

        $result = $auth->resetPassword($token, $password);

        if ($result['success']) {
            $message = $result['message'] . ' You can now <a href="login.php">log in</a> with your new password.';
            $validToken = false;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Reset Password - ' . $config['app_name'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Reset Your Password</h2>

                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($validToken): ?>
                        <p class="text-muted">
                            Please enter your new password for <strong><?php echo htmlspecialchars($email); ?></strong>
                        </p>

                        <form method="post" action="" id="resetPasswordForm" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
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
                                        title="Password must be at least 8 characters long"
                                    >
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Password must be at least 8 characters long
                                    </div>
                                </div>
                                <div class="form-text">
                                    <small>At least 8 characters</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control password-toggle"
                                        id="confirm_password"
                                        name="confirm_password"
                                        required
                                        data-match="#password"
                                        data-match-error="Passwords do not match"
                                    >
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Passwords do not match
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                Reset Password
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <p>Please check your email for a password reset link or request a new one.</p>
                            <a href="forgot-password.php" class="btn btn-primary">
                                Request New Reset Link
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    Remember your password? <a href="login.php">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if ($validToken): ?>
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

    const form = document.getElementById('resetPasswordForm');
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
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
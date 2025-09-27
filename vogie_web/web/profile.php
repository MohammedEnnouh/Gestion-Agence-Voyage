<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

if (!isset($conn) || !$conn) {
    $conn = getDBConnection();
}

$auth = new AuthController($conn, $config);

$auth->requireAuth();

$user = $auth->getCurrentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $errors = [];

    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE users
            SET full_name = ?, phone = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->bind_param('ssi', $fullName, $phone, $user['id']);

        if ($stmt->execute()) {

            $_SESSION['user_name'] = $fullName;
            $user = $auth->getCurrentUser();
            $message = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile. Please try again.';
        }

        $stmt->close();
    } else {
        $error = implode('<br>', $errors);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match';
    }

    if (empty($errors)) {

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();

        if ($userData && password_verify($currentPassword, $userData['password'])) {

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $user['id']);

            if ($stmt->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }

            $stmt->close();
        } else {
            $error = 'Current password is incorrect';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$pageTitle = 'My Profile - ' . $config['app_name'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-xxl mb-3">
                        <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php
                                $initials = '';
                                $nameParts = explode(' ', $user['full_name']);
                                foreach ($nameParts as $part) {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                    if (strlen($initials) >= 2) break;
                                }
                                echo $initials;
                            ?>
                        </div>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>

                    <div class="list-group list-group-flush">
                        <a href="../web/profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                        <a href="../web/my-bookings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-suitcase me-2"></i> My Bookings
                        </a>
                        <a href="../web/wishlist.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i> Wishlist
                        </a>
                        <a href="../web/settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <a href="../web/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Flash Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Update Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="" id="profileForm">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="full_name"
                                name="full_name"
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                disabled
                            >
                            <small class="text-muted">Contact support to change your email address</small>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+212</span>
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo htmlspecialchars($user['phone']); ?>"
                                    placeholder="6XX-XXXXXX"
                                    pattern="[0-9]{9}"
                                    required
                                >
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="" id="passwordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control password-toggle"
                                    id="current_password"
                                    name="current_password"
                                    required
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control password-toggle"
                                    id="new_password"
                                    name="new_password"
                                    minlength="8"
                                    required
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <small>Password must be at least 8 characters long</small>
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
                                    data-match="#new_password"
                                    data-match-error="Passwords do not match"
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>
                        </div>
                    </form>
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

    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(event) {
            if (!profileForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            profileForm.classList.add('was-validated');
        }, false);
    }

    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {

        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        if (newPassword && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }

        passwordForm.addEventListener('submit', function(event) {
            if (!passwordForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            passwordForm.classList.add('was-validated');
        }, false);
    }

    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {

            let value = this.value.replace(/\D/g, '');

            if (value.length > 0) {
                value = value.substring(0, 9);

                if (value.length > 3) {
                    value = value.substring(0, 3) + '-' + value.substring(3);
                }

                this.value = value;
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
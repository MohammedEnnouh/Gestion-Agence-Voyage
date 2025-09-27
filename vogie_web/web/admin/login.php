<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	if (!empty($email) && !empty($password)) {
		try {
			$conn = getDBConnection();

			$stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($user = $result->fetch_assoc()) {
				$storedPassword = $user['password'] ?? '';
				$loggedIn = false;

				$looksHashed = is_string($storedPassword) && (str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$argon2'));

				if ($looksHashed) {
					$loggedIn = password_verify($password, $storedPassword);
				} else {

					$loggedIn = hash_equals((string)$storedPassword, (string)$password);

					if ($loggedIn) {
						$newHash = password_hash($password, PASSWORD_BCRYPT);
						$up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
						$up->bind_param("si", $newHash, $user['id']);
						$up->execute();
						$up->close();
					}
				}

				if ($loggedIn) {
					$_SESSION['user_id'] = $user['id'];
					$_SESSION['full_name'] = $user['full_name'];
					$_SESSION['email'] = $user['email'];
					$_SESSION['role'] = $user['role'];

					header('Location: index.php');
					exit;
				} else {
					$error = "Mot de passe incorrect";
				}
			} else {
				$error = "Email non trouvé ou compte non administrateur";
			}

			$stmt->close();
		} catch (Exception $e) {
			$error = "Erreur de connexion: " . $e->getMessage();
		} finally {
			if (isset($conn)) {
				$conn->close();
			}
		}
	} else {
		$error = "Veuillez remplir tous les champs";
	}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login - Vogie</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		body {
			background: linear-gradient(135deg,
			min-height: 100vh;
			display: flex;
			align-items: center;
		}
		.login-card {
			background: white;
			border-radius: 20px;
			box-shadow: 0 20px 40px rgba(0,0,0,0.1);
			overflow: hidden;
		}
		.login-header {
			background: linear-gradient(135deg,
			color: white;
			padding: 2rem;
			text-align: center;
		}
		.login-body {
			padding: 2rem;
		}
		.form-control {
			border-radius: 10px;
			border: 2px solid
			padding: 0.75rem 1rem;
		}
		.form-control:focus {
			border-color:
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
		}
		.btn-login {
			background: linear-gradient(135deg,
			border: none;
			border-radius: 10px;
			padding: 0.75rem 2rem;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6 col-lg-4">
				<div class="login-card">
					<div class="login-header">
						<h3><i class="fas fa-shield-alt me-2"></i>VOGIE Admin</h3>
						<p class="mb-0">Connexion administrateur</p>
					</div>

					<div class="login-body">
						<?php if (isset($error)): ?>
							<div class="alert alert-danger"><?php echo $error; ?></div>
						<?php endif; ?>

						<form method="POST">
							<div class="mb-3">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="email" name="email"
									   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
							</div>

							<div class="mb-3">
								<label for="password" class="form-label">Mot de passe</label>
								<input type="password" class="form-control" id="password" name="password" required>
							</div>

							<button type="submit" class="btn btn-primary btn-login w-100">
								<i class="fas fa-sign-in-alt me-2"></i>Se connecter
							</button>
						</form>

						<div class="text-center mt-3">
							<a href="../index.php" class="text-muted">
								<i class="fas fa-arrow-left me-2"></i>Retour au site
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
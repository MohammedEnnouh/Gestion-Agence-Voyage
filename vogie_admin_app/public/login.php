<?php
session_start();
require __DIR__ . '/db.php';

if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
  header('Location: /Vogie2/public/index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin' AND is_active=1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && ($user['password'] === '' || password_verify($password, $user['password']))) {
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
      ];
      header('Location: /Vogie2/public/index.php');
      exit;
    } else {
      $error = "Identifiants invalides";
    }
  } catch (Throwable $e) {
    $error = 'Erreur: ' . htmlspecialchars($e->getMessage());
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion Admin - Vogie</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="/Vogie2/public/assets/styles.css" rel="stylesheet">
  <style>
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .login-container::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
      animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .login-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 20px;
      padding: 3rem;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      position: relative;
      z-index: 2;
      max-width: 400px;
      width: 100%;
      animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg,
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
      animation: float 3s ease-in-out infinite;
    }

    .login-title {
      color: white;
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 0.5rem;
      text-align: center;
    }

    .login-subtitle {
      color: rgba(255, 255, 255, 0.7);
      text-align: center;
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <div class="login-icon">
      <i class="fas fa-user-shield text-white" style="font-size: 2rem;"></i>
    </div>
    <h1 class="login-title">Connexion Admin</h1>
    <p class="login-subtitle">Accédez au tableau de bord</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="background: rgba(255, 154, 158, 0.2); border: 1px solid rgba(255, 154, 158, 0.3); color: #ff9a9e;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label for="email" class="form-label" style="color: rgba(255,255,255,0.8);">Email</label>
        <input type="email" class="form-control" id="email" name="email" required
               placeholder="admin@vogie.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <label for="password" class="form-label" style="color: rgba(255,255,255,0.8);">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password"
               placeholder="Laissez vide si pas de mot de passe">
      </div>
      <button type="submit" class="btn btn-primary w-100 py-3" style="font-size: 1.1rem;">
        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
      </button>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

  document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.style.transform = 'scale(1.02)';
        this.style.boxShadow = '0 0 20px rgba(255,255,255,0.3)';
      });

      input.addEventListener('blur', function() {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = 'none';
      });
    });

    const btn = document.querySelector('.btn-primary');
    btn.addEventListener('click', function() {
      this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion...';
    });
  });
</script>
</body>
</html>
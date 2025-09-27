<?php

class AuthController {
    private $conn;
    private $config;

    public function __construct($conn, $config) {
        $this->conn = $conn;
        $this->config = $config;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($data) {

        $errors = [];

        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        }

        if (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($errors)) {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param('s', $data['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = 'Email already registered';
            }
            $stmt->close();
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("
                INSERT INTO users (full_name, email, phone, password, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'user', 1, NOW())
            ");
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            $stmt->bind_param(
                'ssss',
                $fullName,
                $data['email'],
                $data['phone'],
                $hashedPassword
            );

            if ($stmt->execute()) {
                $userId = $stmt->insert_id;
                $stmt->close();

                $this->login($data['email'], $data['password']);

                return [
                    'success' => true,
                    'message' => 'Registration successful!',
                    'user_id' => $userId
                ];
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }

            $stmt->close();
        }

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("
            SELECT id, full_name, email, password, role
            FROM users
            WHERE email = ? AND is_active = 1
        ");

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            session_regenerate_id(true);

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }

    public function logout() {

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->conn->prepare("
            SELECT id, full_name, email, phone, role, is_active, created_at
            FROM users
            WHERE id = ?
        ");

        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function sendPasswordResetEmail($email) {

        $stmt = $this->conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return [
                'success' => true,
                'message' => 'If an account with that email exists, a password reset link has been sent.'
            ];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->conn->query("DELETE FROM password_resets WHERE email = '" . $this->conn->real_escape_string($email) . "'");

        $stmt = $this->conn->prepare("
            INSERT INTO password_resets (email, token, created_at)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param('sss', $email, $token, $expires);

        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' => 'Failed to generate reset token.'
            ];
        }

        $stmt->close();

        $resetLink = $this->config['app_url'] . '/reset-password.php?token=' . $token;
        $subject = 'Password Reset Request';
        $message = "
            <h2>Password Reset Request</h2>
            <p>Hello {$user['first_name']},</p>
            <p>You have requested to reset your password. Click the link below to set a new password:</p>
            <p><a href='{$resetLink}'>{$resetLink}</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $this->config['mail_from_address'] . "\r\n";

        return [
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.'
        ];
    }

    public function resetPassword($token, $password) {

        $stmt = $this->conn->prepare("
            SELECT email
            FROM password_resets
            WHERE token = ? AND created_at > NOW()
        ");

        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset = $result->fetch_assoc();
        $stmt->close();

        if (!$reset) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token.'
            ];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            UPDATE users
            SET password = ?, updated_at = NOW()
            WHERE email = ?
        ");

        $stmt->bind_param('ss', $hashedPassword, $reset['email']);

        if ($stmt->execute()) {

            $this->conn->query("DELETE FROM password_resets WHERE token = '" . $this->conn->real_escape_string($token) . "'");

            return [
                'success' => true,
                'message' => 'Password has been reset successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to reset password.'
        ];
    }

    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login.php');
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireAuth();

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            echo 'Access denied. Admin privileges required.';
            exit;
        }
    }
}
<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vogie_web');

define('STRIPE_PUBLIC_KEY', 'pk_test_51Pe5DrDW9NzzBWTtvTKSAGaCxmzOb1us4eve170c9zYIcknAh3I4HcQhnUhfyLk8kd1ML8u4qmOzX8Pzi0WHOfvn00ujylHqyb');
define('STRIPE_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxx');

define('BASE_URL', 'http://localhost/vogie/web');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token validation failed');
    }
    return true;
}

$config = [
    'app_name' => 'Vogie',
    'stripe_public_key' => defined('STRIPE_PUBLIC_KEY') ? STRIPE_PUBLIC_KEY : '',
    'stripe_secret_key' => defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '',
    'base_url' => defined('BASE_URL') ? BASE_URL : '',
];
?>
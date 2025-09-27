<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

$result = $auth->logout();

$_SESSION['flash_message'] = 'You have been successfully logged out.';
$_SESSION['flash_type'] = 'success';

header('Location: login.php');
exit;
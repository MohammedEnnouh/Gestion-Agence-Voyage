<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /Vogie2/public/login.php');
exit;
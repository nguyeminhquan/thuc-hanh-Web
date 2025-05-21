<?php
require_once 'auth.php';

// Clear session
session_unset();
session_destroy();

// Clear remember me cookie
setcookie('remember_me', '', time() - 3600, '/');

// Redirect to login page
header('Location: index.php');
exit;
?>
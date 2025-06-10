<?php
require_once 'config.php';

if (isLoggedIn()) {
    // Log do logout
    $security->logAction($_SESSION['user_id'], 'Logout realizado');
}

session_destroy();
header("Location: index.php");
exit();
?>

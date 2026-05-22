<?php
require_once 'config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect with query parameter
header("Location: login.php?logout=success");
exit();
?>
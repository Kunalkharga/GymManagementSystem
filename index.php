<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard/');
} else {
    redirect('login.php');
}
?>
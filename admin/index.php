<?php
require_once __DIR__ . '/../config/config.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/admin/login.php');
}
exit;

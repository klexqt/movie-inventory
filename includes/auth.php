<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUsername() {
    return $_SESSION['username'] ?? 'User';
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
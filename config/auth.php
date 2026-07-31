<?php
// config/auth.php - Session and role authorization helper functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdmin() {
    $role = $_SESSION['role'] ?? '';
    return ($role === 'Admin');
}

function canManagePosts() {
    $role = $_SESSION['role'] ?? '';
    return ($role === 'Admin' || $role === 'Editor' || $role === 'Author');
}

function currentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return getUserById((int)$_SESSION['user_id']);
}

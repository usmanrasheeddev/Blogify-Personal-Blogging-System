<?php
// index.php - Main Front Controller & Page Router

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/components.php';

// Parse query parameters
$page = $_GET['page'] ?? 'home';
$id = (int)($_GET['id'] ?? 0);
$pageNum = max(1, (int)($_GET['p'] ?? 1));
$search = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['cat'] ?? 0);
$perPage = 6;
$offset = ($pageNum - 1) * $perPage;

// Action Route Dispatcher
$actionRoutes = [
    'login_action'    => __DIR__ . '/actions/auth_actions.php',
    'register_action' => __DIR__ . '/actions/auth_actions.php',
    'logout'          => __DIR__ . '/actions/auth_actions.php',
    'add_action'      => __DIR__ . '/actions/post_actions.php',
    'edit_action'     => __DIR__ . '/actions/post_actions.php',
    'delete'          => __DIR__ . '/actions/post_actions.php',
    'comment_add'     => __DIR__ . '/actions/comment_actions.php',
    'comment_update'  => __DIR__ . '/actions/comment_actions.php',
    'comment_delete'  => __DIR__ . '/actions/comment_actions.php',
    'profile_update'  => __DIR__ . '/actions/profile_actions.php',
    'author_add'      => __DIR__ . '/actions/profile_actions.php',
    'author_delete'   => __DIR__ . '/actions/profile_actions.php',
];

if (isset($actionRoutes[$page])) {
    require_once $actionRoutes[$page];
    exit;
}

// View Route Dispatcher
$viewRoutes = [
    'home'     => __DIR__ . '/views/home.php',
    'view'     => __DIR__ . '/views/view_post.php',
    'add'      => __DIR__ . '/views/add_post.php',
    'edit'     => __DIR__ . '/views/edit_post.php',
    'profile'  => __DIR__ . '/views/profile.php',
    'admin'    => __DIR__ . '/views/profile.php',
    'user'     => __DIR__ . '/views/home.php',
    'login'    => __DIR__ . '/views/login.php',
    'register' => __DIR__ . '/views/register.php',
];

if (isset($viewRoutes[$page])) {
    require_once $viewRoutes[$page];
    exit;
}

// Fallback to Home View
require_once __DIR__ . '/views/home.php';

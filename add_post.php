<?php
// add_post.php - Compatibility bridge for legacy direct URL access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_GET['page'] = 'add_action';
    require_once __DIR__ . '/index.php';
} else {
    header("Location: index.php?page=add");
    exit;
}

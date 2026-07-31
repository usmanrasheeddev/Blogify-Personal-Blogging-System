<?php
// includes/header.php - Global HTML Head and Navigation Bar

if (!isset($pageTitle)) {
    $pageTitle = 'Blogify – Personal Blogging System';
}
$user = currentUser();
$currentPage = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body>
<div class="container">
    <nav class="navbar">
        <a href="index.php?page=home" class="nav-brand">
            <span class="brand-icon">✨</span>
            <span class="brand-name">Blogify</span>
        </a>
        
        <div class="nav-links">
            <a href="index.php?page=home" class="<?php echo $currentPage === 'home' ? 'active' : ''; ?>">Home</a>
            
            <?php if (canManagePosts()): ?>
                <a href="index.php?page=add" class="<?php echo $currentPage === 'add' ? 'active' : ''; ?>">+ Add Post</a>
            <?php endif; ?>
            
            <?php if ($user): ?>
                <a href="index.php?page=profile" class="<?php echo $currentPage === 'profile' ? 'active' : ''; ?>">Dashboard</a>
                <span class="nav-user-badge">
                    <span class="dot"></span>
                    <?php echo e($user['name']); ?>
                </span>
                <a href="index.php?page=logout" class="nav-logout" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login" class="<?php echo $currentPage === 'login' ? 'active' : ''; ?>">Login</a>
                <a href="index.php?page=register" class="btn-nav-primary <?php echo $currentPage === 'register' ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main-content">

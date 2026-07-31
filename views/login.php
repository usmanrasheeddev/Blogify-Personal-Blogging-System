<?php
// views/login.php - Login view template

if (currentUser()) {
    header("Location: index.php?page=home");
    exit;
}

$pageTitle = 'Login - Blogify';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card">
        <div class="auth-header">
            <div class="auth-icon">🔑</div>
            <h2>Welcome Back</h2>
            <p class="muted">Enter your credentials to access your Blogify account</p>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Admin or admin@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn-primary btn-full">Sign In →</button>
        </form>

        <div id="auth-message" class="auth-message-box"></div>

        <div class="auth-footer">
            <p>Don't have an account? <a href="index.php?page=register">Create an account</a></p>
            <div class="demo-creds-hint card">
                <p><strong>Demo Login Credentials:</strong></p>
                <p>Admin: <code>Admin</code> / Password: <code>1234</code></p>
                <p>Author: <code>Author</code> / Password: <code>1234</code></p>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

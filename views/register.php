<?php
// views/register.php - User Registration view template

if (currentUser()) {
    header("Location: index.php?page=home");
    exit;
}

$pageTitle = 'Register - Blogify';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card">
        <div class="auth-header">
            <div class="auth-icon">🚀</div>
            <h2>Join Blogify Today</h2>
            <p class="muted">Create your free account to start writing & participating</p>
        </div>

        <form id="registerForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create password" required>
                </div>
                <div class="form-group flex-1">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-full">Create Free Account →</button>
        </form>

        <div id="auth-message" class="auth-message-box"></div>

        <div class="auth-footer">
            <p>Already have an account? <a href="index.php?page=login">Sign in here</a></p>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

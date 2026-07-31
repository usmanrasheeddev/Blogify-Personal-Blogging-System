<?php
// views/edit_post.php - Edit existing post view

if (!canManagePosts()) {
    header("Location: index.php?page=login");
    exit;
}

$post = ($id > 0) ? getPostById($id) : null;
$error = $_GET['error'] ?? '';

$pageTitle = $post ? 'Edit Post - ' . e($post['title']) : 'Edit Post - Blogify';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php?page=view&id=<?php echo $id; ?>">← Back to Post</a>
</div>

<div class="form-container card">
    <div class="form-header">
        <h2>📝 Edit Blog Post</h2>
        <p class="muted">Update the title and content of your article.</p>
    </div>

    <?php if (!$post): ?>
        <div class="alert alert-error">Post not found or has been removed.</div>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">⚠️ <?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=edit_action&id=<?php echo $id; ?>">
            <div class="form-group">
                <label>Post Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo e($post['title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Article Content</label>
                <textarea name="content" class="form-control" rows="10" required><?php echo e($post['content']); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?page=view&id=<?php echo $id; ?>" class="btn-secondary">Cancel</a>
                <button type="submit" name="update_post" class="btn-primary">Update Article →</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

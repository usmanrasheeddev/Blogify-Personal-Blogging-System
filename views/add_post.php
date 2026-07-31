<?php
// views/add_post.php - Add new blog post form view

if (!canManagePosts()) {
    header("Location: index.php?page=login");
    exit;
}

$users = getUsers();
$categories = getCategories();
$current = currentUser();
$error = $_GET['error'] ?? '';

$pageTitle = 'Create New Post - Blogify';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php?page=home">← Back to Articles</a>
</div>

<div class="form-container card">
    <div class="form-header">
        <h2>✏️ Create New Post</h2>
        <p class="muted">Share a new story, tutorial, or article with the Blogify community.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">⚠️ <?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=add_action" enctype="multipart/form-data">
        <?php if (isAdmin()): ?>
            <div class="form-group">
                <label>Select Author</label>
                <select name="user" class="form-control" required>
                    <?php if ($users): while ($u = my_db_fetch_assoc($users)): ?>
                        <option value="<?php echo (int)$u['id']; ?>" <?php echo ((int)($current['id'] ?? 0) === (int)$u['id']) ? 'selected' : ''; ?>>
                            <?php echo e($u['name']); ?> (<?php echo e($u['role']); ?>)
                        </option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="user" value="<?php echo (int)($current['id'] ?? 0); ?>">
            <div class="posting-badge">
                Posting as: <strong><?php echo e($current['name'] ?? 'Author'); ?></strong>
            </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group flex-1">
                <label>Category</label>
                <select name="category" class="form-control" required>
                    <?php if ($categories): my_db_data_seek($categories, 0); while ($c = my_db_fetch_assoc($categories)): ?>
                        <option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>

            <div class="form-group flex-1">
                <label>Cover Image (Optional)</label>
                <input type="file" name="image" class="form-control-file" accept="image/*">
            </div>
        </div>

        <div class="form-group">
            <label>Post Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter post title..." required>
        </div>

        <div class="form-group">
            <label>Article Content</label>
            <textarea name="content" class="form-control" rows="10" placeholder="Write your full article here..." required></textarea>
        </div>

        <div class="form-actions">
            <a href="index.php?page=home" class="btn-secondary">Cancel</a>
            <button type="submit" name="add_post" class="btn-primary">Publish Post →</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

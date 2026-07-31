<?php
// views/view_post.php - Detailed post view with comments thread

$post = ($id > 0) ? getPostDetail($id) : null;
$commentError = $_GET['comment_error'] ?? '';
$commentEditId = (int)($_GET['comment_edit_id'] ?? 0);

$pageTitle = $post ? e($post['title']) . ' - Blogify' : 'Post Not Found - Blogify';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php?page=home">← Back to Articles</a>
</div>

<?php if (!$post): ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Post Not Found</h3>
        <p>The requested blog post does not exist or has been removed.</p>
        <a href="index.php?page=home" class="btn-primary">Return Home</a>
    </div>
<?php else: ?>
    <article class="single-post-container">
        <?php renderPostCard($post, false, true, false); ?>
    </article>

    <!-- Comments Section -->
    <section class="comments-section card">
        <div class="comments-header">
            <h3>💬 Discussion & Comments</h3>
        </div>

        <?php
        $comments = getCommentsByPostId($id);
        $editComment = ($commentEditId > 0) ? getCommentById($commentEditId) : null;
        ?>

        <?php if ($comments && my_db_num_rows($comments) === 0): ?>
            <p class="no-comments">No comments yet. Be the first to share your thoughts!</p>
        <?php elseif ($comments): ?>
            <div class="comments-list">
                <?php while ($c = my_db_fetch_assoc($comments)): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <strong class="comment-author"><?php echo e($c['author']); ?></strong>
                            <?php if (!empty($c['created_at'])): ?>
                                <span class="comment-date"><?php echo e(date('M j, Y g:i a', strtotime($c['created_at']))); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="comment-text"><?php echo nl2br(e($c['comment'])); ?></p>
                        
                        <?php if (isAdmin()): ?>
                            <div class="comment-actions">
                                <a href="index.php?page=view&id=<?php echo $id; ?>&comment_edit_id=<?php echo (int)$c['id']; ?>" class="btn-link">Edit</a>
                                <a href="index.php?page=comment_delete&comment_id=<?php echo (int)$c['id']; ?>&post_id=<?php echo $id; ?>" class="btn-link text-danger" onclick="return confirm('Delete this comment?');">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Edit Comment Modal/Box for Admin -->
        <?php if (isAdmin() && $editComment): ?>
            <div class="edit-comment-box card">
                <h4>Edit Comment</h4>
                <form method="post" action="index.php?page=comment_update&comment_id=<?php echo (int)$editComment['id']; ?>&post_id=<?php echo $id; ?>">
                    <div class="form-group">
                        <textarea name="comment" required><?php echo e($editComment['comment']); ?></textarea>
                    </div>
                    <button class="btn-primary">Update Comment</button>
                    <a href="index.php?page=view&id=<?php echo $id; ?>" class="btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Add Comment Form -->
        <div class="add-comment-box">
            <h4>Leave a Comment</h4>
            <?php if (!empty($commentError)): ?>
                <div class="alert alert-error">⚠️ <?php echo e($commentError); ?></div>
            <?php endif; ?>

            <?php if (currentUser()): ?>
                <form method="post" action="index.php?page=comment_add&id=<?php echo $id; ?>">
                    <div class="form-group">
                        <textarea name="comment" placeholder="Write a constructive comment..." required></textarea>
                    </div>
                    <button type="submit" name="add_comment" class="btn-primary">Post Comment →</button>
                </form>
            <?php else: ?>
                <div class="login-prompt card">
                    <p>Please <a href="index.php?page=login">sign in to your account</a> to participate in the conversation.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

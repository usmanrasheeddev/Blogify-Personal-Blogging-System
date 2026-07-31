<?php
// views/profile.php - Author & Admin dashboard view

$user = currentUser();
if (!$user) {
    header("Location: index.php?page=login");
    exit;
}

$myPosts = countPostsByUser((int)$user['id']);
$myComments = countCommentsByUser((int)$user['id']);
$commentsOnMyPosts = getCommentsOnUserPosts((int)$user['id']);

$pageTitle = 'Dashboard - ' . e($user['name']);
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="dashboard-container">
    <!-- User Profile Banner -->
    <div class="profile-header-card card">
        <div class="profile-header-inner">
            <div class="avatar-wrap">
                <?php if (!empty($user['profile_image'])): ?>
                    <img class="profile-avatar" src="uploads/profiles/<?php echo e($user['profile_image']); ?>" alt="<?php echo e($user['name']); ?>">
                <?php else: ?>
                    <div class="profile-avatar placeholder"><?php echo e(strtoupper(substr($user['name'], 0, 1))); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="profile-details">
                <div class="profile-title-row">
                    <h2><?php echo e($user['name']); ?></h2>
                    <span class="role-badge role-<?php echo strtolower($user['role']); ?>"><?php echo e($user['role']); ?></span>
                </div>
                <?php if (!empty($user['email'])): ?>
                    <p class="profile-email">📧 <?php echo e($user['email']); ?></p>
                <?php endif; ?>
                <?php if (!empty($user['profile'])): ?>
                    <p class="profile-bio"><?php echo nl2br(e($user['profile'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overview Stats Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-body">
                <div class="stat-value"><?php echo $myPosts; ?></div>
                <div class="stat-label">My Published Posts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-body">
                <div class="stat-value"><?php echo $myComments; ?></div>
                <div class="stat-label">Comments Written</div>
            </div>
        </div>
        <?php if (isAdmin()): ?>
            <div class="stat-card">
                <div class="stat-icon">🌐</div>
                <div class="stat-body">
                    <div class="stat-value"><?php echo countAllPosts(); ?></div>
                    <div class="stat-label">Total System Posts</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-body">
                    <div class="stat-value"><?php echo countAllUsers(); ?></div>
                    <div class="stat-label">Total Registered Users</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Update Profile Accordion -->
    <details class="collapsible-card card">
        <summary class="collapsible-summary">⚙️ Update Profile Settings</summary>
        <div class="collapsible-body">
            <form method="post" action="index.php?page=profile_update" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($user['name']); ?>" required>
                    </div>
                    <div class="form-group flex-1">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo e($user['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Short Bio</label>
                    <textarea name="profile" class="form-control" rows="3" placeholder="Tell readers about yourself..."><?php echo e($user['profile'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Profile Avatar</label>
                    <input type="file" name="profile_image" class="form-control-file" accept="image/*">
                </div>

                <button type="submit" class="btn-primary">Save Profile Changes</button>
            </form>
        </div>
    </details>

    <!-- Admin Panel: Author Management & All Posts Management -->
    <?php if (isAdmin()): ?>
        <section class="admin-section card">
            <h3>👥 Manage Authors</h3>
            <form method="post" action="index.php?page=author_add" class="author-add-form">
                <div class="form-row">
                    <div class="form-group flex-1">
                        <input type="text" name="name" class="form-control" placeholder="Author Full Name" required>
                    </div>
                    <div class="form-group flex-1">
                        <input type="email" name="email" class="form-control" placeholder="Author Email" required>
                    </div>
                    <div class="form-group flex-1">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn-primary">+ Add Author</button>
                </div>
            </form>

            <?php $authors = getAuthors(); ?>
            <?php if ($authors && my_db_num_rows($authors) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($a = my_db_fetch_assoc($authors)): ?>
                                <tr>
                                    <td><strong><?php echo e($a['name']); ?></strong></td>
                                    <td><?php echo e($a['email'] ?? '-'); ?></td>
                                    <td><span class="role-badge role-author"><?php echo e($a['role']); ?></span></td>
                                    <td>
                                        <a href="index.php?page=author_delete&id=<?php echo (int)$a['id']; ?>" class="btn-link text-danger" onclick="return confirm('Remove this author and their content?');">Remove</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted">No authors registered yet.</p>
            <?php endif; ?>
        </section>

        <section class="admin-section card">
            <h3>📂 System Posts Management</h3>
            <?php $adminPosts = getPosts(); ?>
            <?php if ($adminPosts && my_db_num_rows($adminPosts) > 0): ?>
                <div class="post-grid manage-posts-grid">
                    <?php while ($p = my_db_fetch_assoc($adminPosts)): ?>
                        <div class="card manage-post-card">
                            <h4><?php echo e($p['title']); ?></h4>
                            <p class="muted">By <?php echo e($p['author']); ?> | <?php echo e($p['category']); ?></p>
                            <div class="manage-post-actions">
                                <a href="index.php?page=view&id=<?php echo (int)$p['id']; ?>" class="btn-sm btn-outline">View</a>
                                <a href="index.php?page=edit&id=<?php echo (int)$p['id']; ?>" class="btn-sm btn-secondary">Edit</a>
                                <a href="index.php?page=delete&id=<?php echo (int)$p['id']; ?>" class="btn-sm btn-danger" onclick="return confirm('Delete this post?');">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="muted">No posts available in system.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- Author Section: Comments on User Posts -->
    <?php if (canManagePosts() && !isAdmin()): ?>
        <section class="author-comments-section card">
            <h3>💬 Recent Comments on Your Posts</h3>
            <?php if ($commentsOnMyPosts && my_db_num_rows($commentsOnMyPosts) > 0): ?>
                <div class="comments-list">
                    <?php while ($c = my_db_fetch_assoc($commentsOnMyPosts)): ?>
                        <div class="comment-item">
                            <p><strong><?php echo e($c['commenter']); ?></strong> on <em>"<?php echo e($c['post_title']); ?>"</em>:</p>
                            <p class="comment-text"><?php echo e($c['comment']); ?></p>
                            <?php if (!empty($c['created_at'])): ?>
                                <small class="muted"><?php echo e($c['created_at']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="muted">No comments have been posted on your articles yet.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<?php
// views/home.php - Homepage view featuring post feed, filters, and pagination

$categories = getCategories();
$posts = getPostsFiltered($search, $categoryId, $perPage, $offset);
$total = countPostsFiltered($search, $categoryId);
$totalPages = (int)ceil($total / $perPage);

$pageTitle = 'Blogify – Personal Blogging System';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div class="header-text">
        <h1 class="page-title">Explore Articles</h1>
        <p class="page-subtitle">Discover perspectives, tutorials, and stories from our community.</p>
    </div>
</div>

<?php renderFilterForm($categoryId, $categories, 'home', $search); ?>

<?php if ($posts === false): ?>
    <div class="alert alert-error">Query Error: <?php echo e(my_db_error($conn)); ?></div>
<?php elseif (my_db_num_rows($posts) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>No Posts Found</h3>
        <p>No blog posts matched your criteria. Try adjusting your search query or category filter.</p>
    </div>
<?php else: ?>
    <div class="post-grid">
        <?php while ($p = my_db_fetch_assoc($posts)): ?>
            <?php renderPostCard($p, false, true, true); ?>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php renderPagination($pageNum, $totalPages, ['page' => 'home', 'q' => $search, 'cat' => $categoryId]); ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

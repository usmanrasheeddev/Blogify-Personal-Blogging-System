<?php
// includes/components.php - Modular UI component functions

/**
 * Renders a post card component.
 */
function renderPostCard($post, $showAdminActions = false, $showContent = true, $showViewLink = true) {
    echo "<article class=\"post-card\">";
    
    if (!empty($post['image'])) {
        $imgSrc = rawurlencode($post['image']);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $publicSrc = $basePath . '/uploads/' . $imgSrc;
        $diskPath = dirname(__DIR__) . '/uploads/' . $post['image'];
        
        echo "<div class=\"post-card-image-wrap\">";
        echo "<img class=\"post-image\" src=\"" . e($publicSrc) . "\" alt=\"" . e($post['title']) . "\" loading=\"lazy\">";
        if (!file_exists($diskPath) && !getenv('VERCEL')) {
            echo "<small class=\"image-warning\">Image missing</small>";
        }
        echo "</div>";
    }
    
    echo "<div class=\"post-card-body\">";
    echo "<div class=\"post-meta-top\">";
    echo "<span class=\"category-tag\">" . e($post['category']) . "</span>";
    if (!empty($post['created_at'])) {
        $formattedDate = date('M j, Y', strtotime($post['created_at']));
        echo "<span class=\"post-date\">" . e($formattedDate) . "</span>";
    }
    echo "</div>";
    
    echo "<h3 class=\"post-title\"><a href=\"index.php?page=view&id=" . (int)$post['id'] . "\">" . e($post['title']) . "</a></h3>";
    echo "<p class=\"post-author\">By <strong>" . e($post['author']) . "</strong></p>";
    
    if ($showContent) {
        $excerpt = nl2br(e($post['content']));
        echo "<div class=\"post-excerpt\">" . $excerpt . "</div>";
    }
    
    echo "<div class=\"post-card-actions\">";
    if ($showAdminActions) {
        echo "<div class=\"admin-actions\">";
        echo "<a class=\"btn-sm btn-outline\" href=\"index.php?page=view&id=" . (int)$post['id'] . "\">💬 Comments</a>";
        echo "<a class=\"btn-sm btn-secondary\" href=\"index.php?page=edit&id=" . (int)$post['id'] . "\">✏️ Edit</a>";
        echo "<a class=\"btn-sm btn-danger\" href=\"index.php?page=delete&id=" . (int)$post['id'] . "\" onclick=\"return confirm('Delete this post?');\">🗑️ Delete</a>";
        echo "</div>";
    } elseif ($showViewLink) {
        echo "<a class=\"btn-sm btn-primary\" href=\"index.php?page=view&id=" . (int)$post['id'] . "\">Read & Comments →</a>";
    }
    echo "</div>";
    
    echo "</div>";
    echo "</article>";
}

/**
 * Renders category and search filter bar.
 */
function renderFilterForm($categoryId, $categories, $page, $search = '') {
    echo "<form class=\"filter-bar\" method=\"get\" action=\"index.php\">";
    echo "<input type=\"hidden\" name=\"page\" value=\"" . e($page) . "\">";
    
    echo "<div class=\"search-input-wrap\">";
    echo "<span class=\"search-icon\">🔍</span>";
    echo "<input type=\"text\" name=\"q\" class=\"search-input\" placeholder=\"Search posts...\" value=\"" . e($search) . "\">";
    echo "</div>";
    
    echo "<select name=\"cat\" class=\"category-select\">";
    echo "<option value=\"0\">All Categories</option>";
    if ($categories) {
        my_db_data_seek($categories, 0);
        while ($c = my_db_fetch_assoc($categories)) {
            $selected = ((int)$c['id'] === $categoryId) ? 'selected' : '';
            echo "<option value=\"" . (int)$c['id'] . "\" {$selected}>" . e($c['name']) . "</option>";
        }
    }
    echo "</select>";
    
    echo "<button type=\"submit\" class=\"btn-filter\">Apply Filter</button>";
    if (!empty($search) || $categoryId > 0) {
        echo "<a href=\"index.php?page=" . e($page) . "\" class=\"btn-reset\">Reset</a>";
    }
    echo "</form>";
}

/**
 * Renders pagination controls.
 */
function renderPagination($page, $totalPages, $baseParams) {
    if ($totalPages <= 1) {
        return;
    }
    echo "<nav class=\"pagination\">";
    if ($page > 1) {
        $prev = $page - 1;
        echo "<a class=\"page-link prev\" href=\"?" . http_build_query(array_merge($baseParams, ['p' => $prev])) . "\">← Prev</a>";
    }
    echo "<span class=\"page-info\">Page <strong>{$page}</strong> of <strong>{$totalPages}</strong></span>";
    if ($page < $totalPages) {
        $next = $page + 1;
        echo "<a class=\"page-link next\" href=\"?" . http_build_query(array_merge($baseParams, ['p' => $next])) . "\">Next →</a>";
    }
    echo "</nav>";
}

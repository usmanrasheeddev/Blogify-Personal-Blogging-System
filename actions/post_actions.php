<?php
// actions/post_actions.php - Handlers for post creation, editing, and deletion

$action = $_GET['page'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'add_action') {
    if (!canManagePosts()) {
        header("Location: index.php?page=login");
        exit;
    }

    $current = currentUser();
    $userId = isAdmin() ? (int)($_POST['user'] ?? 0) : (int)($current['id'] ?? 0);
    $categoryId = (int)($_POST['category'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $imageName = '';
    $uploadDir = dirname(__DIR__) . '/uploads/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image']['name']));
            $imageName = time() . '_' . $baseName;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if ($userId <= 0 || $categoryId <= 0 || empty($title) || empty($content)) {
        header("Location: index.php?page=add&error=" . urlencode('All fields are required.'));
        exit;
    }

    $stmt = my_db_prepare($conn, "INSERT INTO posts(user_id, category_id, title, content, image) VALUES(?,?,?,?,?)");
    my_db_stmt_bind_param($stmt, "iisss", $userId, $categoryId, $title, $content, $imageName);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);

    header("Location: index.php?page=home");
    exit;
}

if ($action === 'edit_action' && $id > 0) {
    if (!canManagePosts()) {
        header("Location: index.php?page=login");
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        header("Location: index.php?page=edit&id={$id}&error=" . urlencode('Title and content are required.'));
        exit;
    }

    $stmt = my_db_prepare($conn, "UPDATE posts SET title = ?, content = ? WHERE id = ?");
    my_db_stmt_bind_param($stmt, "ssi", $title, $content, $id);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);

    header("Location: index.php?page=view&id={$id}");
    exit;
}

if ($action === 'delete' && $id > 0) {
    if (!canManagePosts()) {
        header("Location: index.php?page=login");
        exit;
    }

    // Delete associated comments first
    $stmtComments = my_db_prepare($conn, "DELETE FROM comments WHERE post_id = ?");
    my_db_stmt_bind_param($stmtComments, "i", $id);
    my_db_stmt_execute($stmtComments);
    my_db_stmt_close($stmtComments);

    // Delete post
    $stmt = my_db_prepare($conn, "DELETE FROM posts WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $id);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);

    header("Location: index.php?page=home");
    exit;
}

<?php
// actions/comment_actions.php - Handlers for adding, editing, and deleting comments

$action = $_GET['page'] ?? '';

if ($action === 'comment_add') {
    $postId = (int)($_GET['id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $user = currentUser();

    if (!$user) {
        header("Location: index.php?page=login");
        exit;
    }

    if ($postId <= 0 || empty($comment)) {
        header("Location: index.php?page=view&id={$postId}&comment_error=" . urlencode('Please write a comment.'));
        exit;
    }

    addComment($postId, (int)$user['id'], $comment);
    header("Location: index.php?page=view&id={$postId}");
    exit;
}

if ($action === 'comment_update') {
    if (!isAdmin()) {
        header("Location: index.php?page=login");
        exit;
    }
    $commentId = (int)($_GET['comment_id'] ?? 0);
    $postId = (int)($_GET['post_id'] ?? 0);
    $commentText = trim($_POST['comment'] ?? '');

    if ($commentId > 0 && !empty($commentText)) {
        updateComment($commentId, $commentText);
    }

    header("Location: index.php?page=view&id={$postId}");
    exit;
}

if ($action === 'comment_delete') {
    if (!isAdmin()) {
        header("Location: index.php?page=login");
        exit;
    }
    $commentId = (int)($_GET['comment_id'] ?? 0);
    $postId = (int)($_GET['post_id'] ?? 0);

    if ($commentId > 0) {
        deleteComment($commentId);
    }

    header("Location: index.php?page=view&id={$postId}");
    exit;
}

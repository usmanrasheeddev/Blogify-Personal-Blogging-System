<?php
// includes/functions.php - Business logic, repository functions, and data access

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/auth.php';

/**
 * Escapes HTML characters for safe output.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Checks whether a column exists in a given table.
 */
function columnExists($table, $column) {
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $columnEscaped = my_db_real_escape_string($conn, $column);
    $res = my_db_query($conn, "SHOW COLUMNS FROM `{$table}` LIKE '{$columnEscaped}'");
    return ($res && my_db_num_rows($res) > 0);
}

function getUsers() {
    global $conn;
    return my_db_query($conn, "SELECT * FROM users ORDER BY id ASC");
}

function getAuthors() {
    global $conn;
    return my_db_query($conn, "SELECT * FROM users WHERE role = 'Author' ORDER BY id DESC");
}

function getCategories() {
    global $conn;
    return my_db_query($conn, "SELECT * FROM categories ORDER BY id ASC");
}

function getPosts() {
    global $conn;
    $imageField = columnExists('posts', 'image') ? 'posts.image' : 'NULL AS image';
    $dateField = columnExists('posts', 'created_at') ? 'posts.created_at' : 'NULL AS created_at';
    return my_db_query($conn, "
        SELECT posts.id, posts.title, posts.content, {$imageField}, {$dateField},
               users.name AS author, categories.name AS category
        FROM posts
        JOIN users ON posts.user_id = users.id
        JOIN categories ON posts.category_id = categories.id
        ORDER BY posts.id DESC
    ");
}

function getPostById($id) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT * FROM posts WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $id);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function getPostDetail($id) {
    global $conn;
    $imageField = columnExists('posts', 'image') ? 'posts.image' : 'NULL AS image';
    $dateField = columnExists('posts', 'created_at') ? 'posts.created_at' : 'NULL AS created_at';
    $stmt = my_db_prepare($conn, "
        SELECT posts.id, posts.title, posts.content, {$imageField}, {$dateField},
               users.name AS author, categories.name AS category
        FROM posts
        JOIN users ON posts.user_id = users.id
        JOIN categories ON posts.category_id = categories.id
        WHERE posts.id = ?
    ");
    my_db_stmt_bind_param($stmt, "i", $id);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function getCommentsByPostId($postId) {
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    $stmt = my_db_prepare($conn, "
        SELECT comments.id, comments.comment, {$dateField}, users.name AS author
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.id DESC
    ");
    my_db_stmt_bind_param($stmt, "i", $postId);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    my_db_stmt_close($stmt);
    return $res;
}

function addComment($postId, $userId, $comment) {
    global $conn;
    $stmt = my_db_prepare($conn, "INSERT INTO comments(post_id, user_id, comment) VALUES(?,?,?)");
    my_db_stmt_bind_param($stmt, "iis", $postId, $userId, $comment);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);
}

function getPostOwnerId($postId) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT user_id FROM posts WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $postId);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return (int)($row['user_id'] ?? 0);
}

function getPostsFiltered($search, $categoryId, $limit, $offset) {
    global $conn;
    $imageField = columnExists('posts', 'image') ? 'posts.image' : 'NULL AS image';
    $dateField = columnExists('posts', 'created_at') ? 'posts.created_at' : 'NULL AS created_at';
    $sql = "
        SELECT posts.id, posts.title, posts.content, {$imageField}, {$dateField},
               users.name AS author, categories.name AS category
        FROM posts
        JOIN users ON posts.user_id = users.id
        JOIN categories ON posts.category_id = categories.id
        WHERE 1=1
    ";

    $types = '';
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (posts.title LIKE ? OR posts.content LIKE ?) ";
        $types .= 'ss';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if ($categoryId > 0) {
        $sql .= " AND categories.id = ? ";
        $types .= 'i';
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY posts.id DESC LIMIT ? OFFSET ? ";
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = my_db_prepare($conn, $sql);
    if (!$stmt) {
        return getPosts();
    }
    my_db_stmt_bind_param($stmt, $types, ...$params);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    my_db_stmt_close($stmt);
    return $res;
}

function countPostsFiltered($search, $categoryId) {
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM posts JOIN categories ON posts.category_id = categories.id WHERE 1=1";

    $types = '';
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (posts.title LIKE ? OR posts.content LIKE ?) ";
        $types .= 'ss';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if ($categoryId > 0) {
        $sql .= " AND categories.id = ? ";
        $types .= 'i';
        $params[] = $categoryId;
    }

    $stmt = my_db_prepare($conn, $sql);
    if (!$stmt) {
        return 0;
    }
    if ($types !== '') {
        my_db_stmt_bind_param($stmt, $types, ...$params);
    }
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}

function getCommentById($id) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT * FROM comments WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $id);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function updateComment($commentId, $comment) {
    global $conn;
    $stmt = my_db_prepare($conn, "UPDATE comments SET comment = ? WHERE id = ?");
    my_db_stmt_bind_param($stmt, "si", $comment, $commentId);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);
}

function deleteComment($commentId) {
    global $conn;
    $stmt = my_db_prepare($conn, "DELETE FROM comments WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $commentId);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);
}

function getUserById($id) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT * FROM users WHERE id = ?");
    my_db_stmt_bind_param($stmt, "i", $id);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function countPostsByUser($userId) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT COUNT(*) AS total FROM posts WHERE user_id = ?");
    my_db_stmt_bind_param($stmt, "i", $userId);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}

function countCommentsByUser($userId) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT COUNT(*) AS total FROM comments WHERE user_id = ?");
    my_db_stmt_bind_param($stmt, "i", $userId);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}

function countAllPosts() {
    global $conn;
    $res = my_db_query($conn, "SELECT COUNT(*) AS total FROM posts");
    $row = my_db_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function countAllComments() {
    global $conn;
    $res = my_db_query($conn, "SELECT COUNT(*) AS total FROM comments");
    $row = my_db_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function countAllUsers() {
    global $conn;
    $res = my_db_query($conn, "SELECT COUNT(*) AS total FROM users");
    $row = my_db_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function getCommentsOnUserPosts($userId) {
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    $stmt = my_db_prepare($conn, "
        SELECT comments.comment, {$dateField}, users.name AS commenter, posts.title AS post_title
        FROM comments
        JOIN posts ON comments.post_id = posts.id
        JOIN users ON comments.user_id = users.id
        WHERE posts.user_id = ?
        ORDER BY comments.id DESC
    ");
    if (!$stmt) return false;
    my_db_stmt_bind_param($stmt, "i", $userId);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    my_db_stmt_close($stmt);
    return $res;
}

function getAllCommentsWithPosts() {
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    return my_db_query($conn, "
        SELECT comments.comment, {$dateField} AS created_at, users.name AS commenter, posts.title AS post_title
        FROM comments
        JOIN posts ON comments.post_id = posts.id
        JOIN users ON comments.user_id = users.id
        ORDER BY comments.id DESC
    ");
}

function getUserByName($name) {
    global $conn;
    $stmt = my_db_prepare($conn, "SELECT * FROM users WHERE name = ?");
    my_db_stmt_bind_param($stmt, "s", $name);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function getUserByEmail($email) {
    global $conn;
    if (!columnExists('users', 'email')) return null;
    $stmt = my_db_prepare($conn, "SELECT * FROM users WHERE email = ?");
    my_db_stmt_bind_param($stmt, "s", $email);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function getUserByUserEmail($email) {
    global $conn;
    if (!columnExists('users', 'user_emails')) return null;
    $stmt = my_db_prepare($conn, "SELECT * FROM users WHERE user_emails = ?");
    my_db_stmt_bind_param($stmt, "s", $email);
    my_db_stmt_execute($stmt);
    $res = my_db_stmt_get_result($stmt);
    $row = my_db_fetch_assoc($res);
    my_db_stmt_close($stmt);
    return $row;
}

function createUser($name, $email, $role, $password, $profileImage = '') {
    global $conn;
    
    // Hash password securely if it's not already hashed
    if (!empty($password) && strlen($password) < 60) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $passwordHash = $password;
    }

    $hasEmail = columnExists('users', 'email');
    $hasUserEmails = columnExists('users', 'user_emails');
    $hasPassword = columnExists('users', 'password');
    $hasImage = columnExists('users', 'profile_image');
    $hasProfile = columnExists('users', 'profile');

    if ($hasEmail && $hasUserEmails && $hasPassword && $hasImage && $hasProfile) {
        $stmt = my_db_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password, profile_image, profile) VALUES(?,?,?,?,?,?,?)");
        $profileText = '';
        my_db_stmt_bind_param($stmt, "sssssss", $name, $role, $email, $email, $passwordHash, $profileImage, $profileText);
    } elseif ($hasEmail && $hasUserEmails && $hasPassword && $hasImage) {
        $stmt = my_db_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password, profile_image) VALUES(?,?,?,?,?,?)");
        my_db_stmt_bind_param($stmt, "ssssss", $name, $role, $email, $email, $passwordHash, $profileImage);
    } elseif ($hasEmail && $hasUserEmails && $hasPassword) {
        $stmt = my_db_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password) VALUES(?,?,?,?,?)");
        my_db_stmt_bind_param($stmt, "sssss", $name, $role, $email, $email, $passwordHash);
    } elseif ($hasEmail && $hasPassword) {
        $stmt = my_db_prepare($conn, "INSERT INTO users(name, role, email, password) VALUES(?,?,?,?)");
        my_db_stmt_bind_param($stmt, "ssss", $name, $role, $email, $passwordHash);
    } else {
        $stmt = my_db_prepare($conn, "INSERT INTO users(name, role) VALUES(?,?)");
        my_db_stmt_bind_param($stmt, "ss", $name, $role);
    }
    
    $ok = my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);
    return $ok;
}

function deleteUserAndContent($userId) {
    global $conn;
    $userId = (int)$userId;
    if ($userId <= 0) return false;

    $postIds = [];
    $res = my_db_query($conn, "SELECT id FROM posts WHERE user_id = {$userId}");
    if ($res) {
        while ($row = my_db_fetch_assoc($res)) {
            $postIds[] = (int)$row['id'];
        }
    }

    if (count($postIds) > 0) {
        $in = implode(',', $postIds);
        my_db_query($conn, "DELETE FROM comments WHERE post_id IN ({$in})");
        my_db_query($conn, "DELETE FROM posts WHERE id IN ({$in})");
    }

    my_db_query($conn, "DELETE FROM comments WHERE user_id = {$userId}");
    my_db_query($conn, "DELETE FROM users WHERE id = {$userId}");
    return true;
}

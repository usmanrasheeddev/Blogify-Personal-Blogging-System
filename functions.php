<?php
include 'db.php';

function e($value){
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function columnExists($table, $column){
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $columnEscaped = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` LIKE '{$columnEscaped}'");
    return ($res && mysqli_num_rows($res) > 0);
}

function getUsers(){
    global $conn;
    return mysqli_query($conn,"SELECT * FROM users");
}

function getAuthors(){
    global $conn;
    return mysqli_query($conn, "SELECT * FROM users WHERE role = 'Author' ORDER BY id DESC");
}

function getCategories(){
    global $conn;
    return mysqli_query($conn,"SELECT * FROM categories");
}

function getPosts(){
    global $conn;
    $imageField = columnExists('posts', 'image') ? 'posts.image' : 'NULL AS image';
    $dateField = columnExists('posts', 'created_at') ? 'posts.created_at' : 'NULL AS created_at';
    return mysqli_query($conn,"
        SELECT posts.id, posts.title, posts.content, {$imageField}, {$dateField},
               users.name AS author, categories.name AS category
        FROM posts
        JOIN users ON posts.user_id = users.id
        JOIN categories ON posts.category_id = categories.id
        ORDER BY posts.id DESC
    ");
}

function getPostById($id){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function getPostDetail($id){
    global $conn;
    $imageField = columnExists('posts', 'image') ? 'posts.image' : 'NULL AS image';
    $dateField = columnExists('posts', 'created_at') ? 'posts.created_at' : 'NULL AS created_at';
    $stmt = mysqli_prepare($conn, "
        SELECT posts.id, posts.title, posts.content, {$imageField}, {$dateField},
               users.name AS author, categories.name AS category
        FROM posts
        JOIN users ON posts.user_id = users.id
        JOIN categories ON posts.category_id = categories.id
        WHERE posts.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function getCommentsByPostId($postId){
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    $stmt = mysqli_prepare($conn, "
        SELECT comments.id, comments.comment, {$dateField}, users.name AS author
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $res;
}

function addComment($postId, $userId, $comment){
    global $conn;
    $stmt = mysqli_prepare($conn, "INSERT INTO comments(post_id, user_id, comment) VALUES(?,?,?)");
    mysqli_stmt_bind_param($stmt, "iis", $postId, $userId, $comment);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getPostOwnerId($postId){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)($row['user_id'] ?? 0);
}

function getPostsFiltered($search, $categoryId, $limit, $offset){
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

    if($categoryId > 0){
        $sql .= " AND categories.id = ? ";
        $types .= 'i';
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY posts.id DESC LIMIT ? OFFSET ? ";
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = mysqli_prepare($conn, $sql);
    if(!$stmt){
        // fallback to basic query if schema doesn't match
        return getPosts();
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $res;
}

function countPostsFiltered($search, $categoryId){
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM posts JOIN categories ON posts.category_id = categories.id WHERE 1=1";

    $types = '';
    $params = [];

    if($categoryId > 0){
        $sql .= " AND categories.id = ? ";
        $types .= 'i';
        $params[] = $categoryId;
    }

    $stmt = mysqli_prepare($conn, $sql);
    if(!$stmt){
        return 0;
    }
    if($types !== ''){
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}

function getCommentById($id){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function updateComment($commentId, $comment){
    global $conn;
    $stmt = mysqli_prepare($conn, "UPDATE comments SET comment = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $comment, $commentId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function deleteComment($commentId){
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $commentId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getUserById($id){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function countPostsByUser($userId){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM posts WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}

function countCommentsByUser($userId){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM comments WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)($row['total'] ?? 0);
}


function countAllPosts(){
    global $conn;
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM posts");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function countAllComments(){
    global $conn;
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function countAllUsers(){
    global $conn;
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

function getCommentsOnUserPosts($userId){
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    $stmt = mysqli_prepare($conn, "
        SELECT comments.comment, {$dateField}, users.name AS commenter, posts.title AS post_title
        FROM comments
        JOIN posts ON comments.post_id = posts.id
        JOIN users ON comments.user_id = users.id
        WHERE posts.user_id = ?
        ORDER BY comments.id DESC
    ");
    if(!$stmt){
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $res;
}

function getAllCommentsWithPosts(){
    global $conn;
    $dateField = columnExists('comments', 'created_at') ? 'comments.created_at' : 'NULL AS created_at';
    return mysqli_query($conn, "
        SELECT comments.comment, {$dateField} AS created_at, users.name AS commenter, posts.title AS post_title
        FROM comments
        JOIN posts ON comments.post_id = posts.id
        JOIN users ON comments.user_id = users.id
        ORDER BY comments.id DESC
    ");
}

function getUserByName($name){
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE name = ?");
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function getUserByEmail($email){
    global $conn;
    if(!columnExists('users', 'email')){
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function getUserByUserEmail($email){
    global $conn;
    if(!columnExists('users', 'user_emails')){
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_emails = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

function createUser($name, $email, $role, $password, $profileImage){
    global $conn;
    $hasEmail = columnExists('users', 'email');
    $hasUserEmails = columnExists('users', 'user_emails');
    $hasPassword = columnExists('users', 'password');
    $hasImage = columnExists('users', 'profile_image');
    $hasProfile = columnExists('users', 'profile');

    if($hasEmail && $hasUserEmails && $hasPassword && $hasImage && $hasProfile){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password, profile_image, profile) VALUES(?,?,?,?,?,?,?)");
        $profileText = '';
        mysqli_stmt_bind_param($stmt, "sssssss", $name, $role, $email, $email, $password, $profileImage, $profileText);
    } elseif($hasEmail && $hasUserEmails && $hasPassword && $hasImage){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password, profile_image) VALUES(?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $role, $email, $email, $password, $profileImage);
    } elseif($hasEmail && $hasUserEmails && $hasPassword){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, user_emails, password) VALUES(?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssss", $name, $role, $email, $email, $password);
    } elseif($hasEmail && $hasPassword && $hasImage && $hasProfile){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, password, profile_image, profile) VALUES(?,?,?,?,?,?)");
        $profileText = '';
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $role, $email, $password, $profileImage, $profileText);
    } elseif($hasEmail && $hasPassword && $hasImage){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, password, profile_image) VALUES(?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssss", $name, $role, $email, $password, $profileImage);
    } elseif($hasEmail && $hasPassword){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, email, password) VALUES(?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $role, $email, $password);
    } elseif($hasPassword){
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role, password) VALUES(?,?,?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $role, $password);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users(name, role) VALUES(?,?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $role);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteUserAndContent($userId){
    global $conn;
    $userId = (int)$userId;
    if($userId <= 0){
        return false;
    }

    $postIds = [];
    $res = mysqli_query($conn, "SELECT id FROM posts WHERE user_id = {$userId}");
    if($res){
        while($row = mysqli_fetch_assoc($res)){
            $postIds[] = (int)$row['id'];
        }
    }

    if(count($postIds) > 0){
        $in = implode(',', $postIds);
        mysqli_query($conn, "DELETE FROM comments WHERE post_id IN ({$in})");
        mysqli_query($conn, "DELETE FROM posts WHERE id IN ({$in})");
    }

    mysqli_query($conn, "DELETE FROM comments WHERE user_id = {$userId}");
    mysqli_query($conn, "DELETE FROM users WHERE id = {$userId}");
    return true;
}
?>

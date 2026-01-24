<?php
session_start();
include 'functions.php';

$page = $_GET['page'] ?? 'home';
$id = (int)($_GET['id'] ?? 0);
$pageNum = max(1, (int)($_GET['p'] ?? 1));
$search = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['cat'] ?? 0);
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

function isAdmin(){
    $role = $_SESSION['role'] ?? '';
    return ($role === 'Admin');
}

function canManagePosts(){
    $role = $_SESSION['role'] ?? '';
    return ($role === 'Admin' || $role === 'Editor' || $role === 'Author');
}

function currentUser(){
    if(!isset($_SESSION['user_id'])){
        return null;
    }
    return getUserById((int)$_SESSION['user_id']);
}

function renderHeader($title){
    $safeTitle = e($title);
    echo "<!DOCTYPE html>\n<html>\n<head>\n<title>{$safeTitle}</title>\n<link rel=\"stylesheet\" href=\"style.css?v=1\">\n</head>\n<body>\n<div class=\"container\">";
    renderNavbar();
}

function renderNavbar(){
    $user = currentUser();
    echo "<nav class=\"navbar\">\n";
    echo "  <div class=\"nav-left\">Blogify – Personal Blogging System</div>\n";
    echo "  <div class=\"nav-links\">";
    echo "<a href=\"index.php?page=home\">Home</a>";
    echo "<a href=\"index.php?page=add\">Add Post</a>";
    
    if($user){
        echo "<a href=\"index.php?page=profile\">Profile</a>";
        echo "<span class=\"nav-user\">Hi " . e($user['name']) . "</span>";
        echo "<a href=\"index.php?page=logout\" onclick=\"return confirm('Are you sure you want to logout?');\">Logout</a>";
    } else {
        echo "<a href=\"index.php?page=login\">Login</a>";
        echo "<a href=\"index.php?page=register\">Register</a>";
    }
    echo "</div>\n";
    echo "</nav>\n";
}

function renderFooter(){
    echo "</div>\n<script src=\"script.js?v=1\"></script>\n</body>\n</html>";
}

function renderPostCard($post, $showAdminActions = false, $showContent = true, $showViewLink = true){
    echo "<div class=\"card\">";
    if(!empty($post['image'])){
        $imgSrc = rawurlencode($post['image']);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $publicSrc = $basePath . '/uploads/' . $imgSrc;
        $diskPath = __DIR__ . '/uploads/' . $post['image'];
        echo "<img class=\"post-image\" src=\"{$publicSrc}\" alt=\"Post Image\">";
        if(!file_exists($diskPath)){
            echo "<small style=\"color:red;\">Image file missing: " . e($post['image']) . "</small>";
        }
    }
    echo "<h3>" . e($post['title']) . "</h3>";
    echo "<small>By " . e($post['author']) . " | Category: " . e($post['category']) . " | " . e($post['created_at']) . "</small>";
    if($showContent){
        echo "<p>" . nl2br(e($post['content'])) . "</p>";
    }
    if($showAdminActions){
        echo "<div class=\"admin-actions\">";
        echo "<a href=\"index.php?page=view&id=" . (int)$post['id'] . "\">Comments</a>";
        echo "<a href=\"index.php?page=edit&id=" . (int)$post['id'] . "\">Edit</a>";
        echo "<a href=\"index.php?page=delete&id=" . (int)$post['id'] . "\" onclick=\"return confirm('Delete this post?');\">Delete</a>";
        echo "</div>";
    } elseif($showViewLink){
        echo "<a href=\"index.php?page=view&id=" . (int)$post['id'] . "\">Comments</a>";
    }
    echo "</div>";
}

function renderFilterForm($categoryId, $categories, $page){
    echo "<form class=\"search-form\" method=\"get\">";
    echo "<input type=\"hidden\" name=\"page\" value=\"" . e($page) . "\">";
    echo "<select name=\"cat\">";
    echo "<option value=\"0\">All Categories</option>";
    while($c = mysqli_fetch_assoc($categories)){
        $selected = ((int)$c['id'] === $categoryId) ? 'selected' : '';
        echo "<option value=\"" . (int)$c['id'] . "\" {$selected}>" . e($c['name']) . "</option>";
    }
    echo "</select>";
    echo "<button>Filter</button>";
    echo "</form>";
}

function renderPagination($page, $totalPages, $baseParams){
    if($totalPages <= 1){
        return;
    }
    echo "<div class=\"pagination\">";
    if($page > 1){
        $prev = $page - 1;
        echo "<a href=\"?" . http_build_query(array_merge($baseParams, ['p' => $prev])) . "\">Prev</a>";
    }
    echo "<span>Page {$page} of {$totalPages}</span>";
    if($page < $totalPages){
        $next = $page + 1;
        echo "<a href=\"?" . http_build_query(array_merge($baseParams, ['p' => $next])) . "\">Next</a>";
    }
    echo "</div>";
}

// Login/Logout
if($page === 'login'){
    renderHeader('Login');
    echo "<h2>Login</h2>";
    echo "<div class=\"card auth-card\">";
    echo "<p class=\"muted\">Use your name and password.</p>";
    echo "<form id=\"loginForm\">";
    echo "<input type=\"text\" name=\"name\" placeholder=\"Name\" required><br>";
    echo "<input type=\"password\" name=\"password\" placeholder=\"Password\" required><br>";
    echo "<select name=\"role\" required>";
    echo "<option value=\"Author\">Author</option>";
    echo "<option value=\"Admin\">Admin</option>";
    echo "</select><br>";
    echo "<button>Login</button>";
    echo "</form>";
    echo "<p id=\"auth-message\"></p>";
    echo "</div>";
    renderFooter();
    exit;
}

if($page === 'register'){
    renderHeader('Register');
    echo "<h2>Register</h2>";
    echo "<div class=\"card auth-card\">";
    echo "<p class=\"muted\">Create a basic account.</p>";
    echo "<form id=\"registerForm\" enctype=\"multipart/form-data\">";
    echo "<input type=\"text\" name=\"name\" placeholder=\"Name\" required><br>";
    echo "<input type=\"email\" name=\"email\" placeholder=\"Email\" required><br>";
    echo "<input type=\"password\" name=\"password\" placeholder=\"Password\" required><br>";
    echo "<input type=\"password\" name=\"confirm_password\" placeholder=\"Confirm Password\" required><br>";
    echo "<select name=\"role\">";
    echo "<option value=\"Author\">Author</option>";
    echo "<option value=\"Admin\">Admin</option>";
    echo "</select><br>";
    echo "<button>Register</button>";
    echo "</form>";
    echo "<p id=\"auth-message\"></p>";
    echo "</div>";
    renderFooter();
    exit;
}

if($page === 'register_action'){
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'Author');
    if($role !== 'Admin' && $role !== 'Author'){
        $role = 'Author';
    }

    if(!columnExists('users', 'email')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN email VARCHAR(150)");
    }
    if(!columnExists('users', 'user_emails')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN user_emails VARCHAR(150)");
    }
    if(!columnExists('users', 'password')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN password VARCHAR(255)");
    }
    if(!columnExists('users', 'profile_image')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255)");
    }
    if(!columnExists('users', 'profile')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile TEXT");
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(['ok' => false, 'message' => 'Enter a valid email.']);
        exit;
    }

    if($name === '' || $password === '' || $email === ''){
        echo json_encode(['ok' => false, 'message' => 'All fields required.']);
        exit;
    }

    if(getUserByName($name)){
        echo json_encode(['ok' => false, 'message' => 'User already exists.']);
        exit;
    }

    if(getUserByEmail($email) || getUserByUserEmail($email)){
        echo json_encode(['ok' => false, 'message' => 'Email already exists.']);
        exit;
    }

    $ok = createUser($name, $email, $role, $password, '');
    echo json_encode(['ok' => $ok, 'message' => $ok ? 'Registered successfully.' : 'Register failed.']);
    exit;
}

if($page === 'login_action'){
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $requestedRole = trim($_POST['role'] ?? '');
    $user = getUserByName($name);
    if(!$user){
        $user = getUserByEmail($name);
    }
    if(!$user){
        $user = getUserByUserEmail($name);
    }

    if(!columnExists('users', 'password')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN password VARCHAR(255)");
        if($user){
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $password, $user['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    if(!$user){
        $_SESSION = [];
        echo json_encode(['ok' => false, 'message' => 'Username not found. Don\'t have an account? Register.' ]);
        exit;
    }

    if($requestedRole !== '' && strcasecmp($requestedRole, (string)$user['role']) !== 0){
        $_SESSION = [];
        echo json_encode(['ok' => false, 'message' => 'Role mismatch. Select the correct role.' ]);
        exit;
    }

    if(columnExists('users', 'password')){
        $dbPass = $user['password'] ?? '';
        $isPlainMatch = ($password === $dbPass);
        $isHashMatch = password_verify($password, $dbPass);
        $isValid = ($isPlainMatch || $isHashMatch);
        if($isHashMatch && !$isPlainMatch){
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $password, $user['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        if($dbPass === '' && $password !== ''){
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $password, $user['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $isValid = true;
        }
    } else {
        $isValid = false;
    }

    if(!$isValid){
        $_SESSION = [];
        echo json_encode(['ok' => false, 'message' => 'Wrong password.']);
        exit;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(['ok' => true, 'message' => 'Login successful.']);
    exit;
}

if($page === 'logout'){
    session_destroy();
    header("Location: index.php?page=home");
    exit;
}

// Actions
if($page === 'delete' && $id > 0){
    if(!canManagePosts()){
        header("Location: index.php?page=login");
        exit;
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: index.php?page=admin");
    exit;
}


if($page === 'comment_delete' && isset($_GET['comment_id'])){
    if(!canManagePosts()){
        header("Location: index.php?page=login");
        exit;
    }
    $commentId = (int)$_GET['comment_id'];
    $postId = (int)($_GET['post_id'] ?? 0);
    deleteComment($commentId);
    header("Location: index.php?page=view&id=" . $postId);
    exit;
}

if($page === 'comment_update' && isset($_GET['comment_id'])){
    if(!canManagePosts()){
        header("Location: index.php?page=login");
        exit;
    }
    $commentId = (int)$_GET['comment_id'];
    $postId = (int)($_GET['post_id'] ?? 0);
    $commentText = trim($_POST['comment'] ?? '');
    if($commentText !== ''){
        updateComment($commentId, $commentText);
    }
    header("Location: index.php?page=view&id=" . $postId);
    exit;
}

if($page === 'profile_update'){
    $user = currentUser();
    if(!$user){
        header("Location: index.php?page=login");
        exit;
    }

    $name = trim($_POST['name'] ?? $user['name']);
    $email = trim($_POST['email'] ?? ($user['email'] ?? ''));
    $profileText = trim($_POST['profile'] ?? ($user['profile'] ?? ''));
    $imageName = $user['profile_image'] ?? '';
    $uploadDir = __DIR__ . '/uploads/profiles/';

    if(!is_dir($uploadDir)){
        @mkdir($uploadDir, 0777, true);
    }

    if(!empty($_FILES['profile_image']['name'])){
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['profile_image']['name']));
            $imageName = time() . '_' . $baseName;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if(!columnExists('users', 'profile')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile TEXT");
    }
    if(!columnExists('users', 'profile_image')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255)");
    }
    if(!columnExists('users', 'email')){
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN email VARCHAR(150)");
    }

    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, profile = ?, profile_image = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $profileText, $imageName, $user['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: index.php?page=profile");
    exit;
}

if($page === 'author_add'){
    if(!isAdmin()){
        header("Location: index.php?page=login");
        exit;
    }
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($name !== '' && $email !== '' && $password !== ''){
        if(!columnExists('users', 'email')){
            mysqli_query($conn, "ALTER TABLE users ADD COLUMN email VARCHAR(150)");
        }
        if(!columnExists('users', 'user_emails')){
            mysqli_query($conn, "ALTER TABLE users ADD COLUMN user_emails VARCHAR(150)");
        }
        if(!columnExists('users', 'password')){
            mysqli_query($conn, "ALTER TABLE users ADD COLUMN password VARCHAR(255)");
        }
        if(!columnExists('users', 'profile_image')){
            mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255)");
        }
        if(!columnExists('users', 'profile')){
            mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile TEXT");
        }

        if(!getUserByName($name) && !getUserByEmail($email) && !getUserByUserEmail($email)){
            createUser($name, $email, 'Author', $password, '');
        }
    }
    header("Location: index.php?page=profile");
    exit;
}

if($page === 'author_delete' && $id > 0){
    if(!isAdmin()){
        header("Location: index.php?page=login");
        exit;
    }
    deleteUserAndContent($id);
    header("Location: index.php?page=profile");
    exit;
}

if($page === 'home'){
    $categories = getCategories();
    $posts = getPostsFiltered($search, $categoryId, $perPage, $offset);
    $total = countPostsFiltered($search, $categoryId);
    $totalPages = (int)ceil($total / $perPage);

    renderHeader('Blogify – Personal Blogging System');
    echo "<h2>All Posts</h2>";
    renderFilterForm($categoryId, $categories, 'home');

    if($posts === false){
        echo "<p style=\"color:red;\">Query error: " . e(mysqli_error($conn)) . "</p>";
    } elseif(mysqli_num_rows($posts) === 0){
        echo "<p>No posts found.</p>";
    } else {
        echo "<div class=\"post-grid\">";
        while($p = mysqli_fetch_assoc($posts)){
            renderPostCard($p, false, true, true);
        }
        echo "</div>";
    }

    renderPagination($pageNum, $totalPages, ['page' => 'home', 'q' => $search, 'cat' => $categoryId]);
    renderFooter();
    exit;
}

if($page === 'user'){
    $categories = getCategories();
    $posts = getPostsFiltered($search, $categoryId, $perPage, $offset);
    $total = countPostsFiltered($search, $categoryId);
    $totalPages = (int)ceil($total / $perPage);

    renderHeader('User Section');
    echo "<h2>User Section</h2>";
    echo "<p>Here users can only view posts.</p>";
    renderFilterForm($categoryId, $categories, 'user');

    if($posts === false){
        echo "<p style=\"color:red;\">Query error: " . e(mysqli_error($conn)) . "</p>";
    } elseif(mysqli_num_rows($posts) === 0){
        echo "<p>No posts found.</p>";
    } else {
        while($p = mysqli_fetch_assoc($posts)){
            renderPostCard($p, false, true, true);
        }
    }

    renderPagination($pageNum, $totalPages, ['page' => 'user', 'cat' => $categoryId]);
    renderFooter();
    exit;
}

if($page === 'admin'){
    if(!isAdmin()){
        header("Location: index.php?page=login");
        exit;
    }
    $posts = getPosts();
    $totalPosts = countAllPosts();
    $totalComments = countAllComments();
    $totalUsers = countAllUsers();
    renderHeader('Admin Panel');
    echo "<h2>Admin Panel</h2>";
    echo "<div class=\"stat-grid\">";
    echo "<div class=\"stat-card\"><div class=\"stat-label\">Total Posts</div><div class=\"stat-value\">{$totalPosts}</div></div>";
    echo "<div class=\"stat-card\"><div class=\"stat-label\">Total Comments</div><div class=\"stat-value\">{$totalComments}</div></div>";
    echo "<div class=\"stat-card\"><div class=\"stat-label\">Total Users</div><div class=\"stat-value\">{$totalUsers}</div></div>";
    echo "</div>";
    echo "<a class=\"btn\" href=\"index.php?page=add\">Add New Post</a>";
    if($posts === false){
        echo "<p style=\"color:red;\">Query error: " . e(mysqli_error($conn)) . "</p>";
    } elseif(mysqli_num_rows($posts) === 0){
        echo "<p>No posts found.</p>";
    } else {
        while($p = mysqli_fetch_assoc($posts)){
            renderPostCard($p, true, true, true);
        }
    }
    renderFooter();
    exit;
}

if($page === 'add'){
    if(!canManagePosts()){
        header("Location: index.php?page=login");
        exit;
    }
    $error = '';
    if(isset($_POST['add_post'])){
        $current = currentUser();
        $user = isAdmin() ? (int)($_POST['user'] ?? 0) : (int)($current['id'] ?? 0);
        $category = (int)($_POST['category'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $imageName = '';
        $uploadDir = __DIR__ . '/uploads/';

        if(!is_dir($uploadDir)){
            $error = 'Uploads folder missing.';
        }
        if($error === '' && !is_writable($uploadDir)){
            $error = 'Uploads folder not writable.';
        }

        if(!empty($_FILES['image']['name'])){
            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, $allowed)){
                $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image']['name']));
                $imageName = time() . '_' . $baseName;
                if(!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)){
                    $code = (int)($_FILES['image']['error'] ?? 0);
                    $error = 'Image upload failed. Error code: ' . $code;
                }
            } else {
                $error = 'Invalid image type.';
            }
        }

        if($user <= 0 || $category <= 0 || $title === '' || $content === ''){
            $error = 'All fields are required.';
        } elseif($error === '') {
            if($imageName !== '' && !columnExists('posts', 'image')){
                mysqli_query($conn, "ALTER TABLE posts ADD COLUMN image VARCHAR(255)");
            }
            if($imageName !== '' && !columnExists('posts', 'image')){
                $error = 'Image column not found in DB.';
            }
            if($error === ''){
                if(columnExists('posts', 'image')){
                    $stmt = mysqli_prepare($conn, "INSERT INTO posts(user_id,category_id,title,content,image) VALUES(?,?,?,?,?)");
                    mysqli_stmt_bind_param($stmt, "iisss", $user, $category, $title, $content, $imageName);
                } else {
                    $stmt = mysqli_prepare($conn, "INSERT INTO posts(user_id,category_id,title,content) VALUES(?,?,?,?)");
                    mysqli_stmt_bind_param($stmt, "iiss", $user, $category, $title, $content);
                }
                mysqli_stmt_execute($stmt);
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if($imageName !== '' && columnExists('posts', 'image')){
                    $stmt = mysqli_prepare($conn, "UPDATE posts SET image = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "si", $imageName, $newId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }

                header("Location: index.php?page=home");
                exit;
            }
        }
    }

    $users = getUsers();
    $categories = getCategories();

    renderHeader('Add Post');
    echo "<h2>Add New Post</h2>";
    echo "<div class=\"card\">";
    if($error !== ''){
        echo "<p style=\"color:red;\">" . e($error) . "</p>";
    }
    if(!columnExists('posts', 'image')){
        echo "<p style=\"color:#a85;\">Note: add image column in DB to show images.</p>";
    }
    echo "<form method=\"post\" enctype=\"multipart/form-data\">";
    if(isAdmin()){
        echo "<select name=\"user\">";
        while($u = mysqli_fetch_assoc($users)){
            echo "<option value=\"" . (int)$u['id'] . "\">" . e($u['name']) . "</option>";
        }
        echo "</select>";
    } else {
        $current = currentUser();
        echo "<input type=\"hidden\" name=\"user\" value=\"" . (int)($current['id'] ?? 0) . "\">";
        echo "<p class=\"muted\">Posting as: <strong>" . e($current['name'] ?? '') . "</strong></p>";
    }
    echo "<select name=\"category\">";
    mysqli_data_seek($categories, 0);
    while($c = mysqli_fetch_assoc($categories)){
        echo "<option value=\"" . (int)$c['id'] . "\">" . e($c['name']) . "</option>";
    }
    echo "</select><br>";
    echo "<input type=\"text\" name=\"title\" placeholder=\"Title\" value=\"" . e($_POST['title'] ?? '') . "\"><br>";
    echo "<textarea name=\"content\" placeholder=\"Content\">" . e($_POST['content'] ?? '') . "</textarea><br>";
    echo "<input type=\"file\" name=\"image\" accept=\"image/*\"><br>";
    echo "<button name=\"add_post\">Add Post</button>";
    echo "</form>";
    echo "</div>";
    echo "<a href=\"index.php?page=home\">Back to Home</a>";
    renderFooter();
    exit;
}

if($page === 'profile'){
    $user = currentUser();
    if(!$user){
        header("Location: index.php?page=login");
        exit;
    }

    $myPosts = countPostsByUser((int)$user['id']);
    $myComments = countCommentsByUser((int)$user['id']);
    $commentsOnMyPosts = getCommentsOnUserPosts((int)$user['id']);
    $allComments = isAdmin() ? getAllCommentsWithPosts() : null;

    renderHeader('My Profile');
    echo "<h2>My Dashboard</h2>";
    echo "<div class=\"profile-header\">";
    echo "<div class=\"profile-info\">";
    if(!empty($user['profile_image'])){
        echo "<img class=\"profile-image\" src=\"uploads/profiles/" . e($user['profile_image']) . "\" alt=\"Profile\">";
    } else {
        echo "<div class=\"profile-image placeholder\">" . e(substr($user['name'], 0, 1)) . "</div>";
    }
    echo "<div class=\"profile-meta\">";
    echo "<h3>" . e($user['name']) . "</h3>";
    if(!empty($user['email'])){
        echo "<p>" . e($user['email']) . "</p>";
    }
    echo "<span class=\"role-badge\">" . e($user['role']) . "</span>";
    if(!empty($user['profile'])){
        echo "<p class=\"profile-bio\">" . nl2br(e($user['profile'])) . "</p>";
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div class=\"stat-grid\">";
    echo "<div class=\"stat-card\"><div class=\"stat-label\">My Posts</div><div class=\"stat-value\">{$myPosts}</div></div>";
    echo "<div class=\"stat-card\"><div class=\"stat-label\">My Comments</div><div class=\"stat-value\">{$myComments}</div></div>";
    echo "</div>";

    echo "<details class=\"collapsible\">";
    echo "<summary>Update Profile</summary>";
    echo "<div class=\"card\">";
    echo "<form class=\"profile-form\" method=\"post\" enctype=\"multipart/form-data\" action=\"index.php?page=profile_update\">";
    echo "<div class=\"form-grid\">";
    echo "<input type=\"text\" name=\"name\" placeholder=\"Name\" value=\"" . e($user['name']) . "\" required>";
    echo "<input type=\"email\" name=\"email\" placeholder=\"Email\" value=\"" . e($user['email'] ?? '') . "\">";
    echo "</div>";
    echo "<textarea name=\"profile\" placeholder=\"Short bio\">" . e($user['profile'] ?? '') . "</textarea>";
    echo "<div class=\"form-grid\">";
    echo "<input type=\"file\" name=\"profile_image\" accept=\"image/*\">";
    echo "<div class=\"form-actions\"><button>Save Profile</button></div>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
    echo "</details>";

    if(isAdmin()){
        $authors = getAuthors();
        echo "<h3>Manage Authors</h3>";
        echo "<div class=\"card\">";
        echo "<form class=\"author-form\" method=\"post\" action=\"index.php?page=author_add\">";
        echo "<div class=\"form-grid\">";
        echo "<input type=\"text\" name=\"name\" placeholder=\"Author name\" required>";
        echo "<input type=\"email\" name=\"email\" placeholder=\"Author email\" required>";
        echo "</div>";
        echo "<div class=\"form-grid\">";
        echo "<input type=\"password\" name=\"password\" placeholder=\"Password\" required>";
        echo "<div class=\"form-actions\"><button class=\"btn-add-author\">Add Author</button></div>";
        echo "</div>";
        echo "</form>";

        if($authors && mysqli_num_rows($authors) > 0){
            echo "<ul class=\"author-list\">";
            while($a = mysqli_fetch_assoc($authors)){
                echo "<li>" . e($a['name']) . " (" . e($a['email'] ?? '') . ") ";
                echo "<a href=\"index.php?page=author_delete&id=" . (int)$a['id'] . "\" onclick=\"return confirm('Remove this author?');\">Remove</a>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No authors found.</p>";
        }
        echo "</div>";
    }

    if(isAdmin()){
        $adminPosts = getPosts();
        echo "<h3>Manage Posts</h3>";
        if($adminPosts === false){
            echo "<p>Unable to load posts.</p>";
        } elseif(mysqli_num_rows($adminPosts) === 0){
            echo "<p>No posts found.</p>";
        } else {
            echo "<div class=\"post-grid manage-posts-grid\">";
            while($p = mysqli_fetch_assoc($adminPosts)){
                echo "<div class=\"card manage-post-card\">";
                echo "<h4>" . e($p['title']) . "</h4>";
                echo "<p class=\"clamp-2\">" . e($p['content']) . "</p>";
                echo "<div class=\"manage-post-actions\">";
                echo "<a class=\"btn btn-small\" href=\"index.php?page=view&id=" . (int)$p['id'] . "\">See More</a>";
                echo "<a href=\"index.php?page=edit&id=" . (int)$p['id'] . "\">Edit</a>";
                echo "<a href=\"index.php?page=delete&id=" . (int)$p['id'] . "\" onclick=\"return confirm('Delete this post?');\">Delete</a>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        }
    }

    if(canManagePosts() && !isAdmin()){
        echo "<h3>Comments on My Posts</h3>";
        echo "<div class=\"card\">";
        $list = $commentsOnMyPosts;
        if($list === false || $list === null){
            echo "<p>Unable to load comments.</p>";
        } elseif(mysqli_num_rows($list) === 0){
            echo "<p>No comments yet.</p>";
        } else {
            while($c = mysqli_fetch_assoc($list)){
                echo "<p><strong>" . e($c['commenter']) . "</strong> on <em>" . e($c['post_title']) . "</em>: " . e($c['comment']) . "</p>";
                if(!empty($c['created_at'])){
                    echo "<small>" . e($c['created_at']) . "</small>";
                }
            }
        }
        echo "</div>";
    }
    renderFooter();
    exit;
}

if($page === 'edit'){
    if(!canManagePosts()){
        header("Location: index.php?page=login");
        exit;
    }
    $post = ($id > 0) ? getPostById($id) : null;
    $error = '';

    if(!$post){
        $error = 'Post not found.';
    } elseif(isset($_POST['update_post'])){
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if($title === '' || $content === ''){
            $error = 'Title and content are required.';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE posts SET title = ?, content = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header("Location: index.php?page=view&id=" . $id);
            exit;
        }
    }

    renderHeader('Edit Post');
    echo "<h2>Edit Post</h2>";
    if($error !== ''){
        echo "<p style=\"color:red;\">" . e($error) . "</p>";
    }
    if($post){
        echo "<form method=\"post\">";
        echo "<input type=\"text\" name=\"title\" placeholder=\"Title\" value=\"" . e($_POST['title'] ?? $post['title']) . "\"><br>";
        echo "<textarea name=\"content\" placeholder=\"Content\">" . e($_POST['content'] ?? $post['content']) . "</textarea><br>";
        echo "<button name=\"update_post\">Update Post</button>";
        echo "</form>";
    }
    echo "<a href=\"index.php?page=home\">Back to Home</a>";
    renderFooter();
    exit;
}

if($page === 'view'){
    $post = ($id > 0) ? getPostDetail($id) : null;
    $commentError = '';
    $commentEditId = (int)($_GET['comment_edit_id'] ?? 0);

    if($post && isset($_POST['add_comment'])){
        $comment = trim($_POST['comment'] ?? '');
            $user = currentUser();
            $userId = $user ? (int)$user['id'] : 0;
            if($userId <= 0 || $comment === ''){
                $commentError = 'Please login and write a comment.';
        } else {
            addComment($id, $userId, $comment);
            header("Location: index.php?page=view&id=" . $id);
            exit;
        }
    }

    $users = getUsers();
    $comments = $post ? getCommentsByPostId($id) : null;
    $editComment = ($commentEditId > 0) ? getCommentById($commentEditId) : null;

    renderHeader('View Post');
    echo "<h2>View Post</h2>";
    if(!$post){
        echo "<p>Post not found.</p>";
    } else {
        renderPostCard($post, false, true, false);

        echo "<div class=\"card\">";
        echo "<h3>Comments</h3>";
        if($comments && mysqli_num_rows($comments) === 0){
            echo "<p>No comments yet.</p>";
        } elseif($comments){
            while($c = mysqli_fetch_assoc($comments)){
                echo "<p><strong>" . e($c['author']) . ":</strong> " . e($c['comment']) . "</p>";
                echo "<small>" . e($c['created_at']) . "</small>";
                if(isAdmin()){
                    echo "<div class=\"admin-actions\">";
                    echo "<a href=\"index.php?page=view&id=" . $id . "&comment_edit_id=" . (int)$c['id'] . "\">Edit</a>";
                    echo "<a href=\"index.php?page=comment_delete&comment_id=" . (int)$c['id'] . "&post_id=" . $id . "\" onclick=\"return confirm('Delete comment?');\">Delete</a>";
                    echo "</div>";
                }
            }
        }
        echo "</div>";

        if(isAdmin() && $editComment){
            echo "<div class=\"card\">";
            echo "<h3>Edit Comment</h3>";
            echo "<form method=\"post\" action=\"index.php?page=comment_update&comment_id=" . (int)$editComment['id'] . "&post_id=" . $id . "\">";
            echo "<textarea name=\"comment\">" . e($editComment['comment']) . "</textarea><br>";
            echo "<button>Update Comment</button>";
            echo "</form>";
            echo "</div>";
        }

        echo "<div class=\"card\">";
        echo "<h3>Add Comment</h3>";
        if($commentError !== ''){
            echo "<p style=\"color:red;\">" . e($commentError) . "</p>";
        }
        echo "<form method=\"post\">";
        echo "<textarea name=\"comment\" placeholder=\"Write a comment...\">" . e($_POST['comment'] ?? '') . "</textarea><br>";
        echo "<button name=\"add_comment\">Add Comment</button>";
        echo "</form>";
        echo "</div>";
    }
    echo "<a href=\"index.php?page=home\">Back to Home</a>";
    renderFooter();
    exit;
}

header("Location: index.php?page=home");
exit;

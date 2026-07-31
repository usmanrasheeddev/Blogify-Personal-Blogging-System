<?php
// actions/profile_actions.php - Profile bio, avatar upload, and admin author management handlers

$action = $_GET['page'] ?? '';

if ($action === 'profile_update') {
    $user = currentUser();
    if (!$user) {
        header("Location: index.php?page=login");
        exit;
    }

    $name = trim($_POST['name'] ?? $user['name']);
    $email = trim($_POST['email'] ?? ($user['email'] ?? ''));
    $profileText = trim($_POST['profile'] ?? ($user['profile'] ?? ''));
    $imageName = $user['profile_image'] ?? '';
    $uploadDir = dirname(__DIR__) . '/uploads/profiles/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['profile_image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['profile_image']['name']));
            $imageName = time() . '_' . $baseName;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    $stmt = my_db_prepare($conn, "UPDATE users SET name = ?, email = ?, profile = ?, profile_image = ? WHERE id = ?");
    my_db_stmt_bind_param($stmt, "ssssi", $name, $email, $profileText, $imageName, $user['id']);
    my_db_stmt_execute($stmt);
    my_db_stmt_close($stmt);

    header("Location: index.php?page=profile");
    exit;
}

if ($action === 'author_add') {
    if (!isAdmin()) {
        header("Location: index.php?page=login");
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($name) && !empty($email) && !empty($password)) {
        if (!getUserByName($name) && !getUserByEmail($email)) {
            createUser($name, $email, 'Author', $password, '');
        }
    }

    header("Location: index.php?page=profile");
    exit;
}

if ($action === 'author_delete') {
    if (!isAdmin()) {
        header("Location: index.php?page=login");
        exit;
    }
    $authorId = (int)($_GET['id'] ?? 0);
    if ($authorId > 0) {
        deleteUserAndContent($authorId);
    }

    header("Location: index.php?page=profile");
    exit;
}

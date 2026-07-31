<?php
// actions/auth_actions.php - Authentication handlers for login, register, and logout

$action = $_GET['page'] ?? '';

if ($action === 'login_action') {
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($password)) {
        echo json_encode(['ok' => false, 'message' => 'Username/email and password are required.']);
        exit;
    }

    $user = getUserByName($name);
    if (!$user) {
        $user = getUserByEmail($name);
    }
    if (!$user) {
        $user = getUserByUserEmail($name);
    }

    if (!$user) {
        $_SESSION = [];
        echo json_encode(['ok' => false, 'message' => 'Account not found. Please register.']);
        exit;
    }

    $dbPass = $user['password'] ?? '';
    $isPlainMatch = ($password === $dbPass);
    $isHashMatch = password_verify($password, $dbPass);
    $isValid = ($isPlainMatch || $isHashMatch);

    // Auto-upgrade legacy plain text password to hashed password
    if ($isPlainMatch && !$isHashMatch && strlen($dbPass) < 60) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = my_db_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        my_db_stmt_bind_param($stmt, "si", $newHash, $user['id']);
        my_db_stmt_execute($stmt);
        my_db_stmt_close($stmt);
    }

    if (!$isValid) {
        $_SESSION = [];
        echo json_encode(['ok' => false, 'message' => 'Invalid credentials. Please check your password.']);
        exit;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(['ok' => true, 'message' => 'Login successful! Redirecting...']);
    exit;
}

if ($action === 'register_action') {
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = 'Author';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    if (empty($name) || empty($password) || empty($email)) {
        echo json_encode(['ok' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (getUserByName($name)) {
        echo json_encode(['ok' => false, 'message' => 'Username is already taken.']);
        exit;
    }

    if (getUserByEmail($email) || getUserByUserEmail($email)) {
        echo json_encode(['ok' => false, 'message' => 'Email address is already registered.']);
        exit;
    }

    $ok = createUser($name, $email, $role, $password, '');
    $err = my_db_error($conn);

    if ($ok) {
        echo json_encode(['ok' => true, 'message' => 'Registration successful! You can now log in.']);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Registration failed: ' . $err]);
    }
    exit;
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: index.php?page=home");
    exit;
}

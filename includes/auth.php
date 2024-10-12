<?php
function check_login() {
    if (isset($_SESSION['user_id'])) {
        global $conn;
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return false;
}

function require_login() {
    $user = check_login();
    if (!$user) {
        header("Location: login.php");
        exit();
    }
    return $user;
}

function require_admin() {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
    return $user;
}
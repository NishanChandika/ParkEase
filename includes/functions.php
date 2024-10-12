<?php
function get_user_by_id($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function check_login() {
    if (isset($_SESSION['user_id'])) {
        return get_user_by_id($_SESSION['user_id']);
    }
    return false;
}

function attempt_login($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function register_user($username, $email, $password) {
    global $conn;
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return "Username or email already exists";
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return "Registration failed: " . $conn->error;
    }
}


function get_all_parking_spots() {
    global $conn;
    $result = $conn->query("SELECT * FROM parking_spots ORDER BY name");
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function get_parking_spot($spot_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM parking_spots WHERE id = ?");
    $stmt->bind_param("i", $spot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function add_parking_spot($name, $latitude, $longitude, $hourly_rate) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO parking_spots (name, latitude, longitude, hourly_rate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddd", $name, $latitude, $longitude, $hourly_rate);
    return $stmt->execute();
}

function update_parking_spot($spot_id, $name, $latitude, $longitude, $hourly_rate) {
    global $conn;
    $stmt = $conn->prepare("UPDATE parking_spots SET name = ?, latitude = ?, longitude = ?, hourly_rate = ? WHERE id = ?");
    $stmt->bind_param("sdddi", $name, $latitude, $longitude, $hourly_rate, $spot_id);
    return $stmt->execute();
}

function delete_parking_spot($spot_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM parking_spots WHERE id = ?");
    $stmt->bind_param("i", $spot_id);
    return $stmt->execute();
}

function create_reservation($user_id, $spot_id, $start_time, $end_time) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, spot_id, start_time, end_time, status) VALUES (?, ?, ?, ?, 'confirmed')");
    $stmt->bind_param("iiss", $user_id, $spot_id, $start_time, $end_time);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function get_user_reservations($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.id, p.name AS spot_name, r.start_time, r.end_time, r.status 
                            FROM reservations r 
                            JOIN parking_spots p ON r.spot_id = p.id 
                            WHERE r.user_id = ? 
                            ORDER BY r.start_time DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_all_reservations() {
    global $conn;
    $result = $conn->query("SELECT r.id, u.username, p.name AS spot_name, r.start_time, r.end_time, r.status 
                            FROM reservations r 
                            JOIN users u ON r.user_id = u.id 
                            JOIN parking_spots p ON r.spot_id = p.id 
                            ORDER BY r.start_time DESC");
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function cancel_reservation($reservation_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reservation_id, $user_id);
    return $stmt->execute();
}

function is_spot_available($spot_id, $start_time, $end_time) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations 
                            WHERE spot_id = ? AND status = 'confirmed'
                            AND ((start_time BETWEEN ? AND ?) 
                            OR (end_time BETWEEN ? AND ?)
                            OR (start_time <= ? AND end_time >= ?))");
    $stmt->bind_param("issssss", $spot_id, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] == 0;
}

function calculate_reservation_cost($spot_id, $start_time, $end_time) {
    global $conn;
    $stmt = $conn->prepare("SELECT hourly_rate FROM parking_spots WHERE id = ?");
    $stmt->bind_param("i", $spot_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $hourly_rate = $result['hourly_rate'];

    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $duration = $end->diff($start);
    $hours = $duration->h + ($duration->days * 24);

    return $hours * $hourly_rate;
}

function require_admin() {
    $user = check_login();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
    return $user;
}

// Utility function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate a CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
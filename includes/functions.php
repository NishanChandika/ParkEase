<?php
// Database connection should be available as $conn

function get_user_by_id($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_all_parking_spots() {
    global $conn;
    $result = $conn->query("SELECT * FROM parking_spots ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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

// New Function to Update a Parking Spot
function update_parking_spot($spot_id, $name, $latitude, $longitude, $hourly_rate) {
    global $conn;
    $stmt = $conn->prepare("UPDATE parking_spots SET name = ?, latitude = ?, longitude = ?, hourly_rate = ? WHERE id = ?");
    $stmt->bind_param("sdddi", $name, $latitude, $longitude, $hourly_rate, $spot_id);
    return $stmt->execute();
}

// New Function to Delete a Parking Spot
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
    return $stmt->execute() ? $conn->insert_id : false;
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
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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

// Utility function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function get_parking_spot_rate($spot_id) {
    global $conn; // Assuming you are using $conn as the DB connection

    $query = "SELECT hourly_rate FROM parking_spots WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $spot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['hourly_rate']; // Return rate as a decimal
    } else {
        return false; // Spot not found or inactive
    }
}

// Function to calculate the total payment based on start time, end time, and parking spot rate
function calculate_payment($spot_id, $start_time, $end_time) {
    // Get the rate from the database
    $rate_per_hour = get_parking_spot_rate($spot_id);

    if (!$rate_per_hour) {
        return "Invalid or inactive parking spot.";
    }

    // Convert start and end times to timestamps
    $start_timestamp = strtotime($start_time);
    $end_timestamp = strtotime($end_time);

    // Ensure the end time is after the start time
    if ($start_timestamp >= $end_timestamp) {
        return "Invalid reservation times.";
    }

    // Calculate the duration in hours (round up to the next hour)
    $hours_reserved = ceil(($end_timestamp - $start_timestamp) / 3600);

    // Calculate the total payment (rate is in decimal format, so convert to cents for Stripe)
    $total_payment = $hours_reserved * $rate_per_hour * 100; // Convert to cents

    return $total_payment; // Return the total amount in cents
}


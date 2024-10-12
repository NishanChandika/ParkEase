<?php
// Fetch statistics
$total_spots = $conn->query("SELECT COUNT(*) FROM parking_spots")->fetch_row()[0];
$total_reservations = $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

// Fetch recent reservations
$recent_reservations = $conn->query("
    SELECT r.id, u.username, p.name AS spot_name, r.start_time, r.end_time, r.status
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN parking_spots p ON r.spot_id = p.id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<h2>Admin Dashboard</h2>

<div class="stats-container">
    <div class="stat-card">
        <h3>Total Parking Spots</h3>
        <p><?php echo $total_spots; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Reservations</h3>
        <p><?php echo $total_reservations; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>
</div>

<h3>Recent Reservations</h3>
<?php if (empty($recent_reservations)): ?>
    <p>No recent reservations found.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Parking Spot</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_reservations as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                <td><?php echo htmlspecialchars($reservation['spot_name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['start_time']); ?></td>
                <td><?php echo htmlspecialchars($reservation['end_time']); ?></td>
                <td><?php echo htmlspecialchars($reservation['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
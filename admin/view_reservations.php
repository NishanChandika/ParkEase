<?php
if (!defined('ADMIN_PAGE')) {
    exit('Direct access not permitted');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Get total number of reservations
$total = $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0];
$pages = ceil($total / $perPage);

// Fetch reservations with pagination
$reservations = $conn->query("
    SELECT r.id, u.username, p.name AS spot_name, r.start_time, r.end_time, r.status, r.created_at
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN parking_spots p ON r.spot_id = p.id
    ORDER BY r.created_at DESC
    LIMIT {$start}, {$perPage}
")->fetch_all(MYSQLI_ASSOC);
?>

<h2>View Reservations</h2>

<?php if (empty($reservations)): ?>
    <p>No reservations found.</p>
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
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                <td><?php echo htmlspecialchars($reservation['spot_name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['start_time']); ?></td>
                <td><?php echo htmlspecialchars($reservation['end_time']); ?></td>
                <td><?php echo htmlspecialchars($reservation['status']); ?></td>
                <td><?php echo htmlspecialchars($reservation['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?action=view_reservations&page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
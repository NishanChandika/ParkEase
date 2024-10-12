<?php
if (!defined('ADMIN_PAGE')) {
    exit('Direct access not permitted');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_spot'])) {
        $name = $_POST['name'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $hourly_rate = $_POST['hourly_rate'];
        
        if (add_parking_spot($name, $latitude, $longitude, $hourly_rate)) {
            $message = "Parking spot added successfully.";
        } else {
            $message = "Error adding parking spot.";
        }
    } elseif (isset($_POST['delete_spot'])) {
        $spot_id = $_POST['spot_id'];
        
        if (delete_parking_spot($spot_id)) {
            $message = "Parking spot deleted successfully.";
        } else {
            $message = "Error deleting parking spot.";
        }
    }
}

$parking_spots = get_all_parking_spots();
?>

<h2>Manage Parking Spots</h2>

<?php if ($message): ?>
    <div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<h3>Add New Parking Spot</h3>
<form method="POST" class="admin-form">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="latitude">Latitude:</label>
        <input type="number" id="latitude" name="latitude" step="any" required>
    </div>
    <div class="form-group">
        <label for="longitude">Longitude:</label>
        <input type="number" id="longitude" name="longitude" step="any" required>
    </div>
    <div class="form-group">
        <label for="hourly_rate">Hourly Rate:</label>
        <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" required>
    </div>
    <button type="submit" name="add_spot" class="btn-primary">Add Parking Spot</button>
</form>

<h3>Existing Parking Spots</h3>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Hourly Rate</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parking_spots as $spot): ?>
        <tr>
            <td><?php echo htmlspecialchars($spot['id']); ?></td>
            <td><?php echo htmlspecialchars($spot['name']); ?></td>
            <td><?php echo htmlspecialchars($spot['latitude']); ?></td>
            <td><?php echo htmlspecialchars($spot['longitude']); ?></td>
            <td>$<?php echo htmlspecialchars($spot['hourly_rate']); ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="spot_id" value="<?php echo $spot['id']; ?>">
                    <button type="submit" name="delete_spot" class="btn-delete" onclick="return confirm('Are you sure you want to delete this parking spot?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ensure only admin users can access this page
$user = require_admin();

// Variables to hold the current parking spot data (for edit mode)
$edit_spot_id = '';
$edit_name = '';
$edit_latitude = '';
$edit_longitude = '';
$edit_hourly_rate = '';

// Handle adding a new parking spot or updating an existing one
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_spot'])) {
    $name = $_POST['name'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $hourly_rate = $_POST['hourly_rate'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($id) {
        // Update existing parking spot
        if (update_parking_spot($id, $name, $latitude, $longitude, $hourly_rate)) {
            $success_message = "Parking spot updated successfully.";
        } else {
            $error_message = "Failed to update parking spot.";
        }
    } else {
        // Add a new parking spot
        if (add_parking_spot($name, $latitude, $longitude, $hourly_rate)) {
            $success_message = "Parking spot added successfully.";
        } else {
            $error_message = "Failed to add parking spot.";
        }
    }
}

// Handle editing a parking spot
if (isset($_POST['edit_spot'])) {
    $edit_spot_id = $_POST['id'] ?? '';
    $edit_name = $_POST['name'] ?? '';
    $edit_latitude = $_POST['latitude'] ?? '';
    $edit_longitude = $_POST['longitude'] ?? '';
    $edit_hourly_rate = $_POST['hourly_rate'] ?? '';
}

// Handle deleting a parking spot
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_spot'])) {
    $id = $_POST['id'] ?? '';

    if (delete_parking_spot($id)) {
        $success_message = "Parking spot deleted successfully.";
    } else {
        $error_message = "Failed to delete parking spot.";
    }
}

// Fetch parking spots and reservations
$parking_spots = get_all_parking_spots();
$reservations = get_all_reservations();

// Calculate basic statistics
$total_spots = count($parking_spots);
$total_reservations = count($reservations);
$total_revenue = array_sum(array_column($reservations, 'total_cost'));

$current_page = 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --background-color: #ecf0f1;
            --text-color: #2c3e50;
            --light-text: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 1rem 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 1rem;
        }

        nav ul li a {
            color: var(--light-text);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover, nav ul li a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        main {
            padding: 2rem 0;
        }

        h1, h2 {
            margin-bottom: 1rem;
        }

        .admin-section {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 1rem;
            border-radius: 5px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 0.5rem;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: var(--light-text);
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        form {
            display: grid;
            gap: 1rem;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: var(--secondary-color);
            color: var(--light-text);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #27ae60;
        }

        .success-message {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .error-message {
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        footer {
            background-color: var(--primary-color);
            color: var(--light-text);
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">ParkEase Admin</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin.php" class="active">Admin Panel</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Admin Panel</h1>
        
        <!-- Display Success or Error Messages -->
        <?php
        if (isset($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (isset($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        
        <!-- Add/Edit Parking Spot Form -->
        <section class="admin-section">
            <h2><?php echo $edit_spot_id ? 'Edit Parking Spot' : 'Add New Parking Spot'; ?></h2>
            <form action="admin.php" method="POST">
                <!-- Hidden field to store the ID of the parking spot being edited -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_spot_id); ?>">
                
                <input type="text" name="name" placeholder="Spot Name" value="<?php echo htmlspecialchars($edit_name); ?>" required>
                <input type="number" name="latitude" placeholder="Latitude" step="any" value="<?php echo htmlspecialchars($edit_latitude); ?>" required>
                <input type="number" name="longitude" placeholder="Longitude" step="any" value="<?php echo htmlspecialchars($edit_longitude); ?>" required>
                <input type="number" name="hourly_rate" placeholder="Hourly Rate" step="0.01" value="<?php echo htmlspecialchars($edit_hourly_rate); ?>" required>
                
                <button type="submit" name="add_spot"><?php echo $edit_spot_id ? 'Update Parking Spot' : 'Add Parking Spot'; ?></button>
            </form>
        </section>

        <!-- Parking Spots List -->
        <section class="admin-section">
            <h2>Parking Spots</h2>
            <table>
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
                            <!-- Edit Button -->
                            <form action="admin.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($spot['id']); ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($spot['name']); ?>">
                                <input type="hidden" name="latitude" value="<?php echo htmlspecialchars($spot['latitude']); ?>">
                                <input type="hidden" name="longitude" value="<?php echo htmlspecialchars($spot['longitude']); ?>">
                                <input type="hidden" name="hourly_rate" value="<?php echo htmlspecialchars($spot['hourly_rate']); ?>">
                                <button type="submit" name="edit_spot">Edit</button>
                            </form>
                            
                            <!-- Delete Button -->
                            <form action="admin.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($spot['id']); ?>">
                                <button type="submit" name="delete_spot" onclick="return confirm('Are you sure you want to delete this spot?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="admin-section">
            <h2>Recent Reservations</h2>
            <table>
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
                    <?php foreach (array_slice($reservations, 0, 10) as $reservation): ?>
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
        </section>

    </main>

    <footer>
        <p>&copy; 2024 ParkEase. All rights reserved.</p>
    </footer>
</body>
</html>

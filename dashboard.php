<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);
$reservations = get_user_reservations($user_id);

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ParkEase</title>
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
            color: var(--primary-color);
        }

        .dashboard-section {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: var(--light-text);
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
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
            <div class="logo">ParkEase</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <section class="dashboard-section">
            <h2>Your Information</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Account Type:</strong> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
        </section>

        <section class="dashboard-section">
            <h2>Dashboard Actions</h2>
            <div class="dashboard-actions">
                <a href="make_reservation.php" class="btn">Make a New Reservation</a>
                <a href="view_parking_spots.php" class="btn">View Parking Spots</a>
            </div>
        </section>

        <section class="dashboard-section">
            <h2>Your Current Reservations</h2>
            <?php if (empty($reservations)): ?>
                <p>You have no current reservations.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Parking Spot</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['spot_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['end_time']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['status']); ?></td>
                                <td>
                                    <?php if ($reservation['status'] == 'confirmed'): ?>
                                        <a href="cancel_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn" onclick="return confirm('Are you sure you want to cancel this reservation?')">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 ParkEase. All rights reserved.</p>
    </footer>
</body>
</html>
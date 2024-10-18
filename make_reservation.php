<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$user = check_login();
if (!$user) {
    header('Location: login.php');
    exit;
}

$message = '';
$parking_spots = get_all_parking_spots();

// Check if redirected after successful payment
$payment_success = isset($_GET['payment']) && $_GET['payment'] === 'success';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $spot_id = $_POST['spot_id'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if (empty($spot_id) || empty($start_time) || empty($end_time)) {
        $message = '<p class="error">Please fill in all fields.</p>';
    } else {
        $start_datetime = new DateTime($start_time);
        $end_datetime = new DateTime($end_time);

        if ($end_datetime <= $start_datetime) {
            $message = '<p class="error">End time must be after start time.</p>';
        } else {
            $spot = get_parking_spot($spot_id);
            $duration = $start_datetime->diff($end_datetime);
            $hours = $duration->h + ($duration->days * 24);
            $total_cost = $hours * $spot['hourly_rate'];

            // Create the reservation as pending
            $reservation_id = create_reservation($user['id'], $spot_id, $start_time, $end_time, $total_cost, 'pending');
            if ($reservation_id) {
                // Redirect to the checkout page
                header("Location: create_checkout_session.php?reservation_id={$reservation_id}&cost={$total_cost}");
                exit;
            } else {
                $message = '<p class="error">Failed to create reservation. Please try again.</p>';
            }
        }
    }
}

$current_page = 'make_reservation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation - ParkEase</title>
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

        .reservation-form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
        }

        select, input[type="datetime-local"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: var(--primary-color);
            color: var(--light-text);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1rem;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error {
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .success {
            color: var(--secondary-color);
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
            <div class="logo">ParkEase</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="make_reservation.php" class="active">Make Reservation</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Make a Reservation</h1>
        
        <section class="reservation-form">
            <h2>Reserve Your Parking Spot</h2>
            <?php echo $message; ?>
            <form action="make_reservation.php" method="POST">
                <div class="form-group">
                    <label for="spot_id">Select Parking Spot:</label>
                    <select id="spot_id" name="spot_id" required>
                        <option value="">Choose a parking spot</option>
                        <?php foreach ($parking_spots as $spot): ?>
                            <option value="<?php echo $spot['id']; ?>">
                                <?php echo htmlspecialchars($spot['name']) . ' - $' . number_format($spot['hourly_rate'], 2) . '/hour'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <input type="datetime-local" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time:</label>
                    <input type="datetime-local" id="end_time" name="end_time" required>
                </div>
                <button type="submit">Make Reservation</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 ParkEase. All rights reserved.</p>
    </footer>

    <script>
        // Check if the page was loaded with the payment=success query parameter
        <?php if ($payment_success): ?>
            alert('Payment was successful! Your reservation is confirmed.');
        <?php endif; ?>
    </script>
</body>
</html>

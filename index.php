<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$user = check_login();
$parking_spots = get_all_parking_spots();
$current_page = 'home';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase - Find Your Perfect Parking Spot</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero {
            background-color: #777;
            color: var(--light-text);
            text-align: center;
            padding: 4rem 0;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--light-text);
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .map-container {
            height: 500px;
            margin-bottom: 2rem;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .spot-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .spot-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .spot-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .spot-card .price {
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .spot-card .distance {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .features {
            text-align: center;
            margin-bottom: 2rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .feature-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <main>
        <section class="hero">
            <div class="container">
                <h1>Find Your Perfect Spot</h1>
                <p>Effortless parking at your fingertips</p>
                <a href="#available-spots" class="btn">FIND A SPOT NOW</a>
            </div>
        </section>

        <div class="container">
            <section class="map-container">
                <div id="map"></div>
            </section>

            <section id="available-spots">
                <h2>Available Spots</h2>
                <div class="spot-list">
                    <?php foreach ($parking_spots as $spot): ?>
                        <div class="spot-card" data-spot-id="<?php echo $spot['id']; ?>">
                            <h3><?php echo htmlspecialchars($spot['name']); ?></h3>
                            <p class="price">$<?php echo htmlspecialchars($spot['hourly_rate']); ?>/hr</p>
                            <p class="distance"><i class="fas fa-map-marker-alt"></i> <span class="distance-value">Calculating...</span></p>
                            <a href="make_reservation.php?spot_id=<?php echo $spot['id']; ?>" class="btn">RESERVE</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="features">
                <h2>Why Choose ParkEase?</h2>
                <div class="feature-grid">
                    <div class="feature-card">
                        <i class="fas fa-clock feature-icon"></i>
                        <h3>Save Time</h3>
                        <p>Find and reserve spots in seconds</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-money-bill-wave feature-icon"></i>
                        <h3>Save Money</h3>
                        <p>Compare rates to find the best deal</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-map-marked-alt feature-icon"></i>
                        <h3>Easy Navigation</h3>
                        <p>Get directions right to your spot</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script>
        let map;
        let markers = [];
        const spots = <?php echo json_encode($parking_spots); ?>;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 40.7128, lng: -74.0060},
                zoom: 13
            });

            spots.forEach(spot => addMarker(spot));
            fitMapToBounds();
        }

        function addMarker(spot) {
            const marker = new google.maps.Marker({
                position: {lat: parseFloat(spot.latitude), lng: parseFloat(spot.longitude)},
                map: map,
                title: spot.name
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="map-info-window">
                        <h3>${spot.name}</h3>
                        <p>$${spot.hourly_rate}/hr</p>
                        <a href="make_reservation.php?spot_id=${spot.id}" class="btn">Reserve</a>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });

            markers.push(marker);
        }

        function fitMapToBounds() {
            if (markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => bounds.extend(marker.getPosition()));
                map.fitBounds(bounds);
            }
        }

        document.querySelectorAll('.spot-card').forEach(card => {
            card.addEventListener('mouseenter', () => highlightMarker(card.dataset.spotId, true));
            card.addEventListener('mouseleave', () => highlightMarker(card.dataset.spotId, false));
        });

        function highlightMarker(spotId, highlight) {
            const marker = markers.find(m => m.spotId === spotId);
            if (marker) {
                marker.setAnimation(highlight ? google.maps.Animation.BOUNCE : null);
            }
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB99uW764bcbhZg50RBQm9wnW6CYWX0jjk&callback=initMap" async defer></script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
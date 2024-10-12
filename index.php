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

<main class="container">
    <section class="hero parallax-bg">
        <div class="hero-content fade-in">
            <h1 class="glow-text">Find Your Perfect Spot</h1>
            <p class="subtitle">Effortless parking at your fingertips</p>
            <a href="#find-spot" class="btn btn-cta pulse">Find a Spot Now</a>
        </div>
    </section>

    <section id="find-spot" class="find-spot slide-in">
        <div class="search-container">
            <input type="text" id="location-search" placeholder="Enter your destination">
            <button id="search-btn" class="btn">Search</button>
        </div>
    </section>

    <section class="map-and-list">
        <div class="map-container fade-in">
            <div id="map"></div>
        </div>
        <div class="spot-list-container slide-in">
            <h2>Available Spots</h2>
            <div class="spot-list">
                <?php foreach ($parking_spots as $spot): ?>
                    <div class="spot-card" data-spot-id="<?php echo $spot['id']; ?>">
                        <div class="spot-info">
                            <h3><?php echo htmlspecialchars($spot['name']); ?></h3>
                            <p class="price">$<?php echo htmlspecialchars($spot['hourly_rate']); ?>/hr</p>
                            <p class="distance"><i class="fas fa-map-marker-alt"></i> <span class="distance-value">Calculating...</span></p>
                        </div>
                        <a href="make_reservation.php?spot_id=<?php echo $spot['id']; ?>" class="btn btn-reserve">Reserve</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="features fade-in">
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
</main>

<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
<script>
let map;
let markers = [];
const spots = <?php echo json_encode($parking_spots); ?>;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 40.7128, lng: -74.0060},
        zoom: 13,
        styles: [
            {
                "featureType": "all",
                "elementType": "labels.text.fill",
                "stylers": [{"color": "#ffffff"}]
            },
            {
                "featureType": "all",
                "elementType": "labels.text.stroke",
                "stylers": [{"color": "#000000"}, {"lightness": 13}]
            },
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{"color": "#000000"}]
            }
        ]
    });

    spots.forEach(spot => {
        const marker = new google.maps.Marker({
            position: {lat: parseFloat(spot.latitude), lng: parseFloat(spot.longitude)},
            map: map,
            title: spot.name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: "#3498db",
                fillOpacity: 0.8,
                strokeWeight: 0
            }
        });

        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div class="map-info-window">
                    <h3>${spot.name}</h3>
                    <p>$${spot.hourly_rate}/hr</p>
                    <a href="make_reservation.php?spot_id=${spot.id}" class="btn btn-reserve">Reserve</a>
                </div>
            `
        });

        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });

        markers.push(marker);
    });

    if (markers.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.getPosition()));
        map.fitBounds(bounds);
    }
}

document.querySelectorAll('.spot-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        const spotId = card.dataset.spotId;
        const marker = markers.find(m => m.spotId === spotId);
        if (marker) {
            marker.setAnimation(google.maps.Animation.BOUNCE);
        }
    });

    card.addEventListener('mouseleave', () => {
        const spotId = card.dataset.spotId;
        const marker = markers.find(m => m.spotId === spotId);
        if (marker) {
            marker.setAnimation(null);
        }
    });
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const parallax = document.querySelector('.parallax-bg');
    let scrollPosition = window.pageYOffset;
    parallax.style.transform = 'translateY(' + scrollPosition * 0.5 + 'px)';
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB99uW764bcbhZg50RBQm9wnW6CYWX0jjk&callback=initMap" async defer></script>

<?php include 'includes/footer.php'; ?>
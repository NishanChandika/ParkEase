let map;
let markers = [];

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 6.914838, lng: 79.972797}, // Default to New York City, 
        zoom: 13
    });
}

function addMarker(spot) {
    const marker = new google.maps.Marker({
        position: {lat: spot.lat, lng: spot.lng},
        map: map,
        title: spot.name
    });

    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div class="info-window">
                <h3>${spot.name}</h3>
                <p>Price: $${spot.price}/hour</p>
                <button onclick="selectSpot(${spot.id}, '${spot.name}')" class="btn-primary">Select</button>
            </div>
        `
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });

    markers.push(marker);
}

function clearMarkers() {
    for (let marker of markers) {
        marker.setMap(null);
    }
    markers = [];
}

function selectSpot(spotId, spotName) {
    document.getElementById('spot-id').value = spotId;
    document.getElementById('reservation-form').style.display = 'block';
    document.getElementById('reservation-form').scrollIntoView({ behavior: 'smooth' });
    
    // Update form to show selected spot
    const spotInfo = document.createElement('div');
    spotInfo.innerHTML = `<p>Selected Spot: ${spotName}</p>`;
    document.getElementById('parking-form').prepend(spotInfo);
}

// This function would be called when the page loads
window.onload = initMap;
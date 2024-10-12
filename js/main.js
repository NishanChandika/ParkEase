document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();

    // Add event listener for reservation form
    const parkingForm = document.getElementById('parking-form');
    if (parkingForm) {
        parkingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            makeReservation();
        });
    }
});

function searchParking() {
    const location = document.getElementById('search-input').value;
    
    // Show loading indicator
    document.querySelector('.search-container .btn-primary').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    
    // Make AJAX call to backend
    fetch('api/get_parking_spots.php?location=' + encodeURIComponent(location))
        .then(response => response.json())
        .then(data => {
            clearMarkers();
            data.forEach(spot => addMarker(spot));
            map.setCenter({lat: data[0].lat, lng: data[0].lng});
            
            // Reset button text
            document.querySelector('.search-container .btn-primary').innerHTML = '<i class="fas fa-search"></i> Find Parking';
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.search-container .btn-primary').innerHTML = '<i class="fas fa-search"></i> Find Parking';
        });
}

function makeReservation() {
    const form = document.getElementById('parking-form');
    const formData = new FormData(form);

    // Show loading indicator
    form.querySelector('.btn-primary').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch('api/make_reservation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Reservation successful!');
            form.reset();
        } else {
            showErrorMessage(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred. Please try again.');
    })
    .finally(() => {
        form.querySelector('.btn-primary').innerHTML = '<i class="fas fa-check"></i> Confirm Reservation';
    });
}

function showSuccessMessage(message) {
    const successMessage = document.createElement('div');
    successMessage.className = 'success-message';
    successMessage.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.getElementById('parking-form').appendChild(successMessage);

    setTimeout(() => {
        successMessage.remove();
    }, 3000);
}

function showErrorMessage(message) {
    const errorMessage = document.createElement('div');
    errorMessage.className = 'error-message';
    errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    document.getElementById('parking-form').appendChild(errorMessage);

    setTimeout(() => {
        errorMessage.remove();
    }, 3000);
}
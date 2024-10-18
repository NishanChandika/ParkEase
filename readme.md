# ParkEase: Parking Reservation System

ParkEase is a web-based parking reservation system that allows users to find, reserve, and pay for parking spots easily.

## Features

- User registration and authentication
- Search for available parking spots
- Real-time parking spot availability
- Reservation system with date and time selection
- Secure payment processing using Stripe
- Admin panel for managing parking spots and reservations

## Technologies Used

- PHP 7.4+
- MySQL 5.7+
- HTML5, CSS3, JavaScript
- Stripe API for payment processing
- Google Maps API for location services

## Prerequisites

Before you begin, ensure you have met the following requirements:

- XAMPP (or similar local server environment) installed
- Composer installed for PHP dependency management
- Stripe account for payment processing
- Google Maps API key

## Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/NishanChandika/parkease.git
   ```

2. Navigate to the project directory:
   ```
   cd parkease
   ```

3. Install PHP dependencies using Composer:
   ```
   composer install
   ```

4. Create a new MySQL database named `parkease` (or your preferred name).

5. Import the database schema:
   ```
   mysql -u your_username -p parkease < database/schema.sql
   ```

6. Configure the database connection:
   - Open `includes/config.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'parkease');
     ```

7. Set up Stripe API keys:
   - Open `process_payment.php`
   - Replace `YOUR_STRIPE_SECRET_KEY` with your actual Stripe secret key
   - Replace `YOUR_STRIPE_PUBLISHABLE_KEY` in the JavaScript section with your Stripe publishable key

8. Set up Google Maps API:
   - Open `index.php`
   - Replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual Google Maps API key

9. Configure your local server:
   - If using XAMPP, move the project folder to `htdocs` directory
   - Ensure Apache and MySQL services are running

10. Access the application:
    - Open a web browser and navigate to `http://localhost/parkease` (adjust the URL if needed based on your local setup)

## Adding SSL (for production use)

To add SSL to your site when hosting on a Windows virtual machine using XAMPP:

1. Generate a self-signed certificate:
   ```
   cd C:\xampp\apache\bin
   openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt
   ```

2. Move the certificate files:
   ```
   move server.crt C:\xampp\apache\conf\ssl.crt\
   move server.key C:\xampp\apache\conf\ssl.key\
   ```

3. Configure Apache for SSL:
   - Open `C:\xampp\apache\conf\extra\httpd-ssl.conf`
   - Update the VirtualHost section:
     ```apache
     <VirtualHost _default_:443>
         ServerName yourdomainorip
         DocumentRoot "C:/xampp/htdocs/parkease"
         SSLEngine on
         SSLCertificateFile "C:/xampp/apache/conf/ssl.crt/server.crt"
         SSLCertificateKeyFile "C:/xampp/apache/conf/ssl.key/server.key"
     </VirtualHost>
     ```

4. Enable SSL module in `C:\xampp\apache\conf\httpd.conf`:
   Uncomment these lines:
   ```
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   ```

5. Restart Apache from the XAMPP control panel.

## Usage

1. Register a new user account or log in with existing credentials.
2. Search for available parking spots using the map or list view.
3. Select a parking spot and choose your desired reservation time.
4. Complete the payment process using the Stripe integration.
5. View your active reservations in the user dashboard.

## Admin Access

To access the admin panel:
1. Log in with an admin account (you may need to manually set a user's role to 'admin' in the database).
2. Navigate to `http://localhost/parkease/admin.php`





Project Link: [https://github.com/NishanChandika/parkease](https://github.com/NishanChandika/parkease)

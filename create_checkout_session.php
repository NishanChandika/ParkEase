<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require 'vendor/autoload.php'; // Stripe PHP library

\Stripe\Stripe::setApiKey('sk_test_51O94scBodQLTnjkWGyZ554D7YhNcl8umth1UdQBR0Gbau9GmlmpsfbA5LGGQtwDu5ONWGCemEQ1y91Ux35Xbk7Gb00FVhdQYuw'); // Your Secret Key

// Get reservation ID and cost
$reservation_id = $_GET['reservation_id'] ?? '';
$cost = $_GET['cost'] ?? 0;

if (!$reservation_id || !$cost) {
    echo 'Invalid reservation details.';
    exit;
}

try {
    // Create a new Stripe Checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Parking Reservation',
                ],
                'unit_amount' => $cost * 100, // Amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/ParkEase/success.php?reservation_id=' . $reservation_id,
        'cancel_url' => 'http://localhost/ParkEase/cancel.php?reservation_id=' . $reservation_id,
    ]);

    // Redirect to Stripe Checkout page
    header('Location: ' . $session->url);
    exit;

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

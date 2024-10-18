<?php
// After successful payment, redirect back to make_reservation.php with a success flag
header('Location: make_reservation.php?payment=success');
exit;

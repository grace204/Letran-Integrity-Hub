<?php
session_start();

// Default response
$response = array('loggedOut' => false, 'cooldown' => false);

// Check if session exists
if (!isset($_SESSION['user_id'])) {
    // No session found, mark the user as logged out
    $response['loggedOut'] = true;

    // Check if user is on cooldown
    if (isset($_SESSION['last_logout_time'])) {
        // Check if 30 seconds cooldown has not passed
        if (time() < $_SESSION['last_logout_time'] + 3) {
            $response['cooldown'] = true; // Mark as cooldown
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
<?php
session_start();
require '../includes/connect.php'; // adjust this to your actual database connection

$user_id = $_SESSION['user_id']; // make sure the session has user_id

// Check if user exists in the database
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// If user data doesn't exist, destroy session and redirect to login
if (!$user_data) {
    session_destroy();
    header("Location: ../index.php?error=deleted");
    exit();
}
?>
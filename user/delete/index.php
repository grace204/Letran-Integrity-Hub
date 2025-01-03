<?php
// Simula ng file
session_start();

$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

// Properly generated obfuscated ID
$obfuscated_id = "ID" . $random_prefix . $random_suffix . $custom_suffix;

// Dapat naka-login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

include '../../includes/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    // Kunin ang user ID mula sa query string
    $user_id = $_GET['id'];
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // I-delete ang user mula sa database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "User deleted successfully!";
    } else {
        echo "Error deleting user: " . $stmt->error;
    }

    $stmt->close();

    // Enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // Redirect after deletion (using proper concatenation for the header function)
    header("Location: ../?id=" . urlencode($obfuscated_id)); // Corrected redirect
    exit();
} else {
    echo "Invalid request.";
}
?>
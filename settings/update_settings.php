<?php
session_start();

// Redirect kung hindi logged in o walang tamang role
$allowed_roles = ['superadmin', 'admin', 'staff'];
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/connect.php';

//===================
// Start of obfuscated ID processing
//===================
$obfuscated_id = isset($_GET['id']) ? $_GET['id'] : null;

$custom_suffix = "LETRAN"; // Updated suffix
$random_length = 8; // Length of the random prefix and suffix (8 characters each)

if ($obfuscated_id !== null && strpos($obfuscated_id, "ID") === 0 && strpos($obfuscated_id, $custom_suffix) === (strlen($obfuscated_id) - strlen($custom_suffix))) {
    // Strip the "ID" prefix and the custom suffix
    $cleaned_id = str_replace(["ID", $custom_suffix], "", $obfuscated_id);

    // Validate length to ensure it's properly obfuscated
    if (strlen($cleaned_id) < ($random_length * 2)) {
        error_log("Invalid obfuscated ID length: " . $cleaned_id);
        http_response_code(404);
        include('../404/index.php'); // Include the custom 404 page
        exit();
    }

    // Extract data by removing the random prefix and suffix
    $extracted_data = substr($cleaned_id, $random_length, -$random_length);

    // Here you can do something with the extracted data, if needed
    // echo "Extracted Data: " . $extracted_data;

} else {
    // Invalid obfuscated ID pattern
    error_log("Invalid obfuscated ID pattern: " . $obfuscated_id);
    http_response_code(404);
    include('../404/index.php'); // Include the custom 404 page
    exit();
}
//===================
// End of obfuscated ID processing
//===================

// Validate at fetch POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$capture_image = isset($_POST['capture_image']) ? 1 : 0;
$security_settings = isset($_POST['security_settings']) ? 1 : 0;
$sms_url = isset($_POST['sms_url']) ? $_POST['sms_url'] : "";

// Validate user_id
if ($user_id === null) {
    error_log("Invalid user ID");
    header("Location: ../error.php?message=Invalid user ID");
    exit();
}

try {
    // Check kung may settings para sa user
    $stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing settings
        $stmt = $conn->prepare("UPDATE settings SET capture_image = ?, security_settings = ?, sms_url = ? WHERE user_id = ?");
        $stmt->bind_param("iisi", $capture_image, $security_settings, $sms_url, $user_id);
        $stmt->execute();
        echo "Settings updated successfully!";
    } else {
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO settings (user_id, capture_image, security_settings, sms_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $capture_image, $security_settings, $sms_url);
        $stmt->execute();
        echo "New settings created successfully!";
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Error updating settings. Please try again later.";
}

// Redirect sa role-specific settings page
header("Location: ./?id=<?= htmlspecialchars($obfuscated_id) ?>&role_id=" . $_SESSION['role_id']);
exit();
?>
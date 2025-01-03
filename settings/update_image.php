<?php
session_start();

// Redirect if user is not logged in or does not have the appropriate role
$allowed_roles = ['superadmin', 'admin', 'staff'];
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../index.php");
    exit();
}

include '../includes/connect.php';

// Validate URL parameter
$id = null;
if (isset($_GET['id']) && preg_match('/^\d{12}$/', $_GET['id'])) {
    $id = $_GET['id'];
} else {
    header("Location: ../404/");
    exit();
}

// Initialize variables
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : $_SESSION['user_id']; // Use user_id from form or session
$background_image = '';
$favicon = '';
$logo = '';

// Function to handle file uploads
function handleFileUpload(&$target_file, $file_key) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['name']) {
        $target_file = '../assets/' . basename($_FILES[$file_key]['name']);
        move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_file);
    }
}

// Handle file uploads for background image, favicon, and logo
handleFileUpload($background_image, 'background_image');
handleFileUpload($favicon, 'favicon');
handleFileUpload($logo, 'logo');

// Check if settings exist for this user
$result = $conn->query("SELECT * FROM settings WHERE user_id='$user_id'");

if ($result->num_rows > 0) {
    // Update existing settings
    $sql = "UPDATE settings SET user_id='$user_id'";
    if ($background_image) $sql .= ", background_image='$background_image'";
    if ($favicon) $sql .= ", favicon='$favicon'";
    if ($logo) $sql .= ", logo='$logo'";
    $sql .= " WHERE user_id='$user_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Settings updated successfully!";
    } else {
        echo "Error updating settings: " . $conn->error;
    }
} else {
    // Insert new settings
    $sql = "INSERT INTO settings (user_id";

    if ($background_image) {
        $sql .= ", background_image";
    }
    if ($favicon) {
        $sql .= ", favicon";
    }
    if ($logo) {
        $sql .= ", logo";
    }

    $sql .= ") VALUES ('$user_id'";

    if ($background_image) {
        $sql .= ", '$background_image'";
    }
    if ($favicon) {
        $sql .= ", '$favicon'";
    }
    if ($logo) {
        $sql .= ", '$logo'";
    }

    $sql .= ")";

    if ($conn->query($sql) === TRUE) {
        echo "New settings created successfully!";
    } else {
        echo "Error creating new settings: " . $conn->error;
    }
}

header("Location: ./?id=" . $id); // Redirect based on provided id
exit();
?>

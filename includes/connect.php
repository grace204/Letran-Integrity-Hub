<?php
$host = 'localhost';
$db = 'letran_violation_student';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Connection failed.");
    }
} catch (Exception $e) {
    // Redirect to custom error page
    header('Location: ./offline/index.php');
    exit();
}
?>

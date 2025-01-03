<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include "includes/connect.php";

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

$stmt = $conn->prepare("SELECT favicon, logo, security_settings FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$favicon = $settings ? $settings["favicon"] : "";
$logo = $settings ? $settings["logo"] : "";
$security_settings = $settings ? $settings["security_settings"] : 0;
$cache_buster = time();

$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data["name"] : "";
$profile = $user_data ? $user_data["profile"] : "";

if ($role === "superadmin" || $role === "admin" || $role === "staff") {
    // Authorized roles have access
} else {
    header("Location: admin/?id=<?= htmlspecialchars($obfuscated_id) ?>");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan</title>
    <script src="js/instascan.min.js"></script>
    <!-- Adding Google Fonts for a modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <!-- Simple, professional CSS styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
            color: #333;
            position: relative; /* For positioning the back button */
        }

        .back-button {
            position: absolute; /* Position it absolutely */
            top: 20px; /* Distance from the top */
            left: 20px; /* Distance from the left */
            font-size: 4.5rem; /* Size of the arrow */
            color: #1f3c88; /* Arrow color */
            cursor: pointer; /* Pointer on hover */
            transition: color 0.3s; /* Transition for hover effect */
        }

        .back-button:hover {
            color: #ff6f61; /* Change color on hover */
        }

        h1 {
            font-size: 2.5rem;
            color: #1f3c88;
            margin-bottom: 20px;
            font-weight: 600;
            text-transform: uppercase;
        }

        video {
            width: 500px;
            max-width: 90%;
            border: 5px solid #1f3c88;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            video {
                width: 90%;
            }
        }

        footer {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>

    <span class="back-button" onclick="window.history.back();">&#8592;</span> <!-- Arrow Back Button -->

    <h1>QR Code Scanner</h1>

    <video id="preview"></video>

    <footer>
        <p>&copy; 2024 LETRAN INTEGRITY HUB. All rights reserved.</p>
    </footer>

    <script>
        let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
        scanner.addListener('scan', function (content) {
            window.location.href = content;
        });
        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]);
            } else {
                console.error('No cameras found.');
            }
        }).catch(function (e) {
            console.error(e);
        });
    </script>

</body>
</html>

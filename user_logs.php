<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ./index.php");
    exit();
}

include "./includes/connect.php";

// Fetch all user logs
$stmt = $conn->prepare("SELECT * FROM user_logs ORDER BY log_time DESC LIMIT 100"); // You can limit the number of logs displayed
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Logs</title>
    <link href="./vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="./css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #logs-container {
            height: 500px;
            overflow-y: auto;
        }
        .log-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="h3 mb-4 text-gray-800">Real-Time User Logs</h1>

        <div id="logs-container">
            <!-- User logs will be loaded here -->
        </div>
    </div>

    <script>
        // Function to load logs from the server
        function loadLogs() {
            $.ajax({
                url: 'fetch_logs.php',
                method: 'GET',
                success: function(data) {
                    $('#logs-container').html(data);
                }
            });
        }

        // Load logs when the page loads
        $(document).ready(function() {
            loadLogs();
            
            // Set interval to refresh logs every 5 seconds
            setInterval(loadLogs, 5000);
        });
    </script>
</body>
</html>

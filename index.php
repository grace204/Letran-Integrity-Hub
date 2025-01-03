<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {

    // Generate obfuscated ID
    $random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
    $random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
    $custom_suffix = "LETRAN"; // Custom constant suffix
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

    $obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

    if ($_SESSION['role'] == 'admin') {
        header("Location: ./admin/?id=" . $obfuscated_id);
    } elseif ($_SESSION['role'] == 'superadmin') {
        header("Location: ./admin/?id=" . $obfuscated_id);
    } elseif ($_SESSION['role'] == 'student') {
        header("Location: ./student/?id=" . $obfuscated_id);
    } elseif ($_SESSION['role'] == 'tes coordinator') {
        header("Location: /admin/?id=" . $obfuscated_id);
    }
    exit();
}

include './includes/connect.php';

// Default user_id or handle the case where user_id is not set
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;

// Define default values
$background_image = '';
$favicon = '';
$logo = '';
$security_settings = 0;
$cache_buster = time(); // Forces the browser to refresh the favicon

// Prepare and execute the SQL statement
$stmt = $conn->prepare("SELECT favicon, background_image, logo, security_settings FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    $background_image = $settings['background_image'];
    $favicon = htmlspecialchars($settings['favicon']);
    $logo = htmlspecialchars($settings['logo']);
    $security_settings = $settings["security_settings"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/png" href="./assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="./js/instascan.min.js"></script> <!-- Include the QR code scanner library -->
    <?php if (!empty($background_image)) : ?>
    <style>
        body {
            background: url('./assets/<?= htmlspecialchars($background_image) ?>') no-repeat center center fixed;
            background-size: cover;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
        }
    </style>
    <?php endif; ?>
</head>
<body /*class="bg-gradient-primary"*/>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Left side with QR code scanner -->
                        <div class="col-lg-6">
                            <div class="p-5 text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="width: 126%; height: auto;">Scan QR Code Here</h1>
                                <div id="video-container" style="position: relative; width: 126%; height: auto;">
                                    <video id="preview" style="width: 100%; height: auto;" autoplay></video>
                                    <img id="fallback-image" src="./assets/qr-fallback.jpg" alt="QR Code Placeholder" style="width: 100%; height: auto; display: none;">
                                </div>
                            </div>
                        </div>

                            <!-- Right side with the login form -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <?php if ($logo): ?>
                                            <img src="./assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 150px; width: auto; margin-right: 10px;">
                                        <?php endif; ?>
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>

                                    <form class="user" id="loginForm" action="login.php" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user" name="email" placeholder="Email" required>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                        <hr>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="./forgot">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorModalLabel">Error</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="errorMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <?php if ($security_settings): ?><script src="./js/settings.js"></script><?php endif; ?>

    <!-- QR Code Scanner Script -->
    <script>
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    scanner.addListener('scan', function (content) {
        window.location.href = content;
    });

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            showFallbackImage(); // No camera found
        }
    }).catch(function (e) {
        console.error(e);
        showFallbackImage(); // Error accessing camera
    });

    function showFallbackImage() {
        document.getElementById('preview').style.display = 'none';
        document.getElementById('fallback-image').style.display = 'block';
    }

    // Error handling for login
    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            let errorType = urlParams.get('error');
            let errorMessage = '';

            switch (errorType) {
                case 'deleted':
                    errorMessage = 'Your account has been deleted. Please contact support.';
                    break;
                case 'invalid_password':
                    errorMessage = 'Incorrect email or password. Please try again.';
                    break;
                case 'log_failed':
                    errorMessage = 'Failed to log user action. Please contact support.';
                    break;
                default:
                    errorMessage = 'An unknown error occurred. Please try again.';
                    break;
            }

            if (errorMessage) {
                $('#errorMessage').text(errorMessage);
                $('#errorModal').modal('show');
            }
        }
    });
</script>
</body>
</html>
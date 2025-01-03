<?php
session_start();
include '../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email'];

        $zip_password = $email;

        $new_password = bin2hex(random_bytes(4));
        $new_password_hash = hash('sha256', $new_password);

        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $new_password_hash, $email);
        $update_stmt->execute();

        $zip = new ZipArchive();
        $zip_file = tempnam(sys_get_temp_dir(), 'password') . '.zip';

        if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('new_password.txt', "New Password: " . $new_password);
            $zip->setPassword($zip_password);
            $zip->setEncryptionName('new_password.txt', ZipArchive::EM_AES_256);
            $zip->close();

            $_SESSION['message'] = 'Password reset file is ready to download.';
            $_SESSION['message_type'] = 'success';

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="Reset.zip"');
            header('Content-Length: ' . filesize($zip_file));

            readfile($zip_file);
            unlink($zip_file);

            // Refresh the page after download
            echo '<script>setTimeout(function(){ window.location.reload(); }, 1000);</script>';
            exit();
        } else {
            $_SESSION['message'] = 'Failed to create zip file.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Email not found.';
        $_SESSION['message_type'] = 'warning';
    }

    header("Location: ./index.php?id=123123456456");
    exit();
} else {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        $user_id = 2;
    }

    $stmt = $conn->prepare("SELECT capture_image, background_image, favicon, logo, security_settings FROM settings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $background_image = '';
    $favicon = '';
    if ($result->num_rows > 0) {
        $settings = $result->fetch_assoc();
        $background_image = $settings['background_image'];
        $favicon = $settings['favicon'];
        $logo = $settings['logo'];
        $cache_buster = time();
    }

    // Store message details and clear them from the session
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : null;
    unset($_SESSION['message'], $_SESSION['message_type']);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Forgot Password</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../css/bootstrap-4.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <?php if ($background_image) : ?>
    <style>
        body {
            background: url('../assets/<?= htmlspecialchars($background_image) ?>') no-repeat center center fixed;
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

<body>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                    <?php if (!empty($logo)) : ?>
                                    <img src="../assets/<?= $logo ?>" alt="Logo" style="height: 150px; width: auto; margin-right: 10px;">
                                    <?php endif; ?>
                                        <h1 class="h4 text-gray-900 mb-2">Forgot Your Password?</h1>
                                        <p class="mb-4">We get it, stuff happens. Just enter your email address below
                                            and we'll send you a downloaded Zip to reset your password!</p>
                                    </div>
                                    <form action="./index.php?id=123123456456" method="post">
                                        <div class="input-group mb-3">
                                            <input type="email" name="email" class="form-control" placeholder="Enter your email">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-envelope"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-block">Request new password</button>
                                            </div>
                                        </div>
                                    </form>

                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="../register/">Create an Account!</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="../">Already have an account? Login!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <script>
        $(function() {
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            // Only show the toast if there's a message
            <?php if ($message && $message_type): ?>
            var messageType = '<?= $message_type ?>';
            var message = '<?= $message ?>';

            Toast.fire({
                icon: messageType,
                title: message
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php
}
?>

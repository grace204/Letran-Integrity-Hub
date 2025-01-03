<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.php");
    exit();
}

include "../includes/connect.php";

// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; // Example user_id, replace with actual logic

$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

$role = $_SESSION["role"];

$page_id = isset($_GET["id"]) ? $_GET["id"] : null;
$id = null;

// Fetch settings, including sms_url
$result = $conn->query(
    "SELECT capture_image, background_image, favicon, logo, security_settings, sms_url FROM settings WHERE user_id=$user_id"
);

$settings = $result->fetch_assoc();
$capture_image = $settings ? $settings["capture_image"] : 0;
$background_image = $settings ? $settings["background_image"] : "";
$favicon = $settings ? $settings["favicon"] : "";
$logo = $settings ? $settings["logo"] : "";
$security_settings = $settings ? $settings["security_settings"] : 0;
$sms_url = $settings ? $settings["sms_url"] : ""; // Add default empty value for sms_url

// Fetch user data
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data["name"] : "";
$profile = $user_data ? $user_data["profile"] : "";

if ($role === "superadmin" || $role === "admin" || $role === "staff") {
} else {
    header("Location: ../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>");
    exit();
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
    <title>LETRAN INTEGRITY HUB: A WEB-BASED STUDENT VIOLATION MANAGEMENT SYSTEM - Dashboard</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <style>
        #popup-image img {
    border: 1px solid #ccc;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.5);
    border-radius: 5px;
}
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    
                </div>
                <?php if ($logo): ?><img src="../assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;"><?php endif; ?>
                <div class="sidebar-brand-text mx-3">LETRAN INTEGRITY</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <?php if ($role === 'superadmin' || $role === 'admin'): ?>
            <!-- Nav Item - Student Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStudent" aria-expanded="true"
                aria-controls="collapseStudent">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Student Violation</span>
                </a>
                <div id="collapseStudent" class="collapse" aria-labelledby="headingStudent"
                data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="../student/student_list/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>
                        <a class="collapse-item" href="../student/violate/?id=<?= htmlspecialchars($obfuscated_id) ?>">Violations list</a>
                        <a class="collapse-item" href="../scan">Scan Qr Code</a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($role === 'student'): ?>
            <!-- Nav Item - Account -->
            <li class="nav-item">
                    <a class="nav-link" href="edit/?id=<?= htmlspecialchars($user_id) ?>">
                    <i class="fas fa-fw fa-user"></i>
                 <span>Account</span>
                     </a>
            </li>
            <?php endif; ?>
<!-- Nav Item - Utilities Collapse Menu -->
<li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
               aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-users"></i>
            <span>Account</span>
            </a>
            <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
               data-parent="#accordionSidebar">
               <div class="bg-white py-2 collapse-inner rounded">
               <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                  <a class="collapse-item" href="../user/add/?id=<?= htmlspecialchars($obfuscated_id) ?>">Add Account</a>
                  <?php endif; ?>
                  <a class="collapse-item" href="../user/?id=<?= htmlspecialchars($obfuscated_id) ?>">User List</a>
               </div>
            </div>
         </li>
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($name) ?>, <?= htmlspecialchars($role) ?></span>
                                <img class="img-profile rounded-circle"
                                src="<?= $profile ? '../Profile/' . htmlspecialchars($profile) : 'img/undraw_profile.svg'; ?>" alt="User Image">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../profile/?id=<?= $user_id ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="../settings/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="../user/logs/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Content Row -->
    <div class="row">
        <!-- Add your form here -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Settings</h3>
                </div>
                <div class="card-body">
                <form action="update_settings.php?id=<?= htmlspecialchars($obfuscated_id) ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="user_id">User:</label>
        <select name="user_id" id="user_id" class="form-control">
            <?php
            include '../includes/connect.php';
            $result = $conn->query("SELECT id, role, name FROM users WHERE role IN ('superadmin', 'admin')");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['role']} - {$row['name']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- SMS URL Setting -->
    <div class="form-group">
        <label for="sms_url">SMS API URL:</label>
        <input type="text" class="form-control" id="sms_url" name="sms_url" value="<?php echo isset($sms_url) ? $sms_url : ''; ?>" placeholder="Enter SMS API URL">
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

                </div>
            </div>
        </div>

        <!-- File Upload Settings -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Upload Files</h3>
                </div>
                <div class="card-body">
                    <form action="update_image.php?id=249375180920" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                            <label for="user_id_file">User:</label>
                            <select name="user_id" id="user_id_file" class="form-control">
                                <?php
                                // Reusing the same user query for the file upload
                                include '../includes/connect.php';
                                $result = $conn->query("SELECT id, role, name FROM users");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['role']} - {$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="background_image">Upload Background (JPEG):</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="background_image" name="background_image" accept="image/*">
                                    <label class="custom-file-label" for="background_image">Choose file</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="favicon">Upload Favicon (PNG):</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="favicon" name="favicon" accept="image/png">
                                    <label class="custom-file-label" for="favicon">Choose file</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="logo">Upload Logo (PNG):</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png">
                                    <label class="custom-file-label" for="logo">Choose file</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


                    <!-- Content Row -->

                    <div class="row">

                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        
            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../js/search.js"></script>
    <?php if ($security_settings): ?><script src="../js/settings.js"></script><?php endif; ?> 
        <script>
    document.getElementById('user_id').addEventListener('change', function() {
        var userId = this.value;

        if (userId) {
            fetch(`get_user_settings.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Update checkbox states based on the retrieved data.
                    document.getElementById('capture_image').checked = data.capture_image;
                    document.getElementById('security_settings').checked = data.security_settings;

                    // Update Bootstrap switches for the settings.
                    $("[data-bootstrap-switch]").bootstrapSwitch('state', data.capture_image);
                    $("[data-bootstrap-switch]").bootstrapSwitch('state', data.security_settings);
                })
                .catch(error => console.error('Error:', error));
        }
    });

    $(document).ready(function() {
        // Initialize Bootstrap switches when the document is ready.
        $("[data-bootstrap-switch]").bootstrapSwitch();
    });

    $(function() {
        // Initialize custom file input.
        bsCustomFileInput.init();
    });

    // Add this script at the end of your HTML body or in a separate JS file
document.querySelectorAll('.custom-file-input').forEach(input => {
    input.addEventListener('change', function () {
        const label = this.nextElementSibling; // Get the label next to the input
        const fileName = this.files[0] ? this.files[0].name : 'Choose file'; // Get the filename
        label.textContent = fileName; // Set the label text to the filename
    });
});

</script>
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; LETRAN INTEGRITY HUB</span>
            </div>
        </div>
    </footer>
</body>
</html>

<!---needed to activate nav-item
    (<li class="nav-item (here active)">)
    (<a class="(here nav-link)" href="#" data-toggle="collapse" data-target="#collapseStudent" aria-expanded="true")
    (<div id="collapseStudent" class="collapse show" aria-labelledby="headingStudent" data-parent="#accordionSidebar">)
    (<a class="collapse-item (here active)" href="../student/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>)
        -->
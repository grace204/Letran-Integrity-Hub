<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

include '../../includes/connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$role = $_SESSION['role']; // Get the role from the session
// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; // Example user_id, replace with actual logic

// Generate a new obfuscated ID
$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix
$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

// Check if `id` is set and valid
//$page_id = isset($_GET['id']) ? $_GET['id'] : null;
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $suffix = $_POST['suffix'];
    $age = $_POST['age'];
    $birthdate = $_POST['birthdate'];
    $barangay = $_POST['barangay'];
    $profile = $_FILES['profile']['name'];
    $password_hash = hash('sha256', $password);

    // Save profile picture
    $target_dir = "../../profile/";
    $target_file = $target_dir . basename($_FILES["profile"]["name"]);
    if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
        echo "Failed to upload profile picture.";
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (email, name, password, role, middlename, lastname, suffix, age, birthdate, barangay, profile) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssisss", $email, $name, $password_hash, $role, $middlename, $lastname, $suffix, $age, $birthdate, $barangay, $profile);

    if ($stmt->execute()) {
        header("Location: ../?id=" . htmlspecialchars($obfuscated_id));
        exit();
    } else {
        echo "Error adding new user.";
    }
}

// Fetch current settings
$stmt = $conn->prepare("SELECT favicon, logo, security_settings, demo FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$favicon = $settings ? $settings["favicon"] : "";
$logo = $settings ? $settings["logo"] : "";
$security_settings = $settings ? $settings["security_settings"] : 0;
$demo = $settings ? $settings["demo"] : 0;
$cache_buster = time();

// Fetch the user's details
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data['name'] : '';
$profile = $user_data ? $user_data['profile'] : '';

// Check user role
if ($role === 'superadmin' || $role === 'admin') {
  // Continue to the dashboard
} else {
  // Redirect if the user role is not allowed
  header("Location: ../../letran/?id=" . htmlspecialchars($obfuscated_id));
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
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                <?php if ($logo): ?>
                    <img src="../../assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
                <?php endif; ?>
                <div class="sidebar-brand-text mx-3">LETRAN INTEGRITY</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="../../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <?php if ($role === 'superadmin' || $role === 'admin' || $role === 'staff'): ?>
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
                  <a class="collapse-item" href="../../student/student_list/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>
                  <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                  <a class="collapse-item" href="../../student/violate/?id=<?= htmlspecialchars($obfuscated_id) ?>">Violations list</a>
                  <?php endif; ?>
                  <a class="collapse-item" href="../../scan/">Scan Qr Code</a>
               </div>
            </div>
         </li>
         <?php endif; ?>
            <?php if ($role === 'student'): ?>
            <li class="nav-item active">
                <a class="nav-link" href="./?id=<?= htmlspecialchars($user_id) ?>">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Account</span>
                </a>
            </li>
            <?php endif; ?>
            <!-- Nav Item - Utilities Collapse Menu -->
         <li class="nav-item active">
            <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseUtilities"
               aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-users"></i>
            <span>Account</span>
            </a>
            <div id="collapseUtilities" class="collapse show" aria-labelledby="headingUtilities"
               data-parent="#accordionSidebar">
               <div class="bg-white py-2 collapse-inner rounded">
               <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                  <a class="collapse-item active" href="./?id=<?= htmlspecialchars($obfuscated_id) ?>">Add Account</a>
                  <?php endif; ?>
                  <a class="collapse-item" href="../?id=<?= htmlspecialchars($obfuscated_id) ?>">User List</a>
               </div>
            </div>
         </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($name) ?>, <?= htmlspecialchars($role) ?></span>
                                <img class="img-profile rounded-circle" src="<?= $profile ? '../../Profile/' . htmlspecialchars($profile) : 'img/undraw_profile.svg'; ?>" alt="User Image">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../../profile/?id=<?= $user_id ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                                <a class="dropdown-item" href="../../settings/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="../logs/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2 class="text-center mb-4">Add User</h2>
                            <form action="./?id=<?= htmlspecialchars($obfuscated_id) ?>" method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="password">Password:</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="name">Name:</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="middlename">Middle Name:</label>
                                        <input type="text" class="form-control" name="middlename">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="lastname">Last Name:</label>
                                        <input type="text" class="form-control" name="lastname" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="suffix">Suffix:</label>
                                        <input type="text" class="form-control" name="suffix">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="birthdate">Birthdate:</label>
                                        <input type="date" class="form-control" name="birthdate" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="age">Age:</label>
                                        <input type="number" class="form-control" name="age" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="role">Role:</label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="admin">Admin</option>
                                            <option value="tes coordinator">TES Coordinator</option>
                                            <option value="college student council">College Student Council</option>
                                            <option value="registrar">Registrar</option>
                                            <option value="accounting">Accounting</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="barangay">Barangay:</label>
                                        <input type="text" class="form-control" name="barangay" required>
                                    </div>
                                    <div class="form-group">
                                    <label for="profile">Profile Picture:</label>
                                    <input type="file" class="form-control-file" name="profile" accept="image/*">
                                </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Save User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2024</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <a class="btn btn-primary" href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>
</body>

</html>

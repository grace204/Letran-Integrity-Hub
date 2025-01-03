<?php
session_start();
include '../../includes/connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; // Example user_id, replace with actual logic

$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;
$role = $_SESSION['role']; // Logged-in user's role

// Check if `id` is set and valid in the URL
$user_id_from_url = isset($_GET['id']) ? $_GET['id'] : null;

if ($user_id_from_url === null || !is_numeric($user_id_from_url) || $user_id_from_url < 1) {
    // Show 404 error if the `id` parameter is invalid
    http_response_code(404);
    include('../../404/index.php'); // Include the 404 page with the correct path and file extension
    exit();
}

// Restrict access to editing profiles
// If the logged-in user is not an admin and is trying to edit a profile that isn't theirs, show 404 error
if ($role !== 'superadmin' && $user_id_from_url != $user_id) {
    // Unauthorized access attempt
    http_response_code(404);
    include('../../404/index.php'); // Include the 404 page with the correct path and file extension
    exit();
}

// Fetch the user's details from the database based on the `id` from the URL
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id_from_url);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Show 404 error if the user doesn't exist
    http_response_code(404);
    include('../../404/index.php'); // Include the 404 page with the correct path and file extension
    exit();
}

// Check if user is trying to update their details (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the submitted data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $suffix = $_POST['suffix'];
    $age = $_POST['age'];
    $birthdate = $_POST['birthdate'];
    $barangay = $_POST['barangay'];

    // Check if a new profile picture is uploaded
    if ($_FILES['profile']['error'] === 0) {
        $profile = $_FILES['profile']['name'];
        $target_dir = "../../Profile/";
        $target_file = $target_dir . basename($profile);
        // Move the uploaded file to the target directory
        move_uploaded_file($_FILES['profile']['tmp_name'], $target_file);
    } else {
        $profile = $user['profile'];
    }

    // Check if the password is provided and hash it if necessary
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $password_hash = hash('sha256', $password);
    } else {
        $password_hash = $user['password']; // Keep the current password if not changed
    }

    // Prevent changing the role if the user is an admin
    if ($user['role'] === 'admin') {
        $role = 'admin';
    }

    // Update query to save the user's updated details
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, middlename = ?, lastname = ?, suffix = ?, age = ?, birthdate = ?, barangay = ?, profile = ? WHERE id = ?");
    $stmt->bind_param("sssssssissss", $name, $email, $password_hash, $role, $middlename, $lastname, $suffix, $age, $birthdate, $barangay, $profile, $user_id_from_url);
    $stmt->execute();

    // Redirect back to the same edit page after the update
    header("Location: ?id=" . $user_id_from_url); // Redirect to the same user's edit page
    exit();
}

// Fetch current settings for displaying the page
$result = $conn->query("SELECT capture_image, background_image, favicon, logo, security_settings FROM settings WHERE user_id=$user_id");
$settings = $result->fetch_assoc();
$capture_image = $settings ? $settings['capture_image'] : 0;
$background_image = $settings ? $settings['background_image'] : '';
$favicon = $settings ? $settings['favicon'] : '';
$logo = $settings ? $settings['logo'] : '';
$security_settings = $settings ? $settings['security_settings'] : 0;
$cache_buster = time(); // Forces the browser to refresh the favicon

// Fetch the current user's details for the dashboard display
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data['name'] : '';
$profile = $user_data ? $user_data['profile'] : '';

// Allow only valid roles to continue
if ($role === 'superadmin' || $role === 'admin' || $role === 'student' || $role === 'tes coordinator' || $role === 'college student council' || $role === 'registrar' || $role === 'accounting') {
    // Continue to the dashboard or edit form
} else {
    // Redirect unauthorized roles
    header("Location: ../");
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

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../?id=<?= htmlspecialchars($obfuscated_id) ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    
                </div>
                <?php if ($logo): ?><img src="../../assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;"><?php endif; ?>
                <div class="sidebar-brand-text mx-3">LETRAN INTEGRITY</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../?id=<?= htmlspecialchars($obfuscated_id) ?>">
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
                        <a class="collapse-item" href="../../student/student_list/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>
                        <a class="collapse-item" href="../../student/violate/?id=<?= htmlspecialchars($obfuscated_id) ?>">Violations list</a>
                        <a class="collapse-item" href="../../scan/">Scan Qr Code</a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($role === 'student'): ?>
            <!-- Nav Item - Account -->
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
                  <a class="collapse-item" href="../../user/add/?id=<?= htmlspecialchars($obfuscated_id) ?>">Add Account</a>
                  <?php endif; ?>
                  <a class="collapse-item" href="../../user/?id=<?= htmlspecialchars($obfuscated_id) ?>">User List</a>
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
                                src="<?= $profile ? '../../Profile/' . htmlspecialchars($profile) : 'img/undraw_profile.svg'; ?>" alt="User Image">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
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
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
<!-- Content Row -->
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-center mb-4">Edit User</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="middlename">Middle Name:</label>
                    <input type="text" class="form-control" id="middlename" name="middlename" value="<?= htmlspecialchars($user['middlename']) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="lastname">Last Name:</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="suffix">Suffix:</label>
                    <input type="text" class="form-control" id="suffix" name="suffix" value="<?= htmlspecialchars($user['suffix']) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="age">Age:</label>
                    <input type="number" class="form-control" id="age" name="age" value="<?= htmlspecialchars($user['age']) ?>">
                </div>
            </div>
            <div class="form-row">
    <div class="form-group col-md-6">
        <label for="role">Role:</label>
        <select id="role" name="role" class="form-control" disabled>
            <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
        </select>
        <!-- Hidden input to ensure role is submitted with the form -->
        <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
    </div>
                <div class="form-group col-md-6">
                    <label for="barangay">Barangay:</label>
                    <input type="text" class="form-control" id="barangay" name="barangay" value="<?= htmlspecialchars($user['barangay']) ?>">
                </div>
            </div>
            <!--<div class="form-group">
                <label for="profile">Profile Picture:</label>
                <input type="file" class="form-control-file" id="profile" name="profile">
            </div>-->
            <div class="form-group">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="profile" name="profile" accept="image/*">
                <label class="custom-file-label" for="profile">Choose Profile Picture</label>
            </div>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
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
                    <a class="btn btn-primary" href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>
    <script>
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
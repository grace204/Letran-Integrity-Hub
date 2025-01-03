<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit();
}

include '../includes/connect.php'; // Connect to the database

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session
$role = $_SESSION['role']; // Get the role from the session

// Check if `id` is set and valid in the URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id === null || !is_numeric($id) || $id < 1) {
    // Show 404 error if the `id` parameter is invalid
    http_response_code(404);
    include('../404/index.php'); // Include the custom 404 page
    exit();
}

// Fetch user details from the database based on the `id` in the URL
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Show 404 error if the user doesn't exist
    http_response_code(404);
    include('../404/index.php'); // Include the custom 404 page
    exit();
}

// Restrict access: Only the logged-in user or admin can view/edit the profile
if ($role !== 'admin' && $id != $user_id) {
    // If the user is not an admin and is trying to access someone else's profile
    header("Location: ../404"); // Redirect to the dashboard
    exit();
}

// Fetch current settings from the database for the logged-in user
$stmt = $conn->prepare("SELECT favicon, logo, security_settings, demo FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$favicon = $settings ? $settings["favicon"] : ""; // Favicon from settings
$logo = $settings ? $settings["logo"] : ""; // Logo from settings
$security_settings = $settings ? $settings["security_settings"] : 0; // Security settings flag
$demo = $settings ? $settings["demo"] : 0; // Demo flag
$cache_buster = time(); // Used to prevent caching issues for assets

// Fetch the user's details again for display purposes (if needed)
$stmt = $conn->prepare("SELECT name, middlename, lastname, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data['name'] : ''; // User's first name
$full_name = $user_data ? $user_data['name'] . ' ' . $user_data['middlename'] . ' ' . $user_data['lastname'] : ''; // Full name concatenation
$profile = $user_data ? $user_data['profile'] : ''; // Profile image or path

$dashboard_link = '';

// Generate obfuscated ID
$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

// Determine the dashboard link based on user role
if ($role === 'superadmin' || $role === 'admin' || $role === 'staff') {
    $dashboard_link = '../admin/?id=' . htmlspecialchars($obfuscated_id); // Link for admin and staff
} elseif ($role === 'student') {
    $dashboard_link = '../student/?id=' . htmlspecialchars($obfuscated_id); // Link for student, assuming you want to obfuscate here too
} else {
    // Handle unknown role, if necessary
    $dashboard_link = '#'; // Default link or handle error
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
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $dashboard_link; ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    
                </div>
                <?php if ($logo): ?><img src="../assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;"><?php endif; ?>
                <div class="sidebar-brand-text mx-3">LETRAN INTEGRITY</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
            <a class="nav-link" href="<?php echo $dashboard_link; ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
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
                        <a class="collapse-item" href="../student/student_list/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>
                        <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                        <a class="collapse-item" href="../student/violate/?id=<?= htmlspecialchars($obfuscated_id) ?>">Violations list</a>
                        <?php endif; ?>
                        <a class="collapse-item" href="../scan">Scan Qr Code</a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($role === 'student'): ?>
            <!-- Nav Item - Account -->
            <li class="nav-item">
                    <a class="nav-link" href="../student/edit/?id=<?= htmlspecialchars($user_id) ?>">
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
                                <?php if ($role === 'superadmin' || $role === 'admin'): ?>
                                <a class="dropdown-item" href="../settings/?id=<?= htmlspecialchars($obfuscated_id) ?>">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="../user/logs/?id=<?= htmlspecialchars($obfuscated_id) ?>">
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
                    <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" style="height: 150px; width: 150px;"
                              src="../profile/<?php echo $user['profile']; ?>" 
                               alt="Profile Picture">
                        </div>

                        <h3 class="profile-username text-center"><?= $full_name ? $full_name : 'No Name' ?></h3>

                        <p class="text-muted text-center" style="font-weight: 900;" ><?= isset($user['role']) ? $user['role']  : 'Role not available' ?></p>

                        <p class="text-muted text-center"><?= isset($user['age']) ? $user['age'] . ' years old' : 'Age not available' ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($user['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $user['error'] ?>
                            </div>
                        <?php else: ?>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Last Name</th>
                                    <td><?php echo $user['lastname'] ?></td>
                                </tr>
                                <tr>
                                    <th>First Name</th>
                                    <td><?php echo $user['name'] ?></td>
                                </tr>
                                <tr>
                                    <th>Middle Name</th>
                                    <td><?php echo $user['middlename'] ?></td>
                                </tr>
                                <tr>
                                    <th>Suffix</th>
                                    <td><?php echo $user['suffix'] ?></td>
                                </tr>
                                <tr>
                                    <th>Age</th>
                                    <td><?php echo $user['age'] ?></td>
                                </tr>
                                <tr>
                                    <th>Birth Date</th>
                                    <td><?php echo $user['birthdate'] ?></td>
                                </tr>
                                <tr>
                                    <th>Barangay</th>
                                    <td><?php echo $user['barangay'] ?></td>
                                </tr>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
                    <!-- End of Content Row -->

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
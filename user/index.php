<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/connect.php';

// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; // Example user_id, replace with actual logic

$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

$role = $_SESSION['role']; // Get the role from the session

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

// Fetch users from the database based on the role
if ($role === 'superadmin') {
    // Superadmin can see all users except students
    $userQuery = $conn->query("SELECT id, name, lastname, email, role FROM users WHERE role != 'student'");
} elseif ($role === 'admin') {
    // Admin can see all except superadmins and students
    $userQuery = $conn->query("SELECT id, name, lastname, email, role FROM users WHERE role != 'superadmin' AND role != 'student'");
} elseif ($role === 'staff') {
    // Staff can only see other staff members
    $userQuery = $conn->query("SELECT id, name, lastname, email, role FROM users WHERE role = 'staff'");
} else {
    // If not authorized, redirect to a not authorized page
    header("Location: ../404/");
    exit();
}


$users = $userQuery->fetch_all(MYSQLI_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SB Admin 2 - Tables</title>
    <!-- Custom fonts for this template -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <style>
        #suggestions {
    position: absolute; /* Makes the dropdown appear below the input */
    top: 100%; /* Aligns it directly below the input field */
    left: 0;
    width: 100%; /* Matches the width of the search input */
    z-index: 1000; /* Ensures it appears above other elements */
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 5px 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-height: 200px; /* Optional: Limit height if there are many suggestions */
    overflow-y: auto; /* Allows scrolling if there are too many suggestions */
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
            <a class="collapse-item" href="../scan/">Scan Qr Code</a>
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
                  <a class="collapse-item" href="../user/add/?id=<?= htmlspecialchars($obfuscated_id) ?>">Add Account</a>
                  <?php endif; ?>
                  <a class="collapse-item active" href="../user/?id=<?= htmlspecialchars($obfuscated_id) ?>">User List</a>
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
            <form class="form-inline">
              <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
              <i class="fa fa-bars"></i>
              </button>
            </form>
            <!-- Topbar Search -->
            <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
  <div class="input-group">
    <input type="text" id="search-input" class="form-control bg-light border-0 small" placeholder="Search for..."
      aria-label="Search" aria-describedby="basic-addon2" onkeyup="fetchSuggestions()">
    <div class="input-group-append">
      <!--<button class="btn btn-primary" type="button">
        <i class="fas fa-search fa-sm"></i>
      </button>-->
    </div>
  </div>
  <div id="suggestions" class="bg-white border rounded mt-1"></div>
</form>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
              <!-- Nav Item - User Information -->
              <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($name) ?>, <?= htmlspecialchars($role) ?></span>
                                <img class="img-profile rounded-circle" src="<?= $profile ? '../Profile/' . htmlspecialchars($profile) : 'img/undraw_profile.svg'; ?>" alt="User Image">
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
            <!-- DataTales Example -->
            <div class="card shadow mb-4">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">DataTables Example</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                <table class="table table-hover text-nowrap">
    <thead>
        <tr>
            <th>Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['lastname']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <a href="../profile/?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-success"><i class="fa fa-eye"></i> View</a>
                    <a href="./edit/?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-warning"><i class='fa fa-fw fa-edit'></i> Edit</a>
                    
                    <?php if ($role === 'superadmin'): ?>
                        <!-- Superadmin can delete any user, except other superadmins -->
                        <?php if ($user['role'] !== 'superadmin'): ?>
                            <a href="./delete/?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-danger"><i class='fa fa-fw fa-trash'></i> Delete</a>
                        <?php endif; ?>
                    <?php elseif ($role === 'admin'): ?>
                        <!-- Admin can delete users, but not other admins or superadmins -->
                        <?php if ($user['role'] !== 'admin' && $user['role'] !== 'superadmin'): ?>
                            <a href="./delete/?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-danger"><i class='fa fa-fw fa-trash'></i> Delete</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                </div>
              </div>
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <!-- Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>Copyright &copy; Your Website 2020</span>
            </div>
          </div>
        </footer>
        <!-- End of Footer -->
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
    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="../js/search.js"></script>
  </body>
</html>
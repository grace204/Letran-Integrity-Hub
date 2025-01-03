<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; // Example user_id, replace with actual logic

$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

include "../includes/connect.php";

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];
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
// Validate URL parameter
//$id = null;
//if (isset($_GET['id']) && preg_match('/^\d{12}$/', $_GET['id'])) {
//    $id = $_GET['id'];
//} else {
//    header("Location: ../404/");
//    exit();
//}

// Function to log actions
function log_action($conn, $user_id, $user_name, $user_role, $action, $description) {
    $log_sql = "INSERT INTO user_logs (user_id, name, role, action, log_time, description) VALUES (?, ?, ?, ?, NOW(), ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("issss", $user_id, $user_name, $user_role, $action, $description);
    $log_stmt->execute();
}

// Check if user exists
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// If user data doesn't exist, destroy session and redirect
if (!$user_data) {
    session_destroy();
    header("Location: ../index.php?error=deleted");
    exit();
}

//================================
// New Feature: Check if user is logged in from another device
$check_login_sql = "SELECT session_id FROM user_sessions WHERE user_id = ? AND session_id != ?";
$current_session_id = session_id();
$stmt = $conn->prepare($check_login_sql);
$stmt->bind_param("is", $user_id, $current_session_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Logout user if another session is active
    session_destroy();
    header("Location: ../index.php?error=logged_out");
    exit();
}
//================================

//===== Automatic Logout Feature =====
// JavaScript para sa automatic session check
echo '<script>
    function checkSession() {
        fetch("./check_session.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ check: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.loggedOut) {
                if (data.cooldown) {
                    alert("You have been logged out. Please wait 30 seconds before logging in again.");
                } else {
                    alert("You have been logged out due to inactivity or invalid session.");
                }
                window.location.href = "../index.php"; // Redirect to login page
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // Check session every 5 seconds
    setInterval(checkSession, 5000);
</script>';
//===== End of Automatic Logout Feature =====

// Your existing queries here...

$students_count_result = $conn->query("SELECT COUNT(*) as students_count FROM students");
$students_count_row = $students_count_result->fetch_assoc();
$students_count = $students_count_row['students_count'];

// Count pending violations
$pending_count_result = $conn->query("SELECT COUNT(*) as pending_count FROM violations WHERE compliance_status = 'Pending'");
$pending_count_row = $pending_count_result->fetch_assoc();
$pending_count = $pending_count_row['pending_count'];

// Count violations
$violations_count_result = $conn->query("SELECT COUNT(*) as violations_count FROM violations");
$violations_count_row = $violations_count_result->fetch_assoc();
$violations_count = $violations_count_row['violations_count'];

// Count students with violations
$with_violation_result = $conn->query("SELECT COUNT(DISTINCT student_id) as with_violation FROM violations");
$with_violation_row = $with_violation_result->fetch_assoc();
$students_with_violation = $with_violation_row['with_violation'];

// Count total students
$total_students_result = $conn->query("SELECT COUNT(*) as total_students FROM students");
$total_students_row = $total_students_result->fetch_assoc();
$total_students = $total_students_row['total_students'];

// Calculate students without violations
$students_without_violation = $total_students - $students_with_violation;

// Query to count violations per day
$violations_per_day_result = $conn->query("
    SELECT DATE(violation_date) AS violation_day, COUNT(*) AS violation_count
    FROM violations
    WHERE violation_date >= CURDATE() - INTERVAL 30 DAY
    GROUP BY violation_day
    ORDER BY violation_day ASC
");

$violations_per_day = [];
while($row = $violations_per_day_result->fetch_assoc()) {
    $violations_per_day[] = $row;
}

// Convert data for JavaScript
$days = [];
$violations_count = [];
foreach ($violations_per_day as $data) {
    $days[] = date('M d', strtotime($data['violation_day']));
    $violations_count[] = $data['violation_count'];
}

// Example computation (replace with actual calculation)
$total_violations = 50;  // Total number of violations (example lang)
$completed_violations = 30;  // Completed violations (example lang)

// Calculate progress
if ($total_violations > 0) {
    $task_progress = ($completed_violations / $total_violations) * 1.0;
} else {
    $task_progress = 0;  // No violations means 0% progress
}

// Fetch settings for favicon, logo, and security
$stmt = $conn->prepare("SELECT favicon, logo, security_settings FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$favicon = $settings ? $settings["favicon"] : "";
$logo = $settings ? $settings["logo"] : "";
$security_settings = $settings ? $settings["security_settings"] : 0;
$cache_buster = time();

// Fetch user profile
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data["name"] : "";
$profile = $user_data ? $user_data["profile"] : "";

// Role-based access check
if ($role === "superadmin" || $role === "admin" || $role === "tes coordinator") {
    // Authorized roles have access
} else {
    header("Location: admin/?id=<?= htmlspecialchars($obfuscated_id) ?>");
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
      <link
         href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
         rel="stylesheet">
      <link href="../css/sb-admin-2.min.css" rel="stylesheet">
      <link href="../css/blur.css" rel="stylesheet">
      <link rel="icon" type="image/png" href="../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
   </head>
   <div id="blur-overlay"></div>
   <body id="page-top">
      <div id="wrapper">
      <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
         <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./?id=<?= htmlspecialchars($obfuscated_id) ?>">
            <div class="sidebar-brand-icon rotate-n-15">
            </div>
            <?php if ($logo): ?><img src="../assets/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;"><?php endif; ?>
            <div class="sidebar-brand-text mx-3">LETRAN INTEGRITY</div>
         </a>
         <!-- Divider -->
         <hr class="sidebar-divider my-0">
         <!-- Nav Item - Dashboard -->
         <li class="nav-item active">
            <a class="nav-link" href="../admin/?id=<?= htmlspecialchars($obfuscated_id) ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
         </li>
         <!-- Divider -->
         <hr class="sidebar-divider">
         <?php if ($role === 'superadmin' || $role === 'admin' || $role === 'tes coordinator'): ?>
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
            <?php if ($role === 'superadmin' || $role === 'admin'): ?>
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
            <?php endif; ?>
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
            <?php if ($role === 'superadmin' || $role === 'admin'): ?>
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
               <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
               <a href="generate_report.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                  class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
            </div>
            <?php endif; ?>
            <!-- Content Row -->
            <div class="row">
               <!-------------------------------------------------------------------------------------------------------------------------------------->
               <!-- Earnings (Monthly) Card Example -->
               <div class="col-xl-3 col-md-6 mb-4">
                  <div class="card border-left-primary shadow h-100 py-2">
                     <div class="card-body">
                        <div class="row no-gutters align-items-center">
                           <div class="col mr-2">
                              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                 Students
                              </div>
                              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $students_count ?></div>
                           </div>
                           <div class="col-auto">
                              <i class="fas fa-school fa-2x text-gray-300"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Earnings (Monthly) Card Example -->
               <div class="col-xl-3 col-md-6 mb-4">
                  <div class="card border-left-success shadow h-100 py-2">
                     <div class="card-body">
                        <div class="row no-gutters align-items-center">
                           <div class="col mr-2">
                              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                 Violations
                              </div>
                              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $students_with_violation ?></div>
                           </div>
                           <div class="col-auto">
                              <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Earnings (Monthly) Card Example --> 
               <div class="col-xl-3 col-md-6 mb-4">
                  <div class="card border-left-info shadow h-100 py-2">
                     <div class="card-body">
                        <div class="row no-gutters align-items-center">
                           <div class="col mr-2">
                              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Violation percentage</div>
                              <div class="row no-gutters align-items-center">
                                 <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?= number_format($task_progress, 1) ?>%</div>
                                 </div>
                                 <div class="col">
                                    <div class="progress progress-sm mr-2">
                                       <div class="progress-bar bg-info" role="progressbar"
                                          style="width: <?= $task_progress ?>%" aria-valuenow="<?= $task_progress ?>"
                                          aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-auto">
                              <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Pending Requests Card Example -->
               <div class="col-xl-3 col-md-6 mb-4">
                  <div class="card border-left-warning shadow h-100 py-2">
                     <div class="card-body">
                        <div class="row no-gutters align-items-center">
                           <div class="col mr-2">
                              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                 Pending Requests
                              </div>
                              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_count ?></div>
                           </div>
                           <div class="col-auto">
                              <i class="fas fa-comments fa-2x text-gray-300"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-------------------------------------------------------------------------------------------------------------------------------------->
            </div>
            <div class="row">
               <!-- Area Chart -->
               <!-- Area Chart -->
               <div class="col-xl-8 col-lg-7">
                  <div class="card shadow mb-4">
                     <!-- Card Header -->
                     <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Violations Overview</h6>
                     </div>
                     <!-- Card Body -->
                     <div class="card-body">
                        <div class="chart-area">
                           <canvas id="myAreaChart"></canvas>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Pie Chart -->
               <div class="col-xl-4 col-lg-5">
                  <div class="card shadow mb-4">
                     <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Students Violations</h6>
                     </div>
                     <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                           <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                           <span class="mr-2">
                           <i class="fas fa-circle text-primary"></i> No Violations
                           </span>
                           <span class="mr-2">
                           <i class="fas fa-circle text-success"></i> Violations
                           </span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row">
            </div>
         </div>
      </div>
      <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
      </a>
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
      <script src="../vendor/chart.js/Chart.min.js"></script>
      <script src="../js/search.js"></script>
      <script src="../js/blur.js"></script>
      <?php include '../js/charr.php'; ?>
      <?php if ($security_settings): ?><script src="../js/settings.js"></script><?php endif; ?>
      <footer class="sticky-footer bg-white">
         <div class="container my-auto">
            <div class="copyright text-center my-auto">
               <span>Copyright &copy; LETRAN INTEGRITY HUB</span>
            </div>
         </div>
      </footer>
   </body>
</html>
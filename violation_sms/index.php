<?php 
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

// Assuming $user_id is available (replace this with your actual user_id logic)
$user_id = $_SESSION['user_id']; 

$random_prefix = bin2hex(random_bytes(4));
$random_suffix = bin2hex(random_bytes(4));
$custom_suffix = "LETRAN";

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

include "../includes/connect.php";

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

// Validate URL parameter for student_id
$student_id = null;

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Check if student_id exists in the database
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        header("Location: ../404/");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ../404/");
    exit();
}

// Fetch settings
$stmt = $conn->prepare("SELECT favicon, logo, security_settings, sms_url FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

// Set default values if settings are missing
$favicon = $settings ? $settings["favicon"] : "";
$logo = $settings ? $settings["logo"] : "";
$security_settings = $settings ? $settings["security_settings"] : 0;

// Set the SMS URL, use default if empty
$sms_url = $settings ? $settings["sms_url"] : ""; 
$cache_buster = time();

// Fetch user details
$stmt = $conn->prepare("SELECT name, profile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$name = $user_data ? $user_data["name"] : "";
$profile = $user_data ? $user_data["profile"] : "";

// Check if role is authorized
if (!in_array($role, ["superadmin", "admin", "student", "staff"])) {
    header("Location: admin/?id=<?= htmlspecialchars($obfuscated_id) ?>");
    exit();
}

// Fetch student details
$student_sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
} else {
    die("Student not found.");
}

// Fetch the student's contact info from the users table
$user_sql = "SELECT contact FROM users WHERE role = 'student' AND student_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $contact_number = $user['contact'];
} else {
    die("User contact not found.");
}

// Handle form submission for adding violations
$message_modal = ""; 
$modal_id = "";      

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $education_level = $_POST['education_level'];
    $violation_type = $_POST['violation_type'];
    $violation_description = $_POST['violation_description'];

    // Insert violation into the database
    $add_violation_sql = "INSERT INTO violations (student_id, education_level, violation_type, violation_description) 
                          VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($add_violation_sql);
    $stmt->bind_param("ssss", $student_id, $education_level, $violation_type, $violation_description);
    
    if ($stmt->execute() === TRUE) {
        $message_modal = "Violation added successfully!";
        $modal_id = "successModal";

        // Send SMS after adding the violation
        $sms_message = "Dear Parent Of, " . $student['student_name'] . " " . $student['middlename'] . " " . $student['lastname'] . " " . $student['suffix'] . ", your child has received a violation: $violation_type. Description: $violation_description. For inquiries, please contact Colegio de San Juan de Letran - Manaoag at (075) 529 0121.";
        $sms_result = sendSMS($contact_number, $sms_message);

        if ($sms_result === true) {
            $message_modal .= "<br>SMS sent successfully to $contact_number!";
        } else {
            $message_modal .= "<br>Failed to send SMS. Error: $sms_result";
            $modal_id = "errorModal";
        }
    } else {
        $message_modal = "Error: " . $conn->error;
        $modal_id = "errorModal";
    }
}

function sendSMS($phone, $message) {
    global $sms_url;  
    $ch = curl_init($sms_url . "?phone=" . urlencode($phone) . "&message=" . urlencode($message));
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code == 200) ? true : $response;
}
$stmt = $conn->prepare("SELECT level FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id); // Replace `$student_id` with the correct variable
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$education_level = $row['level'];

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
            <li class="nav-item active">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseStudent" aria-expanded="true"
                aria-controls="collapseStudent">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Student Violation</span>
                </a>
                <div id="collapseStudent" class="collapse show" aria-labelledby="headingStudent"
                data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item active" href="../student/student_list/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>
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
            <?php if ($role === 'superadmin' || $role === 'admin'): ?>
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
                    <a class="collapse-item" href="../user/add/?id=<?= htmlspecialchars($obfuscated_id) ?>">Add Account</a>
                    <a class="collapse-item" href="../user/?id=<?= htmlspecialchars($obfuscated_id) ?>">User List</a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
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
                                <a class="dropdown-item" href="../user/logs/">
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
                     <!-- Modal Structure -->
<div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel"><?= ($modal_id == "successModal") ? "Success" : "Error" ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $message_modal ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid d-flex justify-content-center mt-5">
    <div class="col-lg-6 mb-4">
        <!-- Violation Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Add Violation of <?php echo htmlspecialchars($student['student_name'] . ' ' . $student['lastname']); ?>
                </h6>
            </div>
            <div class="card-body">
            <form method="POST" action="">
            <div class="form-group">
    <label for="level">Education Level:</label>
    <select name="level" id="level" class="form-control" required>
        <option value="" disabled <?= empty($education_level) ? 'selected' : '' ?>>Select Education Level</option>
        <option value="college" <?= $education_level === 'college' ? 'selected' : '' ?>>College</option>
        <option value="basic_education" <?= $education_level === 'basic_education' ? 'selected' : '' ?>>Basic Education</option>
    </select>
</div>
    <div class="form-group">
        <label for="violation_type">Violation Type:</label>
        <select name="violation_type" id="violation_type" class="form-control" required>
            <option value="" disabled selected>Select Violation Type</option>
        </select>
    </div>

    <div class="form-group">
        <label for="violation_description">Description:</label>
        <textarea name="violation_description" id="violation_description" rows="4" class="form-control" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Add Violation</button>
</form>
            </div>
        </div>
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
                    <a class="btn btn-primary" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
    const violationOptions = {
        college: [
            { value: "Cheating", text: "Cheating" },
            { value: "Vandalism", text: "Vandalism" },
            { value: "Disrespect", text: "Disrespect" },
            { value: "Unexcused Absence", text: "Unexcused Absence" },
            { value: "Dress Code Violation", text: "Dress Code Violation" },
            { value: "Others", text: "Others" }
        ],
        basic_education: [
            { value: "Bullying", text: "Bullying" },
            { value: "Cheating", text: "Cheating" },
            { value: "Misbehavior", text: "Misbehavior" },
            { value: "Disrespect", text: "Disrespect" },
            { value: "Tardiness", text: "Tardiness" },
            { value: "Others", text: "Others" }
        ]
    };

    document.getElementById("level").addEventListener("change", function () {
        const violationTypeSelect = document.getElementById("violation_type");
        const selectedLevel = this.value;

        // Clear existing options
        violationTypeSelect.innerHTML = `<option value="" disabled selected>Select Violation Type</option>`;

        // Add new options based on the selected education level
        if (violationOptions[selectedLevel]) {
            violationOptions[selectedLevel].forEach(option => {
                const opt = document.createElement("option");
                opt.value = option.value;
                opt.textContent = option.text;
                violationTypeSelect.appendChild(opt);
            });
        }
    });
</script>
    <script>
    // Show the modal if needed
    <?php if (!empty($modal_id)) : ?>
        $('#<?= $modal_id ?>').modal('show');
    <?php endif; ?>
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
<?php
$conn->close();
?>



<!---needed to activate nav-item
    (<li class="nav-item (here active)">)
    (<a class="(here nav-link)" href="#" data-toggle="collapse" data-target="#collapseStudent" aria-expanded="true")
    (<div id="collapseStudent" class="collapse show" aria-labelledby="headingStudent" data-parent="#accordionSidebar">)
    (<a class="collapse-item (here active)" href="../student/?id=<?= htmlspecialchars($obfuscated_id) ?>">Student Lists</a>)
        -->
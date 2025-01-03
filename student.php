<?php
session_start();
//if (!isset($_SESSION["user_id"])) {
//    header("Location: ./index.php");
//    exit();
//}

include './includes/connect.php';

//$user_id = $_SESSION["user_id"];
//$role = $_SESSION["role"];

// Get the student_id from the URL
$student_id = $_GET['id'];

// Fetch student details
$student_sql = "SELECT * FROM students WHERE student_id = $student_id";
$student_result = $conn->query($student_sql);

if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
} else {
    die("Student not found."); //if output not found showing in table
}

// Fetch student violations
$violation_sql = "SELECT * FROM violations WHERE student_id = $student_id ORDER BY violation_date DESC";
$violation_result = $conn->query($violation_sql);
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
    <link href="./vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="./css/flip.css" rel="stylesheet">
    <link rel="stylesheet" href="css/docs.css">
    <link rel="stylesheet" href="css/jquery.ui.css">
    <link rel="icon" type="image/png" href="./assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <style>
    /* Main container for centering the content */
    .centered-content {
        text-align: center;
        margin-bottom: 20px;
    }

    /* Styling for individual elements (Name, Email, QR Code) */
    .centered-content p {
        font-size: 1rem;
        margin: 5px 0;
        text-align: center;
    }

    .qr-code img {
        border: 2px solid #ccc;
        border-radius: 8px;
        width: 150px;
        height: 150px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin: 15px 0;
    }

    /* For the table */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th, .table td {
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
    }

    .table th {
        background-color: #1f3c88;
        color: white;
        text-align: center;
    }

    .table td {
        text-align: center;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-striped tbody tr:hover {
        background-color: #f1f1f1;
    }

    /* Card for overall layout */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        width: 80%;
        margin: 0 auto;
        background-color: white;
    }

    .button {
        background-color: #1f3c88;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 8px;
        font-size: 1rem;
    }

    .button:hover {
        background-color: #ff6f61;
        transition: 0.3s;
    }
</style>


</head>

<body id="page-top">
<!-- Modal for the flipbook (simplified) -->
<div class="modal fade" id="flipbookModal" tabindex="-1" role="dialog" aria-labelledby="flipbookModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
    <div class="modal-content" style="border: none;">
      <div class="modal-body" style="padding: 0;">

        <div id="canvas">
            <div id="book-zoom">
                <div class="sample-docs">
                    <div ignore="1" class="tabs"><div class="left">  </div> <div class="right"> </div></div>
                    <div class="hard"></div>
                    <div class="hard"></div>
                    <div class="hard p37"></div>
                    <div class="hard p38"></div>
                </div>
            </div>
        </div>
      </div>
      <!--<div>
        <button type="button" class="btn btn-tiz" data-dismiss="modal">Close</button>
      </div>-->
    </div>
  </div>
</div>
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <a class="back-button" href="./">&#8592;</a> <!-- Arrow Back Button -->
                    <div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Student Details</h6>
            </div>
            <div class="card-body">
                <!-- Centered Content (Name, Email, QR Code) -->
                <div class="centered-content">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['student_name'] . ' ' . $student['middlename']) . ' ' . $student['lastname'] . ' ' . $student['suffix']; ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['student_email']); ?></p>
                    <div class="qr-code">
                        <img src="./qr_codes/<?php echo htmlspecialchars($student['qr_code']); ?>" alt="QR Code">
                    </div>
                </div>

                <!-- Violation History -->
                <h2>Violation History</h2>
                <?php if ($violation_result->num_rows > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Violation Type</th>
                                <th>Description</th>
                                <th>Date and Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($violation = $violation_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($violation['violation_type']); ?></td>
                                    <td><?php echo htmlspecialchars($violation['violation_description']); ?></td>
                                    <td>
                                        <?php 
                                        $formatted_date = date("F j, Y", strtotime($violation['violation_date']));
                                        $formatted_time = date("h:i A", strtotime($violation['violation_date'])); 
                                        echo "$formatted_date at $formatted_time"; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No violations recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="centered-content">
    <!-- Student handbook downloads -->
    <a href="./docs/Collegiate_Student_Handbook_2022.pdf" download="Collegiate_Student_Handbook_2022.pdf">
        <button class="button">Download Collegiate Student Handbook 2022</button>
    </a>

    <a href="./docs/college-discipline-manual.pdf" download="College_Discipline_Manual.pdf">
        <button class="button">Download College Discipline Manual</button>
    </a>
</div>
    <script src="./vendor/jquery/jquery.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="./js/sb-admin2.min.js"></script>
    <script type="text/javascript" src="./extras/jquery.min.js"></script>
    <script type="text/javascript" src="./extras/jquery-ui.min.js"></script>

</body>
</html>
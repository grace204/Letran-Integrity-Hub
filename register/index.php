<?php
session_start();
include '../includes/connect.php';

// Include the QR code library
require_once '../phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_id = $_POST['school_id'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $suffix = $_POST['suffix'];
    $contact = $_POST['contact'];
    $level = $_POST['level'];
    $age = $_POST['age'];
    $birthdate = $_POST['birthdate'];
    $barangay = $_POST['barangay'];
    $password_hash = hash('sha256', $password);
    $profile = isset($_FILES['profile']) ? $_FILES['profile']['name'] : '';

    // Check if the email already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Redirect with error for existing account
        header("Location: ./index.php?error=exists");
        exit();
    }

    // Check if the student details already exist in students table
$student_check_stmt = $conn->prepare("
SELECT * FROM students WHERE student_name = ? AND middlename = ? AND lastname = ? AND contact = ?
");
$student_check_stmt->bind_param("ssss", $name, $middlename, $lastname, $contact);
$student_check_stmt->execute();
$student_check_result = $student_check_stmt->get_result();

if ($student_check_result->num_rows > 0) {
// Redirect with error for existing student details
header("Location: ./index.php?error=existing_student");
exit();
}

    if (!empty($profile)) {
        $target_dir = "../Profile/";
        $target_file = $target_dir . basename($profile);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($target_file, PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                echo "Profile picture uploaded successfully.<br>";
            } else {
                echo "Failed to upload profile picture.<br>";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.<br>";
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (school_id, email, name, password, role, level, middlename, lastname, suffix, contact, age, birthdate, barangay, profile) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssisssss", $school_id, $email, $name, $password_hash, $role, $level, $middlename, $lastname, $suffix, $contact, $age, $birthdate, $barangay, $profile);


    if ($stmt->execute()) {
        $user_id = $conn->insert_id;

        if ($role == 'student') {
            $qr_text = "./student.php?id=" . $user_id;
            $qr_code_path = "../qr_codes/" . $user_id . ".png";
            if (!file_exists('../qr_codes')) {
                mkdir('../qr_codes', 0777, true);
            }
            QRcode::png($qr_text, $qr_code_path);

            $stmt = $conn->prepare("INSERT INTO students (school_id, student_id, student_name, middlename, lastname, suffix, contact, level, student_email, qr_code) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssssss", $school_id, $user_id, $name, $middlename, $lastname, $suffix, $contact, $level, $email, $qr_code_path);


            if ($stmt->execute()) {
                $stmt = $conn->prepare("UPDATE users SET student_id = ? WHERE id = ?");
                $stmt->bind_param("ii", $user_id, $user_id);
                $stmt->execute();

                header("Location: ../?id=" . $user_id);
                exit();
            } else {
                echo "Error adding user to students table.<br>";
            }
        } else {
            echo "User is added but no QR code generated since the role is $role.<br>";
        }
    } else {
        echo "Error adding new user.<br>";
    }
}

// Default user_id or handle the case where user_id is not set
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;

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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Register</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <?php if (!empty($background_image)) : ?>
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
  <body /*class="bg-gradient-primary"*/>
  <div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
      <div class="card-body p-0">
        <div class="row">
          <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
          <div class="col-lg-7">
            <div class="p-5">
              <div class="text-center">
                <?php if (!empty($logo)) : ?>
                <img src="../assets/<?= $logo ?>" alt="Logo" style="height: 150px; width: auto; margin-right: 10px;">
                <?php endif; ?>
                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
              </div>
              <form id="residentForm" class="user" action="./index.php" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="text" class="form-control" id="name" name="name" placeholder="First Name" required>
                  </div>
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last Name" required>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle Name">
                  </div>
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Suffix">
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="text" class="form-control" id="school_id" name="school_id" placeholder="School ID" required pattern="\d{2}-\d{5}" title="Please enter a valid School ID in the format 12-34567">
                  </div>
                  <div class="col-sm-6">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="text" class="form-control" id="contact" name="contact" placeholder="Parent Contact" required>
                  </div>
                  <div class="col-sm-6">
                  <select name="level" id="level" class="form-control" required>
                      <option value="" disabled selected>Select Education Level</option>
                      <option value="College">College</option>
                      <option value="Basic Education">Basic Education</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                  </div>
                  <div class="col-sm-6">
                    <input type="password" class="form-control" id="exampleRepeatPassword" placeholder="Repeat Password" required>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6">
                    <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                  </div>
                  <div class="col-sm-6">
                    <input type="number" class="form-control" id="age" name="age" placeholder="Age" required>
                  </div>
                </div>
                <div class="form-group row">
                  <input type="hidden" id="role" name="role" value="student">
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="barangay" name="barangay" placeholder="Barangay" required value="Brgy. " oninput="ensurePrefix()">
                  </div>
                  <div class="col-sm-6">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="profile" name="profile" accept="image/*">
                      <label class="custom-file-label" for="profile">Choose Profile Picture</label>
                    </div>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary btn-user btn-block" name="insert">Register Account</button>
              </form>
              <hr>
              <div class="text-center">
                <a class="small" href="../forgot">Forgot Password?</a>
              </div>
              <div class="text-center">
                <a class="small" href="http://localhost/letran/">Already have an account? Login!</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for error messages -->
  <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="errorModalLabel">Error</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="errorMessage">
          <!-- Error message will be inserted here dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="../js/sb-admin-2.min.js"></script>
  <?php if ($security_settings): ?><script src="../js/settings.js"></script><?php endif; ?>
  <script>
    $(document).ready(function () {
        // Check if error parameter exists in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            let errorType = urlParams.get('error');
            let errorMessage = '';
    
            // Different error messages based on error type
            if (errorType === 'exists') {
                errorMessage = 'This account already exists. Please try logging in or use a different email.';
            } else if (errorType === 'deleted') {
                errorMessage = 'Your account has been deleted. Please contact support.';
            } else if (errorType === 'invalid_password') {
                errorMessage = 'Incorrect email or password. Please try again.';
            } else if (errorType === 'existing_student') {  // New condition for existing student
                errorMessage = 'A student with these details already exists. Please contact support for assistance.';
            }
    
            // Set the error message and show the modal
            if (errorMessage) {
                $('#errorMessage').text(errorMessage);
                $('#errorModal').modal('show');
            }
        }
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
  </body>
</html>
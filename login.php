<?php
session_start();
include './includes/connect.php';

// Check if POST data exists
if (isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password for comparison
    $password_hash = hash('sha256', $password);

    // Prepare SQL statement with a parameterized query
    $sql = "SELECT * FROM users WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $email, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch user data
            $user = $result->fetch_assoc();

            // Check if the account is deleted
            if ($user['is_deleted'] == 1) {
                // Redirect back to login with error for deleted account
                header("Location: ./index.php?error=deleted");
                exit();
            }

            // Check if user is in cooldown
            if (isset($_SESSION['last_logout_time'])) {
                // Check if 30 seconds cooldown has passed
                if (time() < $_SESSION['last_logout_time'] + 3) {
                    header("Location: ./index.php?error=cooldown"); // Redirect to login with cooldown error
                    exit();
                } else {
                    // Cooldown has passed, clear last logout time
                    unset($_SESSION['last_logout_time']);
                }
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Get user data for logging
            $user_id = $user['id'];
            $user_email = $user['email'];
            $user_name = $user['name'];
            $user_role = $user['role'];

            // Delete old session entries for this user (cleanup step)
            $delete_old_sessions = "DELETE FROM user_sessions WHERE user_id = ?";
            $delete_stmt = $conn->prepare($delete_old_sessions);
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
            }

            // Log user action
            $log_sql = "INSERT INTO user_logs (user_id, email, name, role, action, log_time) VALUES (?, ?, ?, ?, 'Logged in', NOW())";
            $log_stmt = $conn->prepare($log_sql);

            if ($log_stmt) {
                $log_stmt->bind_param("isss", $user_id, $user_email, $user_name, $user_role);

                // Execute and check if insert was successful
                if ($log_stmt->execute()) {
                    // Insert user session
                    $session_id = session_id(); // Get the current session ID
                    $session_sql = "INSERT INTO user_sessions (user_id, session_id, expiry_time) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))";
                    $session_stmt = $conn->prepare($session_sql);

                    if ($session_stmt) {
                        $session_stmt->bind_param("is", $user_id, $session_id);
                        $session_stmt->execute();
                    }

                    // Generate obfuscated ID
$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix
$user_id = $user['id']; // Assuming user ID is from the database

$obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix;

// Redirect to appropriate dashboard
if ($user['role'] == 'superadmin' || $user['role'] == 'admin' || $user['role'] == 'tes coordinator') {
    header("Location: ./admin/index.php?id=" . $obfuscated_id);
} else if ($user['role'] == 'student') {
    header("Location: ./student/?id=" . $obfuscated_id); // Added obfuscated ID for student as well
}
exit();

                } else {
                    // Failed to log the user action
                    header("Location: ./index.php?error=log_failed");
                    exit();
                }
            } else {
                echo "Error preparing log statement.";
            }
        } else {
            // Redirect back to login with error for invalid password
            header("Location: ./index.php?error=invalid_password");
            exit();
        }
    } else {
        echo "Error preparing statement.";
    }
} else {
    echo "Incomplete form submission.";
}
?>

<?php
include '../includes/connect.php'; // Your database connection file
session_start(); // Ensure session is started

// Generate a random obfuscated ID regardless of user session
$random_prefix = bin2hex(random_bytes(4)); // 8-character random string as a prefix
$random_suffix = bin2hex(random_bytes(4)); // 8-character random string as a suffix
$custom_suffix = "LETRAN"; // Custom constant suffix

// Combine all parts to create the obfuscated ID
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $obfuscated_id = "ID" . $random_prefix . $user_id . $random_suffix . $custom_suffix; // Include user_id in the obfuscated ID
} else {
    // Generate a fallback obfuscated ID
    $obfuscated_id = "ID" . $random_prefix . $random_suffix . $custom_suffix; // No user ID
}

// Rest of your search suggestions logic
if (isset($_GET['q'])) {
    $query = strtolower(trim($_GET['q'])); // Trim whitespace and lowercase
    $query = mysqli_real_escape_string($conn, $query);

    $suggestions = [];

    // Search in students and users table
    $sql = "SELECT student_name COLLATE utf8mb4_general_ci AS name, 
                   middlename COLLATE utf8mb4_general_ci AS middlename, 
                   lastname COLLATE utf8mb4_general_ci AS lastname, 
                   user_id 
            FROM students 
            WHERE student_name LIKE '%$query%' COLLATE utf8mb4_general_ci 
               OR lastname LIKE '%$query%' COLLATE utf8mb4_general_ci 
            UNION
            SELECT name COLLATE utf8mb4_general_ci AS name, 
                   middlename COLLATE utf8mb4_general_ci AS middlename, 
                   lastname COLLATE utf8mb4_general_ci AS lastname, 
                   id AS user_id 
            FROM users 
            WHERE name LIKE '%$query%' COLLATE utf8mb4_general_ci 
               OR lastname LIKE '%$query%' COLLATE utf8mb4_general_ci 
            LIMIT 5";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $fullname = htmlspecialchars($row['name'] . ' ' . $row['middlename'] . ' ' . $row['lastname']);
            $user_id = htmlspecialchars($row['user_id']);

            // Add the student/user to suggestions
            $suggestions[] = "<div onclick=\"selectSuggestion('$fullname')\">
                                <a href='../profile/?id=$user_id' class='d-block p-2'>
                                    $fullname
                                </a>
                              </div>";
        }
    }

    // Custom page-based suggestions with obfuscated_id
    $pages = [
        'student list' => "../student/student_list/?id=" . htmlspecialchars($obfuscated_id),
        'violation list' => "../student/violate/?id=" . htmlspecialchars($obfuscated_id),
        'scan qr code' => "../scan/",
        'add user' => "../user/add/?id=" . htmlspecialchars($obfuscated_id),
        'user list' => "../user/?id=" . htmlspecialchars($obfuscated_id),
        'settings' => "../settings/?id=" . htmlspecialchars($obfuscated_id),
        'activity log' => "../user/logs/?id=" . htmlspecialchars($obfuscated_id)
    ];

    // Match user input with page suggestions
    foreach ($pages as $key => $link) {
        if (strpos($key, $query) !== false) {
            $suggestions[] = "<div onclick=\"selectSuggestion('$key')\">
                                <a href='$link' class='d-block p-2'>
                                    " . htmlspecialchars(ucfirst($key)) . "
                                </a>
                              </div>";
        }
    }

    // Output all suggestions or a "no matches" message
    if (!empty($suggestions)) {
        foreach ($suggestions as $suggestion) {
            echo $suggestion;
        }
    } else {
        echo "<div class='p-2'>No matches found</div>";
    }
}
?>

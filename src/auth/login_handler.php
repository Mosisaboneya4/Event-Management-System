<?php
session_start();
require_once '../../config/database.php';

function validateLogin($username, $password, $role) {
    try {
        $conn = getDatabaseConnection();
        
        // Prepare SQL to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'] ?? $user['username'];
                $_SESSION['last_name'] = $user['last_name'] ?? '';
                $_SESSION['email'] = $user['email'];

                // Update last login time
                $update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $update_login->bind_param("i", $user['user_id']);
                $update_login->execute();

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header("Location: ../../public/admin/dashboard.php");
                        break;
                    case 'attendee':
                        header("Location: ../../public/attendee/dashboard.php");
                        break;
                    case 'organizer':
                        header("Location: ../../public/organizer/dashboard.php");
                        break;
                    case 'staff':
                        header("Location: ../../public/staff/dashboard.php");
                        break;
                    default:
                        header("Location: ../../login.php?error=invalid_role");
                }
                exit();
            } else {
                // Incorrect password
                header("Location: ../../login.php?error=incorrect_password&username=" . urlencode($username));
                exit();
            }
        } else {
            // User not found
            header("Location: ../../login.php?error=user_not_found&username=" . urlencode($username));
            exit();
        }
    } catch (Exception $e) {
        // Log the error
        error_log("Login error: " . $e->getMessage());
        header("Location: ../../login.php?error=system_error");
        exit();
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['user_role'] ?? '';

    // Basic validation
    if (empty($username) || empty($password) || empty($role)) {
        header("Location: ../../login.php?error=missing_fields");
        exit();
    }

    // Validate login
    validateLogin($username, $password, $role);
} else {
    // Direct access to this script
    header("Location: ../../login.php");
    exit();
}
?>

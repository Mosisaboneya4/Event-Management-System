<?php
session_start();
require_once '../../config/database.php';

class ProfileManager {
    private $conn;
    private $userId;
    
    public function __construct($connection, $userId) {
        $this->conn = $connection;
        $this->userId = $userId;
    }
    
    public function getUserDetails() {
        $query = "SELECT username, email, created_at FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function verifyCurrentPassword($password) {
        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return password_verify($password, $result['password']);
    }
    
    public function updateProfile($email, $newPassword = null) {
        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET email = ?, password = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssi", $email, $hashedPassword, $this->userId);
        } else {
            $query = "UPDATE users SET email = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $email, $this->userId);
        }
        return $stmt->execute();
    }
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

$conn = getDatabaseConnection();
$profileManager = new ProfileManager($conn, $_SESSION['user_id']);
$update_error = '';
$update_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $newEmail = $_POST['email'] ?? '';
    
    if (!$profileManager->verifyCurrentPassword($currentPassword)) {
        $update_error = 'Current password is incorrect';
    } elseif (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            $update_error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 8) {
            $update_error = 'New password must be at least 8 characters';
        } else {
            $updateSuccess = $profileManager->updateProfile($newEmail, $newPassword);
            $update_success = $updateSuccess ? 'Profile updated successfully' : 'Failed to update profile';
        }
    } else {
        $updateSuccess = $profileManager->updateProfile($newEmail);
        $update_success = $updateSuccess ? 'Profile updated successfully' : 'Failed to update profile';
    }
}

$user_result = $profileManager->getUserDetails();

// Rest of the HTML code remains the same as provided earlier
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Event Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="profile-container">
            <header class="profile-header">
                <div class="header-content">
                    <a href="dashboard.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <h1>My Profile</h1>
                </div>
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user_result['username']); ?></span>
                </div>
            </header>

            <?php if (!empty($update_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($update_error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($update_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($update_success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="profile-form">
                <div class="form-sections">
                    <div class="form-section">
                        <h2>Account Information</h2>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="username" name="username" 
                                    value="<?php echo htmlspecialchars($user_result['username']); ?>" 
                                    readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($user_result['email']); ?>" 
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="account-created">Member Since</label>
                            <div class="input-group">
                                <i class="fas fa-calendar"></i>
                                <input type="text" id="account-created" 
                                    value="<?php echo date('F d, Y', strtotime($user_result['created_at'])); ?>" 
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Security Settings</h2>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" 
                                    placeholder="Enter current password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-key"></i>
                                <input type="password" id="new_password" name="new_password" 
                                    placeholder="Enter new password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-group">
                                <i class="fas fa-check-circle"></i>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                    placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-update">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <style>
        :root {
            --primary-color: #4f46e5;
            --danger-color: #ef4444;
            --success-color: #22c55e;
            --background-color: #f9fafb;
            --card-background: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.5;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .profile-container {
            width: 100%;
            max-width: 800px;
            background: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(to right, var(--primary-color), #818cf8);
            padding: 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .profile-avatar {
            text-align: center;
        }

        .profile-avatar i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .form-sections {
            padding: 2rem;
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .form-section h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem;
            background: white;
        }

        .input-group i {
            color: var(--text-secondary);
            width: 1.5rem;
            text-align: center;
        }

        .input-group input {
            border: none;
            outline: none;
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
        }

        .input-group input:read-only {
            background-color: var(--background-color);
            cursor: not-allowed;
        }

        .btn-update {
            width: calc(100% - 4rem);
            margin: 0 2rem 2rem;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-update:hover {
            background-color: #4338ca;
        }

        .alert {
            margin: 1rem 2rem;
            padding: 1rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: var(--danger-color);
        }

        .alert-success {
            background-color: #dcfce7;
            color: var(--success-color);
        }

        @media (max-width: 640px) {
            .page-wrapper {
                padding: 1rem;
            }

            .profile-container {
                border-radius: 0.5rem;
            }

            .form-sections {
                padding: 1rem;
                grid-template-columns: 1fr;
            }

            .btn-update {
                margin: 0 1rem 1rem;
                width: calc(100% - 2rem);
            }
        }
    </style>
</body>
</html>

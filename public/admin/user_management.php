<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/admin_functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$conn = getDatabaseConnection();

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $delete_query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User deleted successfully.";
    } else {
        $error_message = "Error deleting user.";
    }
    $stmt->close();
}

// Fetch users
$users_query = "SELECT user_id, username, email, role, created_at FROM users WHERE role = 'attendee' ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - EMS Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-management-container {
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin: 20px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            align-items: center;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        .users-table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .users-table tr:hover {
            background-color: #f5f5f5;
        }

        .btn-edit, .btn-delete {
            padding: 8px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            margin: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .btn-edit {
            background: #2196F3;
        }

        .btn-delete {
            background: #f44336;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .admin-header {
            margin-bottom: 2rem;
            padding: 0 2rem;
        }

        .admin-header h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <style>
.sidebar {
    position: fixed;
    left: 0;
    overflow-y: auto;
    z-index: 1000;
}

.admin-main {
    margin-left: 250px;
    flex: 1;
    padding: 24px;
    min-height: 100vh;
    background: #f2f2f7;
}

</style>
        <main class="admin-main">
            <header class="admin-header">
                <h1>User Management</h1>
            </header>
            
            <?php 
            if (isset($success_message)) {
                echo "<div class='alert alert-success'>" . htmlspecialchars($success_message) . "</div>";
            }
            if (isset($error_message)) {
                echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
            }
            ?>
            
            <div class="user-management-container">
                <div class="actions">
                    <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Add New User</a>
                </div>
                
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($user = $users_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                            echo "<td>
                                <a href='#' class='btn-edit' title='Edit user'>
                                    <i class='fas fa-edit'></i>
                                </a>
                                <a href='?delete=" . $user['user_id'] . "' class='btn-delete' title='Delete user' onclick='return confirm(\"Are you sure you want to delete this user?\");'>
                                    <i class='fas fa-trash'></i>
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

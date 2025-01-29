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

// Fetch users
$users_query = "SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - User Management</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_user_management.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>User Management</h1>
        </header>

        <div class="user-management-container">
            <h2>Add New User</h2>
            <form id="addUserForm">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="role">
                    <option value="attendee">Attendee</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Add User</button>
            </form>

            <h2>Existing Users</h2>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <button class="btn-update" data-user-id="<?php echo $user['user_id']; ?>">Update</button>
                                <button class="btn-delete" data-user-id="<?php echo $user['user_id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api_user_management.php', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(formData)),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                if (confirm('Are you sure you want to delete this user?')) {
                    fetch('api_user_management.php?api=delete_user&user_id=' + userId, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    });
                }
            });
        });
    </script>
</body>
</html>

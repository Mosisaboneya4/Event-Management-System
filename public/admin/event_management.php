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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management - EMS Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_event_management.css">
</head>
<body>
    <div class="admin-container">
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
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Event Management</h1>
            </header>
            
            <div class="event-management-container">
                <div class="actions">
                    <button class="btn btn-primary">Create New Event</button>
                </div>
                
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $events_query = "SELECT * FROM events ORDER BY date DESC";
                        $events_result = $conn->query($events_query);
                        
                        while ($event = $events_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($event['name']) . "</td>";
                            echo "<td>" . date('M d, Y H:i', strtotime($event['date'])) . "</td>";
                            echo "<td>" . htmlspecialchars($event['location']) . "</td>";
                            echo "<td>" . htmlspecialchars($event['status']) . "</td>";
                            echo "<td>
                                <button class='btn btn-edit'>Edit</button>
                                <button class='btn btn-delete'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin_event_management.js"></script>
</body>
</html>

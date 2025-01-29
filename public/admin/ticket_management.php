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

// Fetch tickets
$tickets_query = "
    SELECT 
        t.ticket_id, 
        e.name as event_name, 
        u.username as attendee, 
        t.ticket_code, 
        t.payment_status, 
        t.check_in_time IS NOT NULL as checked_in
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    JOIN 
        users u ON t.attendee_id = u.user_id
    ORDER BY 
        t.ticket_id DESC
";
$tickets_result = $conn->query($tickets_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Management - EMS Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_ticket_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            <div class="admin-content">
                <header class="admin-header">
                    <h1>Ticket Management</h1>
                    <div class="header-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Generate Ticket
                        </a>
                    </div>
                </header>
                
                <div class="ticket-management-container">
                    <div class="table-responsive">
                        <table class="tickets-table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Event Name</th>
                                    <th>Attendee</th>
                                    <th>Ticket Code</th>
                                    <th>Payment Status</th>
                                    <th>Check-in Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($tickets_result->num_rows > 0) {
                                    while ($ticket = $tickets_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td data-label='Ticket ID'>" . htmlspecialchars($ticket['ticket_id']) . "</td>";
                                        echo "<td data-label='Event Name'>" . htmlspecialchars($ticket['event_name']) . "</td>";
                                        echo "<td data-label='Attendee'>" . htmlspecialchars($ticket['attendee']) . "</td>";
                                        echo "<td data-label='Ticket Code'>" . htmlspecialchars($ticket['ticket_code']) . "</td>";
                                        echo "<td data-label='Payment Status'>" . htmlspecialchars($ticket['payment_status']) . "</td>";
                                        echo "<td data-label='Check-in Status'>" . ($ticket['checked_in'] ? 'Checked In' : 'Not Checked In') . "</td>";
                                        echo "<td data-label='Actions'>
                                            <div class='action-buttons'>
                                                <a href='#' class='btn-view' title='View Details'>
                                                    <i class='fas fa-eye'></i>
                                                </a>
                                                <a href='#' class='btn-edit' title='Edit Ticket'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                            </div>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='no-data'>No tickets found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin_ticket_management.js"></script>
</body>
</html>

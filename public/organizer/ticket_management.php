<?php
session_start();
require_once '../../config/database.php';

// Ensure only organizers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../login.php");
    exit();
}

$conn = getDatabaseConnection();
$organizer_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$tickets_per_page = 10;
$offset = ($page - 1) * $tickets_per_page;

// Ticket management query
$tickets_query = "
    SELECT 
        t.ticket_id,
        t.ticket_code,
        t.purchase_date,
        t.check_in_time,
        e.name AS event_name,
        u.first_name,
        u.last_name,
        u.email
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    JOIN 
        users u ON t.attendee_id = u.user_id
    WHERE 
        e.organizer_id = ?
    ORDER BY 
        t.purchase_date DESC
    LIMIT ? OFFSET ?
";
$tickets_stmt = $conn->prepare($tickets_query);
$tickets_stmt->bind_param("iii", $organizer_id, $tickets_per_page, $offset);
$tickets_stmt->execute();
$tickets_result = $tickets_stmt->get_result();

// Total tickets count
$count_query = "
    SELECT 
        COUNT(*) as total_tickets
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    WHERE 
        e.organizer_id = ?
";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $organizer_id);
$count_stmt->execute();
$total_tickets = $count_stmt->get_result()->fetch_assoc()['total_tickets'];
$total_pages = ceil($total_tickets / $tickets_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Management - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/organizer_dashboard.css">
    <style>
        .ticket-management-container {
            padding: 2rem;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .ticket-table th, 
        .ticket-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .ticket-table th {
            background-color: #f2f2f2;
        }

        .ticket-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .ticket-status.checked-in {
            background-color: #4CAF50;
            color: white;
        }

        .ticket-status.pending {
            background-color: #FFC107;
            color: black;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination a, 
        .pagination span {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
        }

        .pagination .current {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="user-info">
                    <h1>Ticket Management</h1>
                    <p>Overview of All Ticket Sales</p>
                </div>
            </header>

            <div class="ticket-management-container">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Event Name</th>
                            <th>Attendee Name</th>
                            <th>Attendee Email</th>
                            <th>Purchase Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ticket = $tickets_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['ticket_code']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                                <td><?php echo date('F d, Y h:i A', strtotime($ticket['purchase_date'])); ?></td>
                                <td>
                                    <span class="ticket-status <?php 
                                        echo $ticket['check_in_time'] ? 'checked-in' : 'pending';
                                    ?>">
                                        <?php echo $ticket['check_in_time'] ? 'Checked In' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="viewTicketDetails('<?php echo $ticket['ticket_id']; ?>')">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function viewTicketDetails(ticketId) {
            // Placeholder for ticket details modal or page
            alert('Ticket details for ID: ' + ticketId);
            // In a real implementation, this would open a modal or navigate to a details page
        }
    </script>
</body>
</html>

<?php
session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

$conn = getDatabaseConnection();

// Fetch user's tickets
$tickets_query = "
    SELECT 
        t.ticket_id, 
        t.ticket_code, 
        e.name AS event_name, 
        e.date AS event_date, 
        e.location, 
        t.created_at AS purchase_date,
        t.check_in_time
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    WHERE 
        t.attendee_id = ?
    ORDER BY 
        e.date DESC
";

$stmt = $conn->prepare($tickets_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/my_tickets.css">
</head>
<body>
    <div class="tickets-container">
        <header>
            <h1>My Tickets</h1>
            <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
        </header>

        <main class="tickets-list">
            <?php if ($tickets_result->num_rows > 0): ?>
                <?php while($ticket = $tickets_result->fetch_assoc()): ?>
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <h2><?php echo htmlspecialchars($ticket['event_name']); ?></h2>
                            <span class="ticket-code">
                                Ticket Code: <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                            </span>
                        </div>
                        <div class="ticket-details">
                            <p>
                                <strong>Date:</strong> 
                                <?php echo date('F d, Y h:i A', strtotime($ticket['event_date'])); ?>
                            </p>
                            <p>
                                <strong>Location:</strong> 
                                <?php echo htmlspecialchars($ticket['location']); ?>
                            </p>
                            <p>
                                <strong>Purchased on:</strong> 
                                <?php echo date('F d, Y', strtotime($ticket['purchase_date'])); ?>
                            </p>
                            <p>
                                <strong>Status:</strong> 
                                <?php 
                                if ($ticket['check_in_time']) {
                                    echo '<span class="status-checked-in">Checked In</span>';
                                } else {
                                    echo '<span class="status-pending">Not Checked In</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-tickets">
                    <p>You haven't purchased any tickets yet.</p>
                    <a href="events.php" class="btn-browse-events">Browse Events</a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .tickets-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .ticket-card {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .status-checked-in {
            color: green;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .btn-back, .btn-browse-events {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }
    </style>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

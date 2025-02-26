<?php
session_start();
require_once '../../config/database.php';

// Ensure only organizers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../index.php");
    exit();
}

// Check if event_id is provided
if (!isset($_GET['event_id'])) {
    header("Location: dashboard.php");
    exit();
}

$event_id = intval($_GET['event_id']);
$organizer_id = $_SESSION['user_id'];

$conn = getDatabaseConnection();

// Fetch event details
$event_query = "
    SELECT 
        e.event_id, 
        e.name, 
        e.description, 
        e.date, 
        e.location, 
        e.status, 
        e.ticket_price, 
        e.max_tickets,
        (SELECT COUNT(*) FROM tickets WHERE event_id = e.event_id) as total_tickets_sold,
        (SELECT COUNT(*) FROM tickets WHERE event_id = e.event_id AND check_in_time IS NOT NULL) as checked_in_tickets
    FROM events e
    WHERE e.event_id = ? AND e.organizer_id = ?
";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$event = $event_result->fetch_assoc();

// Fetch ticket details with error handling
try {
    $tickets_query = "
        SELECT 
            t.ticket_id, 
            t.attendee_id,
            t.purchase_date,
            t.check_in_time
        FROM tickets t
        WHERE t.event_id = ?
        ORDER BY t.purchase_date DESC
    ";
    $tickets_stmt = $conn->prepare($tickets_query);
    
    if (!$tickets_stmt) {
        throw new Exception("Failed to prepare tickets query: " . $conn->error);
    }
    
    $tickets_stmt->bind_param("i", $event_id);
    $tickets_stmt->execute();
    $tickets_result = $tickets_stmt->get_result();

    // Prepare to fetch attendee details
    $attendee_details = [];
    while ($ticket = $tickets_result->fetch_assoc()) {
        // Fetch attendee details for each ticket
        $attendee_query = "
            SELECT 
                first_name, 
                last_name, 
                email
            FROM users 
            WHERE user_id = ?
        ";
        $attendee_stmt = $conn->prepare($attendee_query);
        
        if (!$attendee_stmt) {
            throw new Exception("Failed to prepare attendee query: " . $conn->error);
        }
        
        $attendee_stmt->bind_param("i", $ticket['attendee_id']);
        $attendee_stmt->execute();
        $attendee_result = $attendee_stmt->get_result();
        
        $attendee = $attendee_result->fetch_assoc();
        if ($attendee) {
            $ticket['first_name'] = $attendee['first_name'] ?? 'N/A';
            $ticket['last_name'] = $attendee['last_name'] ?? 'N/A';
            $ticket['email'] = $attendee['email'] ?? 'N/A';
            $attendee_details[] = $ticket;
        }
        
        $attendee_stmt->close();
    }
    
    // Re-create result object for template
    $tickets_result = $attendee_details;
} catch (Exception $e) {
    // Log the error
    error_log("Event Details Error: " . $e->getMessage());
    
    // Set a user-friendly error message
    $tickets_error = "Unable to retrieve ticket details. Please try again later.";
    $tickets_result = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details - <?php echo htmlspecialchars($event['name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/organizer_dashboard.css">
    <style>
        .event-details-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .event-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            text-transform: uppercase;
        }

        .event-status.draft {
            background-color: #FFC107;
            color: #212529;
        }

        .event-status.published {
            background-color: #28a745;
            color: white;
        }

        .event-status.cancelled {
            background-color: #dc3545;
            color: white;
        }

        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .tickets-table th, 
        .tickets-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .tickets-table th {
            background-color: #f2f2f2;
        }

        .check-in-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .check-in-status.checked-in {
            background-color: #28a745;
            color: white;
        }

        .check-in-status.pending {
            background-color: #FFC107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="event-details-container">
                <div class="event-header">
                    <h1><?php echo htmlspecialchars($event['name']); ?></h1>
                    <span class="event-status <?php echo strtolower($event['status']); ?>">
                        <?php echo htmlspecialchars($event['status']); ?>
                    </span>
                </div>

                <div class="event-info">
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($event['date'])); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p><strong>Ticket Price:</strong> $<?php echo number_format($event['ticket_price'], 2); ?></p>
                    <p>
                        <strong>Tickets:</strong> 
                        <?php echo $event['total_tickets_sold']; ?> / <?php echo $event['max_tickets']; ?> sold
                        (<?php echo $event['checked_in_tickets']; ?> checked in)
                    </p>
                </div>

                <h2>Ticket Sales</h2>
                <?php if (isset($tickets_error)) : ?>
                    <p class="error-message"><?php echo $tickets_error; ?></p>
                <?php else : ?>
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Attendee Name</th>
                                <th>Email</th>
                                <th>Purchase Date</th>
                                <th>Check-In Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tickets_result as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                                    <td><?php echo date('F d, Y h:i A', strtotime($ticket['purchase_date'])); ?></td>
                                    <td>
                                        <span class="check-in-status <?php 
                                            echo $ticket['check_in_time'] ? 'checked-in' : 'pending'; 
                                        ?>">
                                            <?php echo $ticket['check_in_time'] ? 'Checked In' : 'Pending'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div class="event-actions" style="margin-top: 1rem;">
                    <a href="manage_event.php?event_id=<?php echo $event_id; ?>" class="btn btn-manage">Edit Event</a>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?>

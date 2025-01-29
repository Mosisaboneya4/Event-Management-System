<?php
session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

// Check if ticket code is provided
if (!isset($_GET['ticket_code'])) {
    header("Location: events.php");
    exit();
}

$ticket_code = $_GET['ticket_code'];
$conn = getDatabaseConnection();

// Fetch ticket details
$ticket_query = "SELECT t.ticket_id, t.ticket_code, t.purchase_date, t.otp, t.otp_expiry,
                        e.event_name, e.start_date, e.venue, 
                        e.ticket_price
                 FROM tickets t
                 JOIN events e ON t.event_id = e.event_id
                 WHERE t.ticket_code = ? AND t.attendee_id = ?";
$stmt = $conn->prepare($ticket_query);
$stmt->bind_param("si", $ticket_code, $_SESSION['user_id']);
$stmt->execute();
$ticket_result = $stmt->get_result();

if ($ticket_result->num_rows === 0) {
    header("Location: events.php");
    exit();
}

$ticket = $ticket_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Confirmation - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/ticket_confirmation.css">
</head>
<body>
    <div class="confirmation-container">
        <div class="ticket-card">
            <div class="ticket-header">
                <h1>Ticket Confirmation</h1>
                <p class="success-message">Your ticket has been successfully purchased!</p>
            </div>

            <div class="ticket-details">
                <div class="event-info">
                    <h2><?php echo htmlspecialchars($ticket['event_name']); ?></h2>
                    <div class="event-meta">
                        <span class="event-date">
                            <?php 
                            $start_date = new DateTime($ticket['start_date']);
                            echo $start_date->format('F d, Y h:i A'); 
                            ?>
                        </span>
                        <span class="event-venue">
                            <?php echo htmlspecialchars($ticket['venue']); ?>
                        </span>
                    </div>
                </div>

                <div class="ticket-meta">
                    <div class="ticket-code">
                        <label>Ticket Code</label>
                        <span><?php echo htmlspecialchars($ticket['ticket_code']); ?></span>
                    </div>
                    <div class="purchase-date">
                        <label>Purchase Date</label>
                        <span>
                            <?php 
                            $purchase_date = new DateTime($ticket['purchase_date']);
                            echo $purchase_date->format('F d, Y h:i A'); 
                            ?>
                        </span>
                    </div>
                </div>

                <div class="otp-section">
                    <h3>One-Time Password (OTP)</h3>
                    <div class="otp-details">
                        <p class="otp-code"><?php echo htmlspecialchars($ticket['otp']); ?></p>
                        <p class="otp-expiry">
                            Expires at: 
                            <?php 
                            $otp_expiry = new DateTime($ticket['otp_expiry']);
                            echo $otp_expiry->format('F d, Y h:i A'); 
                            ?>
                        </p>
                    </div>
                </div>

                <div class="ticket-actions">
                    <a href="my_tickets.php" class="btn btn-view-tickets">View My Tickets</a>
                    <a href="events.php" class="btn btn-browse-events">Browse More Events</a>
                </div>

                <div class="otp-note">
                    <p>Use this OTP for event check-in. Please keep it confidential.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

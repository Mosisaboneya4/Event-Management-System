<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/email_config.php';

// Ensure only attendees can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

// Check if event_id is provided
if (!isset($_POST['event_id']) && !isset($_GET['event_id'])) {
    header("Location: events.php");
    exit();
}

$event_id = isset($_POST['event_id']) ? $_POST['event_id'] : $_GET['event_id'];
$conn = getDatabaseConnection();

// Fetch event details
$event_query = "SELECT event_id, name, ticket_price 
                FROM events 
                WHERE event_id = ? AND status = 'published' > 0";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();

if ($event_result->num_rows === 0) {
    header("Location: events.php");
    exit();
}

$event = $event_result->fetch_assoc();

// Handle ticket purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    $ticket_quantity = isset($_POST['ticket_quantity']) ? intval($_POST['ticket_quantity']) : 1;
    
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Check if enough tickets are available
        if ($ticket_quantity > $event['available_tickets']) {
            throw new Exception("Not enough tickets available.");
        }

        // Generate unique ticket code and OTP
        $ticket_code = 'T-' . uniqid() . '-' . $event_id;
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Fetch user email
        $user_query = "SELECT email, phone_number FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $_SESSION['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();

        // Insert ticket with OTP
        $insert_ticket_query = "INSERT INTO tickets (event_id, attendee_id, ticket_code, otp, otp_created_at, otp_expiry) VALUES (?, ?, ?, ?, NOW(), ?)";
        $insert_stmt = $conn->prepare($insert_ticket_query);
        $insert_stmt->bind_param("iisss", $event_id, $_SESSION['user_id'], $ticket_code, $otp, $otp_expiry);
        $insert_stmt->execute();

        // Fetch event name for OTP message
        $event_name_query = "SELECT event_name FROM events WHERE event_id = ?";
        $event_name_stmt = $conn->prepare($event_name_query);
        $event_name_stmt->bind_param("i", $event_id);
        $event_name_stmt->execute();
        $event_name_result = $event_name_stmt->get_result();
        $event_details = $event_name_result->fetch_assoc();

        // Send OTP via email
        if (!empty($user['email'])) {
            $email_sent = sendOTPEmail($user['email'], $otp, $event_details['event_name']);
            if (!$email_sent) {
                // Log email sending failure
                error_log("Failed to send OTP email to: " . $user['email']);
            }
        }

        // Send OTP via SMS (if phone number available)
        if (!empty($user['phone_number'])) {
            $sms_sent = sendOTPSMS($user['phone_number'], $otp, $event_details['event_name']);
            if (!$sms_sent) {
                // Log SMS sending failure
                error_log("Failed to send OTP SMS to: " . $user['phone_number']);
            }
        }

        // Update available tickets
        $update_tickets_query = "UPDATE events SET available_tickets = available_tickets - ? WHERE event_id = ?";
        $update_stmt = $conn->prepare($update_tickets_query);
        $update_stmt->bind_param("ii", $ticket_quantity, $event_id);
        $update_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to ticket confirmation
        header("Location: ticket_confirmation.php?ticket_code=" . urlencode($ticket_code));
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Ticket - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/purchase_ticket.css">
</head>
<body>
    <div class="purchase-container">
        <div class="ticket-details">
            <h1>Purchase Ticket</h1>
            
            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="event-info">
                <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
                <div class="ticket-pricing">
                    <span class="price">
                        $<?php echo number_format($event['ticket_price'], 2); ?> per ticket
                    </span>
                    <span class="available-tickets">
                        <?php echo $event['available_tickets']; ?> tickets available
                    </span>
                </div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                
                <div class="form-group">
                    <label for="ticket_quantity">Number of Tickets</label>
                    <select name="ticket_quantity" id="ticket_quantity">
                        <?php for($i = 1; $i <= min(5, $event['available_tickets']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="total-calculation">
                    <span>Total Cost:</span>
                    <span id="total-cost">
                        $<?php echo number_format($event['ticket_price'], 2); ?>
                    </span>
                </div>

                <button type="submit" name="purchase" class="btn-purchase">
                    Complete Purchase
                </button>
            </form>
        </div>
    </div>

    <script src="../../assets/js/purchase_ticket.js"></script>
</body>
</html>

<?php
$conn->close();
?>

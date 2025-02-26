<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the database
$conn = new mysqli("localhost", "root", "", "ooppayrol");

// Check the connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the ticket code and event ID from the request
$data = json_decode(file_get_contents("php://input"), true);
$ticket_code = isset($data['ticket_code']) ? trim($data['ticket_code']) : '';
$event_id = isset($data['event_id']) ? intval($data['event_id']) : 0;

if (empty($ticket_code) || $event_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ticket or event information.']);
    exit;
}

// Verify ticket and perform check-in
$check_in_query = "
    SELECT 
        t.ticket_id, 
        t.ticket_code, 
        t.attendee_id,
        t.check_in_time,
        e.name AS event_name,
        u.first_name,
        u.last_name
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    JOIN 
        users u ON t.attendee_id = u.user_id
    WHERE 
        t.ticket_code = ? 
        AND t.event_id = ?
";

$stmt = $conn->prepare($check_in_query);
$stmt->bind_param("si", $ticket_code, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();

    // Check if already checked in
    if (!empty($ticket['check_in_time'])) {
        echo json_encode(['status' => 'error', 'message' => 'Ticket already checked in on ' . date('F d, Y h:i A', strtotime($ticket['check_in_time']))]);
    } else {
        // Perform check-in
        $update_query = "
            UPDATE tickets 
            SET 
                check_in_time = NOW() 
            WHERE 
                ticket_code = ? 
                AND event_id = ?
        ";
        
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $ticket_code, $event_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Successfully checked in ' . htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']) . ' for ' . htmlspecialchars($ticket['event_name'])]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to check in ticket. Please try again.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ticket or ticket not found for this event.']);
}

$stmt->close();
$conn->close();
?>

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

// Get the ticket code from the request
$data = json_decode(file_get_contents("php://input"), true);
$ticket_code = isset($data['ticket_code']) ? trim($data['ticket_code']) : '';

if (empty($ticket_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a ticket code.']);
    exit;
}

// Prepare query to fetch ticket details
$query = "
    SELECT 
        t.ticket_id, 
        t.ticket_code, 
        t.check_in_time,
        e.name as event_name,
        e.date as event_date,
        e.location,
        u.username as attendee_name
    FROM 
        tickets t
    JOIN 
        events e ON t.event_id = e.event_id
    JOIN 
        users u ON t.attendee_id = u.user_id
    WHERE 
        t.ticket_code = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $ticket_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket_details = $result->fetch_assoc();

    // Check if ticket is already checked in
    if (!empty($ticket_details['check_in_time'])) {
        echo json_encode(['status' => 'success', 'message' => 'Ticket has already been checked in on ' . date('F d, Y h:i A', strtotime($ticket_details['check_in_time']))]);
    } else {
        echo json_encode(['status' => 'success', 'ticket_details' => $ticket_details]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ticket code. No matching ticket found.']);
}

$stmt->close();
$conn->close();
?>

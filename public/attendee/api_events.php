<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this API
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Establish database connection
$conn = getDatabaseConnection();
if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Fetch events for the user
$user_id = $_SESSION['user_id'];
$events_query = "
    SELECT 
        e.event_id, 
        e.name, 
        e.description, 
        e.date, 
        e.location, 
        e.ticket_price, 
        e.max_tickets,
        (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.event_id) as tickets_sold,
        COALESCE(e.status, 'draft') as status
    FROM events e
    JOIN user_events ue ON e.event_id = ue.event_id
    WHERE ue.user_id = ?
    AND e.status = 'published'
    AND e.date >= CURDATE()
    ORDER BY e.date ASC
";

$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$events_result = $stmt->get_result();

$events = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row;
}

$stmt->close();
$conn->close();

// Return the events in JSON format
echo json_encode(['status' => 'success', 'events' => $events]);
?>

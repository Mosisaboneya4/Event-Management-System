<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
session_start();

// Ensure only organizers can access this API
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$conn = getDatabaseConnection();

// Handle fetching event details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['event_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Event ID is required']);
        exit();
    }

    $event_id = intval($_GET['event_id']);
    $organizer_id = $_SESSION['user_id'];

    // Fetch event details
    $event_query = "
        SELECT 
            event_id, 
            name, 
            description, 
            date, 
            location, 
            status, 
            ticket_price, 
            max_tickets
        FROM events 
        WHERE event_id = ? AND organizer_id = ?
    ";
    $event_stmt = $conn->prepare($event_query);
    $event_stmt->bind_param("ii", $event_id, $organizer_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();

    if ($event_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Event not found']);
        exit();
    }

    $event = $event_result->fetch_assoc();
    echo json_encode(['status' => 'success', 'event' => $event]);
}

// Handle updating event details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['event_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Event ID is required']);
        exit();
    }

    $event_id = intval($_POST['event_id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $ticket_price = floatval($_POST['ticket_price']);
    $max_tickets = intval($_POST['max_tickets']);

    $update_query = "
        UPDATE events 
        SET 
            name = ?, 
            description = ?, 
            date = ?, 
            location = ?, 
            status = ?, 
            ticket_price = ?, 
            max_tickets = ?
        WHERE event_id = ? AND organizer_id = ?
    ";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param(
        "sssssdiii", 
        $name, $description, $date, $location, $status, 
        $ticket_price, $max_tickets, $event_id, $_SESSION['user_id']
    );

    if ($update_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Event updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update event.']);
    }
}

$conn->close();
?>

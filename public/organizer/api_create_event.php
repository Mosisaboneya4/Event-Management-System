<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
session_start();

// Ensure only organizers can access this API
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$conn = getDatabaseConnection();

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['event_description'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $location = trim($_POST['event_location'] ?? '');
    $ticket_price = floatval($_POST['ticket_price'] ?? 0);
    $max_tickets = intval($_POST['max_tickets'] ?? 0);
    $status = $_POST['event_status'] ?? 'draft';

    // Basic validation
    if (empty($name) || empty($date) || empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit();
    } else {
        try {
            // Prepare SQL to insert new event
            $insert_query = "
                INSERT INTO events (
                    name, 
                    description, 
                    date, 
                    location, 
                    ticket_price, 
                    max_tickets, 
                    organizer_id,
                    status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?
                )
            ";
            
            $stmt = $conn->prepare($insert_query);
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare event insert: ' . $conn->error]);
                exit();
            }

            $stmt->bind_param(
                "ssssdiss", 
                $name, 
                $description, 
                $date, 
                $location, 
                $ticket_price, 
                $max_tickets, 
                $_SESSION['user_id'],
                $status
            );

            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create event: ' . $stmt->error]);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Event created successfully!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

$conn->close();
?>

<?php
header("Content-Type: application/json");
// Removed Access-Control-Allow-Origin header
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this API
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$conn = getDatabaseConnection();

// Fetch user details
$user_query = "SELECT username, email, created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();

$stmt->close();
$conn->close();

// Return the user profile data in JSON format
echo json_encode(['status' => 'success', 'user' => $user_result]);
?>

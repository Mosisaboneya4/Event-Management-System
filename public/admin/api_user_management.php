<?php
header("Content-Type: application/json");
// Removed Access-Control-Allow-Origin header
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

// Connect to the database
$conn = getDatabaseConnection();

// Check if the request is for fetching attendance
if (isset($_GET['api']) && $_GET['api'] === 'get_attendance') {
    // Get the user ID from the request
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
        exit;
    }

    // Fetch all attendance records for the user
    $sql = "SELECT * FROM attendance WHERE user_id = ? ORDER BY year DESC, month DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }

    $stmt->close();
    $conn->close();

    // Return the attendance records in JSON format
    echo json_encode(['status' => 'success', 'attendance' => $attendance]);
    exit;
}

// Handle POST request to add a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? 'attendee';

    if (empty($username) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and email are required']);
        exit;
    }

    $insert_query = "INSERT INTO users (username, email, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $username, $email, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding user']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle PUT request to update an existing user
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? 0;
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';

    if ($user_id <= 0 || empty($username) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    $update_query = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $username, $email, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Additional API functionalities can be added here...

$conn->close();
?>

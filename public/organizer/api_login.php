<?php
header("Content-Type: application/json");
// Removed Access-Control-Allow-Origin header
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the database
$conn = new mysqli("localhost", "root", "new_password", "ems_database");

// Check the connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the user credentials from the request
$data = json_decode(file_get_contents("php://input"), true);
$user_role = isset($data['user_role']) ? trim($data['user_role']) : '';
$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if (empty($user_role) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields.']);
    exit;
}

// Prepare query to fetch user details
$query = "
    SELECT 
        user_id, 
        password 
    FROM 
        users 
    WHERE 
        username = ? 
        AND role = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $user_role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'success', 'message' => 'Login successful!', 'user_id' => $user['user_id']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>

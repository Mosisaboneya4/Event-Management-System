<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDatabaseConnection();
    $attendee_id = $_SESSION['user_id'];
    $description = trim($_POST['photo_description']);
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../../uploads/attendee_photos';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $photo = $_FILES['attendee_photo'];
    $photo_path = $upload_dir . '/' . uniqid() . '_' . $photo['name'];
    
    if (move_uploaded_file($photo['tmp_name'], $photo_path)) {
        $db_photo_path = 'uploads/attendee_photos/' . basename($photo_path);
        $query = "INSERT INTO attendee_photos (attendee_id, photo_path, description, upload_date) 
                 VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $attendee_id, $db_photo_path, $description);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Upload Success</title>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Photo Uploaded Successfully!</h1>
            <p>Your photo has been shared with the event staff.</p>
            <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        </div>
    </div>

    <style>
        .success-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
        }

        .success-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            width: 90%;
        }

        .success-icon {
            font-size: 5rem;
            color: #10b981;
            margin-bottom: 1.5rem;
        }

        h1 {
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        p {
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .back-button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }
    </style>
</body>
</html>

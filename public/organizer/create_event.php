<?php
session_start();
require_once '../../config/database.php';

// Ensure only organizers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../login.php");
    exit();
}

$conn = getDatabaseConnection();
$error_message = '';
$success_message = '';

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
    error_log("Event Creation Status: " . $status);

    // Basic validation
    if (empty($name) || empty($date) || empty($location)) {
        $error_message = "Please fill in all required fields.";
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
                error_log("Event Insert Prepare Error: " . $conn->error);
                throw new Exception("Failed to prepare event insert: " . $conn->error);
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
                error_log("Event Insert Execute Error: " . $stmt->error);
                $error_message = "Failed to create event: " . $stmt->error;
            } else {
                error_log("Event Created Successfully. Status: " . $status);
                $success_message = "Event created successfully!";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/organizer_dashboard.css">
    <style>
        .create-event-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-create-event {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-create-event:hover {
            background: #45a049;
        }

        .error-message {
            color: red;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            color: green;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="user-info">
                    <h1>Create New Event</h1>
                    <p>Plan and Launch Your Next Event</p>
                </div>
            </header>

            <div class="create-event-container">
                <form method="POST" class="create-event-form">
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="success-message">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="event_name">Event Name</label>
                        <input 
                            type="text" 
                            id="event_name" 
                            name="event_name" 
                            required
                            value="<?php echo htmlspecialchars($_POST['event_name'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="event_description">Event Description</label>
                        <textarea 
                            id="event_description" 
                            name="event_description" 
                            rows="4"
                        ><?php echo htmlspecialchars($_POST['event_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="event_date">Event Date</label>
                        <input 
                            type="date" 
                            id="event_date" 
                            name="event_date" 
                            required
                            value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="event_location">Event Location</label>
                        <input 
                            type="text" 
                            id="event_location" 
                            name="event_location" 
                            required
                            value="<?php echo htmlspecialchars($_POST['event_location'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="ticket_price">Ticket Price ($)</label>
                        <input 
                            type="number" 
                            id="ticket_price" 
                            name="ticket_price" 
                            step="0.01" 
                            min="0"
                            value="<?php echo htmlspecialchars($_POST['ticket_price'] ?? '0'); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="max_tickets">Max Tickets</label>
                        <input 
                            type="number" 
                            id="max_tickets" 
                            name="max_tickets" 
                            min="1"
                            value="<?php echo htmlspecialchars($_POST['max_tickets'] ?? '100'); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="event_status">Event Status</label>
                        <select 
                            id="event_status" 
                            name="event_status"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-create-event">Create Event</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Optional: Add client-side validation
        document.querySelector('.create-event-form').addEventListener('submit', function(e) {
            const eventName = document.getElementById('event_name');
            const eventDate = document.getElementById('event_date');
            const eventLocation = document.getElementById('event_location');
            
            if (!eventName.value.trim()) {
                e.preventDefault();
                alert('Please enter an event name.');
                return;
            }

            if (!eventDate.value) {
                e.preventDefault();
                alert('Please select an event date.');
                return;
            }

            if (!eventLocation.value.trim()) {
                e.preventDefault();
                alert('Please enter an event location.');
            }
        });
    </script>
</body>
</html>

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
                            WHERE event_id = ?
                        ";
                        $event_stmt = $conn->prepare($event_query);
                        $event_stmt->bind_param("i", $event_id);
                        $event_stmt->execute();
                        $event_result = $event_stmt->get_result();
                        $event = $event_result->fetch_assoc();
                        
                        // Log refreshed event details
                        error_log("Refreshed Event Details: " . 
                            "ID=" . $event['event_id'] . 
                            ", Name=" . $event['name'] . 
                            ", Status=" . $event['status'] . 
                            ", Date=" . $event['date']
                        );
                    } else {
                        $error_message = "No changes made. Check event ownership.";
                        error_log("No rows updated. Event ID: $event_id, User ID: " . $_SESSION['user_id']);
                    }
                } else {
                    // Log detailed error
                    error_log("Status update execute failed: " . $update_stmt->error);
                    $error_message = "Failed to update event status: " . $update_stmt->error;
                }
                
                $update_stmt->close();
            }
        }
    } else {
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
            $ticket_price, $max_tickets, $event_id, $organizer_id
        );

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Event updated successfully!";
            header("Location: event_details.php?event_id=" . $event_id);
            exit();
        } else {
            $error_message = "Failed to update event. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Event - <?php echo htmlspecialchars($event['name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/organizer_dashboard.css">
    <style>
        .manage-event-container {
            max-width: 600px;
            margin: 2rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 1rem;
        }

        .btn-update-status {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-update-status:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="manage-event-container">
                <h1>Manage Event: <?php echo htmlspecialchars($event['name']); ?></h1>

                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name">Event Name</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($event['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required>
                            <?php echo htmlspecialchars($event['description']); ?>
                        </textarea>
                    </div>

                    <div class="form-group">
                        <label for="date">Event Date</label>
                        <input type="datetime-local" id="date" name="date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($event['date'])); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($event['location']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Event Status</label>
                        <select id="status" name="status" required>
                            <option value="draft" <?php echo $event['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $event['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="cancelled" <?php echo $event['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ticket_price">Ticket Price ($)</label>
                        <input type="number" id="ticket_price" name="ticket_price" 
                               value="<?php echo number_format($event['ticket_price'], 2); ?>" 
                               step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="max_tickets">Maximum Tickets</label>
                        <input type="number" id="max_tickets" name="max_tickets" 
                               value="<?php echo $event['max_tickets']; ?>" 
                               min="1" required>
                    </div>

                    <button type="submit" class="btn-submit">Update Event</button>
                    <a href="event_details.php?event_id=<?php echo $event_id; ?>" class="btn btn-secondary">Cancel</a>
                </form>

                <form method="POST">
                    <div class="form-group">
                        <label for="event_status">Event Status</label>
                        <select name="event_status" id="event_status" class="form-control">
                            <option value="draft" <?php echo ($event['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($event['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            <option value="cancelled" <?php echo ($event['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <button type="submit" name="update_status" class="btn btn-update-status">
                        Update Event Status
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?>

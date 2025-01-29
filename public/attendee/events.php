<?php
session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

// Establish database connection
$conn = getDatabaseConnection();
if (!$conn) {
    error_log("Failed to establish database connection");
    die("Database connection failed");
}

// Comprehensive event query with full debugging
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
        COALESCE(e.status, 'draft') as status,
        e.organizer_id
    FROM events e
    WHERE 
        (e.status = 'published')
        AND e.date >= CURDATE()
    ORDER BY e.date ASC
";

// Prepare and execute the query with error handling
try {
    error_log("Executing events query: " . $events_query);
    
    $events_stmt = $conn->prepare($events_query);
    if (!$events_stmt) {
        throw new Exception("Failed to prepare events query: " . $conn->error);
    }

    if (!$events_stmt->execute()) {
        throw new Exception("Failed to execute events query: " . $events_stmt->error);
    }

    $events_result = $events_stmt->get_result();
    
    // Log detailed event information
    error_log("Total Events Found: " . $events_result->num_rows);
    
    // Debug logging for each event
    $debug_events = [];
    while ($debug_event = $events_result->fetch_assoc()) {
        $debug_events[] = $debug_event;
        error_log("Debug Event: " . 
            "ID=" . $debug_event['event_id'] . 
            ", Name=" . $debug_event['name'] . 
            ", Status=" . $debug_event['status'] . 
            ", Date=" . $debug_event['date'] . 
            ", Price=" . $debug_event['ticket_price'] . 
            ", Max Tickets=" . $debug_event['max_tickets']
        );
    }

    // Reset the result pointer
    $events_result->data_seek(0);
    
} catch (Exception $e) {
    // Log the error
    error_log("Event Query Error: " . $e->getMessage());
    
    // Set a user-friendly error message
    $events_error = "Unable to retrieve events. Please try again later.";
    $events_result = null;
}

// Fallback if no events found
if ($events_result && $events_result->num_rows === 0) {
    // Try a more lenient query
    $fallback_query = "
        SELECT 
            event_id, 
            name, 
            description, 
            date, 
            location, 
            ticket_price, 
            max_tickets,
            (SELECT COUNT(*) FROM tickets t WHERE t.event_id = events.event_id) as tickets_sold,
            'draft' as status
        FROM events
        ORDER BY date ASC
        LIMIT 10
    ";
    $events_result = $conn->query($fallback_query);
    error_log("Fallback query executed. Events found: " . $events_result->num_rows);
}

// Debugging: Log all events in the database
$debug_query = "
    SELECT 
        event_id, 
        name, 
        status, 
        date
    FROM events
    ORDER BY date
";
$debug_result = $conn->query($debug_query);
while ($event = $debug_result->fetch_assoc()) {
    error_log("Debug Event: ID=" . $event['event_id'] . 
              ", Name=" . $event['name'] . 
              ", Status=" . $event['status'] . 
              ", Date=" . $event['date']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Events - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/events.css">
</head>
<body>
    <div class="events-container">
        <header>
            <h1>Upcoming Events</h1>
            <div class="search-filter">
                <input type="text" id="event-search" placeholder="Search events...">
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <!-- Placeholder for event categories -->
                    <option value="conference">Conference</option>
                    <option value="workshop">Workshop</option>
                    <option value="concert">Concert</option>
                    <option value="sports">Sports</option>
                </select>
            </div>
        </header>

        <main class="events-grid">
            <?php if (isset($events_error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($events_error); ?>
                </div>
            <?php elseif ($events_result && $events_result->num_rows > 0): ?>
                <?php while($event = $events_result->fetch_assoc()): ?>
                    <div class="event-card" data-category="<?php echo strtolower(str_replace(' ', '-', $event['name'])); ?>">
                        <div class="event-header">
                            <h2><?php echo htmlspecialchars($event['name']); ?></h2>
                            <span class="event-date">
                                <?php 
                                $start_date = new DateTime($event['date']);
                                echo $start_date->format('M d, Y'); 
                                ?>
                            </span>
                            <span class="event-status <?php echo strtolower($event['status']); ?>">
                                <?php echo ucfirst(strtolower($event['status'])); ?>
                            </span>
                        </div>
                        <div class="event-details">
                            <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                            <div class="event-meta">
                                <span class="venue">
                                    <i class="icon-location"></i> 
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </span>
                                <span class="tickets-left">
                                    <?php echo $event['max_tickets'] - $event['tickets_sold']; ?> tickets left
                                </span>
                            </div>
                        </div>
                        <div class="event-footer">
                            <span class="ticket-price">
                                $<?php echo number_format($event['ticket_price'], 2); ?>
                            </span>
                            <form action="purchase_ticket.php" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                <button type="submit" class="btn-purchase" 
                                    <?php echo ($event['status'] !== 'published') ? 'disabled' : ''; ?>>
                                    <?php echo ($event['status'] === 'published') ? 'Purchase Ticket' : 'Not Available'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>No upcoming events at the moment.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../assets/js/events.js"></script>
</body>
</html>

<?php
$conn->close();
?>

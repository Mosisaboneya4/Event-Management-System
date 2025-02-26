<?php
session_start();
require_once '../../config/database.php';

// Ensure only organizers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../index.php");
    exit();
}

$conn = getDatabaseConnection();
$organizer_id = $_SESSION['user_id'];

// Fetch organizer's events
$events_query = "
    SELECT 
        event_id, 
        name, 
        date, 
        status,
        location,
        (SELECT COUNT(*) FROM tickets WHERE event_id = events.event_id) as total_tickets,
        (SELECT COUNT(*) FROM tickets WHERE event_id = events.event_id AND check_in_time IS NOT NULL) as checked_in_tickets
    FROM events 
    WHERE organizer_id = ?
    ORDER BY date
";
$events_stmt = $conn->prepare($events_query);
$events_stmt->bind_param("i", $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

// Fetch financial summary
$financial_query = "
    SELECT 
        COUNT(DISTINCT e.event_id) as total_events,
        SUM(t.ticket_price) as total_revenue,
        AVG(t.ticket_price) as avg_ticket_price
    FROM events e
    JOIN tickets t ON e.event_id = t.event_id
    WHERE e.organizer_id = ?
";
$financial_stmt = $conn->prepare($financial_query);
$financial_stmt->bind_param("i", $organizer_id);
$financial_stmt->execute();
$financial_result = $financial_stmt->get_result()->fetch_assoc();

// Upcoming events
$upcoming_events_query = "
    SELECT name, date, location
    FROM events 
    WHERE organizer_id = ? AND date >= CURDATE()
    ORDER BY date ASC 
    LIMIT 5
";
$upcoming_stmt = $conn->prepare($upcoming_events_query);
$upcoming_stmt->bind_param("i", $organizer_id);
$upcoming_stmt->execute();
$upcoming_events = $upcoming_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Organizer Dashboard</title>
</head>
<body>
    <style>
:root {
  --primary: #2563eb;
  --primary-dark: #1e40af;
  --secondary: #64748b;
  --success: #22c55e;
  --danger: #ef4444;
  --warning: #f59e0b;
  --background: #f8fafc;
  --card: #ffffff;
  --text: #1e293b;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, sans-serif;
  background: var(--background);
  color: var(--text);
  line-height: 1.6;
}

.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1rem;
  background: var(--card);
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.user-info h1 {
  font-size: 1.8rem;
  color: var(--text);
  margin-bottom: 0.5rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-profile {
  background: var(--secondary);
  color: white;
}

.btn-logout {
  background: var(--danger);
  color: white;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--card);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-card h3 {
  color: var(--secondary);
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.stat-card p {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--primary);
}

.event-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.event-card {
  background: var(--card);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.event-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.event-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.draft { background: #f3f4f6; color: var(--secondary); }
.published { background: #ecfdf5; color: var(--success); }
.cancelled { background: #fef2f2; color: var(--danger); }

.event-details {
  margin: 1rem 0;
}

.ticket-stats {
  margin-top: 1rem;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 8px;
  text-align: center;
}

.event-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-details {
  background: var(--primary);
  color: white;
}

.btn-manage {
  background: var(--secondary);
  color: white;
}

.upcoming-events {
  margin-top: 2rem;
}

.upcoming-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.upcoming-event-card {
  background: var(--card);
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.quick-actions {
  margin-top: 2rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.btn-create-event {
  background: var(--primary);
  color: white;
}

.btn-analytics {
  background: var(--warning);
  color: white;
}

.btn-tickets {
  background: var(--success);
  color: white;
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .event-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    text-align: center;
  }
}
.main-header {
    background: linear-gradient(to right, #4f46e5, #818cf8);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left h1 {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn {
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.btn-profile {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-profile:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.btn-logout {
    background-color: #dc2626;
    color: white;
}

.btn-logout:hover {
    background-color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@media (max-width: 640px) {
    .header-container {
        padding: 0 1rem;
    }
    
    .header-left h1 {
        font-size: 1.25rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
    }
}
        </style>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-info">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h1>
                <p>Organizer Dashboard</p>
            </div>
            <header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <h1>Event Management System</h1>
        </div>
        <div class="header-actions">
            <a href="profile.php" class="btn btn-profile">
                <i class="fas fa-user"></i>
                My Profile
            </a>
            <a href="../logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>
</header>


        </header>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Events</h3>
                <p><?php echo htmlspecialchars($financial_result['total_events'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>$<?php echo number_format($financial_result['total_revenue'] ?? 0, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Avg Ticket Price</h3>
                <p>$<?php echo number_format($financial_result['avg_ticket_price'] ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="events-list">
                <h2>My Events</h2>
                <?php if ($events_result->num_rows > 0): ?>
                    <div class="event-grid">
                        <?php while($event = $events_result->fetch_assoc()): ?>
                            <div class="event-card">
                                <div class="event-header">
                                    <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                                    <span class="event-status <?php echo strtolower($event['status']); ?>">
                                        <?php echo htmlspecialchars($event['status']); ?>
                                    </span>
                                </div>
                                <div class="event-details">
                                    <p><strong>Date:</strong> 
                                        <?php echo date('F d, Y', strtotime($event['date'])); ?> 
                                    </p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <div class="ticket-stats">
                                        <span>
                                            Tickets: <?php echo $event['checked_in_tickets']; ?> / <?php echo $event['total_tickets']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="event-actions">
                                    <a href="event_details.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-details">View Details</a>
                                    <a href="manage_event.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-manage">Manage Event</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't created any events yet.</p>
                <?php endif; ?>
            </div>

            <div class="upcoming-events">
                <h2>Upcoming Events</h2>
                <?php if ($upcoming_events->num_rows > 0): ?>
                    <div class="upcoming-list">
                        <?php while($event = $upcoming_events->fetch_assoc()): ?>
                            <div class="upcoming-event-card">
                                <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                                <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($event['date'])); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No upcoming events.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="create_event.php" class="btn btn-create-event">Create New Event</a>
                <a href="event_analytics.php" class="btn btn-analytics">Event Analytics</a>
                <a href="ticket_management.php" class="btn btn-tickets">Ticket Management</a>
            </div>
        </div>
    </div>

    <script src="../../assets/js/organizer_dashboard.js"></script>
</body>
</html>

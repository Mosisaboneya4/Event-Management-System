<?php
session_start();
require_once '../../config/database.php';

// Ensure only organizers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../login.php");
    exit();
}

$conn = getDatabaseConnection();
$organizer_id = $_SESSION['user_id'];

// Fetch overall event analytics
$analytics_query = "
    SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_events,
        SUM(CASE WHEN status = 'UPCOMING' THEN 1 ELSE 0 END) as upcoming_events,
        SUM(ticket_price * (SELECT COUNT(*) FROM tickets WHERE event_id = events.event_id)) as total_revenue
    FROM events
    WHERE organizer_id = ?
";
$analytics_stmt = $conn->prepare($analytics_query);
$analytics_stmt->bind_param("i", $organizer_id);
$analytics_stmt->execute();
$analytics_result = $analytics_stmt->get_result()->fetch_assoc();

// Fetch event-wise ticket sales
$event_sales_query = "
    SELECT 
        name,
        date,
        ticket_price,
        (SELECT COUNT(*) FROM tickets WHERE event_id = events.event_id) as tickets_sold,
        (SELECT COUNT(*) FROM tickets WHERE event_id = events.event_id AND check_in_time IS NOT NULL) as tickets_checked_in
    FROM events
    WHERE organizer_id = ?
    ORDER BY date DESC
    LIMIT 10
";
$event_sales_stmt = $conn->prepare($event_sales_query);
$event_sales_stmt->bind_param("i", $organizer_id);
$event_sales_stmt->execute();
$event_sales_result = $event_sales_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Analytics - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/organizer_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .analytics-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .event-sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .event-sales-table th, 
        .event-sales-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .event-sales-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="user-info">
                    <h1>Event Analytics</h1>
                    <p>Insights into Your Event Performance</p>
                </div>
            </header>

            <div class="analytics-container">
                <div class="analytics-card">
                    <h2>Overall Event Summary</h2>
                    <div class="chart-container">
                        <canvas id="eventStatusChart"></canvas>
                    </div>
                    <div>
                        <p><strong>Total Events:</strong> <?php echo $analytics_result['total_events']; ?></p>
                        <p><strong>Completed Events:</strong> <?php echo $analytics_result['completed_events']; ?></p>
                        <p><strong>Upcoming Events:</strong> <?php echo $analytics_result['upcoming_events']; ?></p>
                        <p><strong>Total Revenue:</strong> $<?php echo number_format($analytics_result['total_revenue'], 2); ?></p>
                    </div>
                </div>

                <div class="analytics-card">
                    <h2>Event Sales Overview</h2>
                    <table class="event-sales-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Ticket Price</th>
                                <th>Tickets Sold</th>
                                <th>Checked In</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($event = $event_sales_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td><?php echo date('F d, Y', strtotime($event['date'])); ?></td>
                                    <td>$<?php echo number_format($event['ticket_price'], 2); ?></td>
                                    <td><?php echo $event['tickets_sold']; ?></td>
                                    <td><?php echo $event['tickets_checked_in']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Event Status Chart
        const ctx = document.getElementById('eventStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Upcoming'],
                datasets: [{
                    data: [
                        <?php echo $analytics_result['completed_events']; ?>, 
                        <?php echo $analytics_result['upcoming_events']; ?>
                    ],
                    backgroundColor: ['#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Event Status Distribution'
                }
            }
        });
    </script>
</body>
</html>

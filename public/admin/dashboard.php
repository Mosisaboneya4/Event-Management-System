<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/admin_functions.php';

// Ensure only admin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch dashboard statistics
$conn = getDatabaseConnection();
$stats = [
    'total_users' => getTotalUsers($conn),
    'total_events' => getTotalEvents($conn),
    'total_tickets' => getTotalTickets($conn),
    'total_revenue' => getTotalRevenue($conn),
    'recent_events' => getRecentEvents($conn, 5),
    'user_roles_breakdown' => getUserRolesBreakdown($conn)
];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Event Management System</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-dashboard-container">

        <!-- Sidebar Navigation -->
        <?php include 'sidebar.php'; ?>
        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1>Admin Dashboard</h1>
                <div class="user-profile">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <img src="../../assets/images/default.png" alt="Admin Avatar">
                </div>
            </header>

            <!-- Dashboard Statistics -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <p><?php echo $stats['total_events']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Tickets</h3>
                    <p><?php echo $stats['total_tickets']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p>$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                </div>
            </section>

            <!-- Recent Events -->
            <section class="recent-events">
                <h2>Recent Events</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Tickets Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_events'] as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($event['date'])); ?></td>
                            <td><?php echo $event['tickets_sold']; ?></td>
                            <td>$<?php echo number_format($event['revenue'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- User Roles Pie Chart -->
            <section class="user-roles-chart">
                <h2>User Roles Distribution</h2>
                <canvas id="userRolesChart"></canvas>
            </section>
        </main>
    </div>

    <script>
        // User Roles Chart
        const ctx = document.getElementById('userRolesChart').getContext('2d');
        const userRolesData = <?php echo json_encode($stats['user_roles_breakdown']); ?>;
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(userRolesData),
                datasets: [{
                    data: Object.values(userRolesData),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', 
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'User Roles Distribution'
                }
            }
        });
    </script>
    <script src="../../assets/js/admin_dashboard.js"></script>
</body>
</html>

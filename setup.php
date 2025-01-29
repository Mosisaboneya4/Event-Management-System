<?php
require_once 'config/database.php';
require_once 'src/admin_functions.php';

// Database connection
$conn = getDatabaseConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize system database
if (initializeSystemDatabase($conn)) {
    echo "System initialization successful!\n";
    echo "\nDefault Users Created:\n";
    echo "1. Attendee Login:\n";
    echo "   Username: attendee_user\n";
    echo "   Password: Attendee2023!\n\n";
    
    echo "2. Organizer Login:\n";
    echo "   Username: event_organizer\n";
    echo "   Password: Organizer2023@\n\n";
    
    echo "3. Staff Login:\n";
    echo "   Username: event_staff\n";
    echo "   Password: Staff2023#\n\n";
    
    echo "4. Admin Login:\n";
    echo "   Username: system_admin\n";
    echo "   Password: Admin2023$\n";
} else {
    echo "System initialization failed. Check error logs.\n";
}

$conn->close();
?>

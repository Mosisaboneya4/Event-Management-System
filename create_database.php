<?php
// Database Configuration
$host = 'localhost';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$dbname = 'ems_database';
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";

if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
    
    // Connect to the new database
    $conn->select_db($dbname);

    // Create users table
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('attendee', 'organizer', 'staff', 'admin') NOT NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Create events table
    $events_table = "CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        date DATETIME NOT NULL,
        location VARCHAR(255),
        organizer_id INT,
        status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft',
        max_tickets INT,
        ticket_price DECIMAL(10,2),
        FOREIGN KEY (organizer_id) REFERENCES users(user_id)
    )";

    // Create tickets table
    $tickets_table = "CREATE TABLE IF NOT EXISTS tickets (
        ticket_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        attendee_id INT NOT NULL,
        ticket_code VARCHAR(50) UNIQUE NOT NULL,
        otp VARCHAR(10),
        otp_created_at DATETIME,
        is_otp_verified BOOLEAN DEFAULT FALSE,
        is_otp_burned BOOLEAN DEFAULT FALSE,
        check_in_time DATETIME,
        checked_by_staff_id INT,
        payment_status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        ticket_price DECIMAL(10,2),
        FOREIGN KEY (event_id) REFERENCES events(event_id),
        FOREIGN KEY (attendee_id) REFERENCES users(user_id),
        FOREIGN KEY (checked_by_staff_id) REFERENCES users(user_id)
    )";

    // Execute table creation
    if ($conn->query($users_table) === TRUE && 
        $conn->query($events_table) === TRUE && 
        $conn->query($tickets_table) === TRUE) {
        echo "Tables created successfully\n";

        // Insert default users
        $default_users = [
            ['system_admin', 'admin@ems.local', 'admin', password_hash('Admin2023$', PASSWORD_BCRYPT)],
            ['attendee_user', 'attendee@ems.local', 'attendee', password_hash('Attendee2023!', PASSWORD_BCRYPT)],
            ['event_organizer', 'organizer@ems.local', 'organizer', password_hash('Organizer2023@', PASSWORD_BCRYPT)],
            ['event_staff', 'staff@ems.local', 'staff', password_hash('Staff2023#', PASSWORD_BCRYPT)]
        ];

        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)");

        foreach ($default_users as $user) {
            $insert_stmt->bind_param("ssss", $user[0], $user[1], $user[2], $user[3]);
            $insert_stmt->execute();
        }

        echo "Default users inserted successfully\n";
    } else {
        echo "Error creating tables: " . $conn->error;
    }
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>

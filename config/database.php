<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ems_database');

// Create database connection
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database if not exists
    $create_db_query = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`";
    $conn->query($create_db_query);

    // Select the database
    $conn->select_db(DB_NAME);

    // Create users table if not exists
    $create_users_table = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('attendee', 'organizer', 'staff', 'admin') NOT NULL,
        is_active TINYINT(1) DEFAULT 1,  -- Add this line for is_active
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_users_table);

    // Create events table if not exists
    $create_events_table = "CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        date DATETIME NOT NULL,
        location VARCHAR(255),
        organizer_id INT,
        status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft',
        max_tickets INT,
        ticket_price DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (organizer_id) REFERENCES users(user_id)
    )";
    $conn->query($create_events_table);

    // Create tickets table if not exists
    $create_tickets_table = "CREATE TABLE IF NOT EXISTS tickets (
        ticket_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        attendee_id INT NOT NULL,
        ticket_code VARCHAR(50) UNIQUE NOT NULL,
        otp VARCHAR(10),
        otp_created_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(event_id),
        FOREIGN KEY (attendee_id) REFERENCES users(user_id)
    )";
    $conn->query($create_tickets_table);

    return $conn;
}

// Close database connection function
function closeDatabaseConnection($conn) {
    $conn->close();
}
?>
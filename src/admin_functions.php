<?php
// Admin dashboard statistics and utility functions

function getTotalUsers($conn) {
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'attendee'";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'];
}

function getTotalEvents($conn) {
    $query = "SELECT COUNT(*) as total FROM events";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'];
}

function getTotalTickets($conn) {
    $query = "SELECT COUNT(*) as total FROM tickets";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'];
}

function getTotalRevenue($conn) {
    $query = "SELECT COALESCE(SUM(ticket_price), 0) as total_revenue FROM tickets WHERE payment_status = 'completed'";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total_revenue'];
}

function getRecentEvents($conn, $limit = 5) {
    $query = "
        SELECT 
            e.name, 
            e.date, 
            COUNT(t.ticket_id) as tickets_sold,
            COALESCE(SUM(t.ticket_price), 0) as revenue
        FROM 
            events e
        LEFT JOIN 
            tickets t ON e.event_id = t.event_id AND t.payment_status = 'completed'
        GROUP BY 
            e.event_id, e.name, e.date
        ORDER BY 
            e.date DESC
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserRolesBreakdown($conn) {
    $query = "
        SELECT 
            role, 
            COUNT(*) as count 
        FROM 
            users 
        WHERE 
            role = 'attendee'
        GROUP BY 
            role
    ";
    
    $result = $conn->query($query);
    $roles = [];
    
    while ($row = $result->fetch_assoc()) {
        $roles[$row['role']] = intval($row['count']);
    }
    
    return $roles;
}

function createAdminAuditLog($conn, $user_id, $action, $details) {
    $query = "
        INSERT INTO admin_audit_logs 
        (admin_user_id, action, action_details, action_timestamp) 
        VALUES (?, ?, ?, NOW())
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $user_id, $action, $details);
    return $stmt->execute();
}

function validateAdminAction($conn, $admin_id, $required_permission) {
    $query = "
        SELECT 
            permission_level 
        FROM 
            admin_permissions 
        WHERE 
            user_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $permissions = $result->fetch_assoc();
    return ($permissions['permission_level'] >= $required_permission);
}

function generateAdminActionToken($admin_id) {
    // Create a secure, time-limited token for admin actions
    $token = bin2hex(random_bytes(16)); // 32 character token
    $expiry = time() + (30 * 60); // 30 minutes expiry
    
    // Store token in database with expiry
    global $conn;
    $query = "
        INSERT INTO admin_action_tokens 
        (admin_id, token, expiry) 
        VALUES (?, ?, FROM_UNIXTIME(?))
        ON DUPLICATE KEY UPDATE 
        token = ?, 
        expiry = FROM_UNIXTIME(?)
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issis", $admin_id, $token, $expiry, $token, $expiry);
    $stmt->execute();
    
    return $token;
}

function verifyAdminActionToken($token, $admin_id) {
    global $conn;
    $query = "
        SELECT 
            admin_id, 
            expiry 
        FROM 
            admin_action_tokens 
        WHERE 
            token = ? 
            AND admin_id = ? 
            AND expiry > NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $token, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function createDefaultUsers($conn) {
    $default_users = [
        [
            'username' => 'attendee_user',
            'email' => 'attendee@ems.local',
            'role' => 'attendee',
            'password' => password_hash('Attendee2023!', PASSWORD_BCRYPT),
            'status' => 'active',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ],
        [
            'username' => 'event_organizer',
            'email' => 'organizer@ems.local',
            'role' => 'organizer',
            'password' => password_hash('Organizer2023@', PASSWORD_BCRYPT),
            'status' => 'active',
            'first_name' => 'Emily',
            'last_name' => 'Smith'
        ],
        [
            'username' => 'event_staff',
            'email' => 'staff@ems.local',
            'role' => 'staff',
            'password' => password_hash('Staff2023#', PASSWORD_BCRYPT),
            'status' => 'active',
            'first_name' => 'Michael',
            'last_name' => 'Johnson'
        ],
        [
            'username' => 'system_admin',
            'email' => 'admin@ems.local',
            'role' => 'admin',
            'password' => password_hash('Admin2023$', PASSWORD_BCRYPT),
            'status' => 'active',
            'first_name' => 'Admin',
            'last_name' => 'User'
        ]
    ];

    $query = "INSERT INTO users (username, email, role, password, status, first_name, last_name, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) 
              ON DUPLICATE KEY UPDATE 
              email = VALUES(email), 
              password = VALUES(password), 
              status = VALUES(status),
              first_name = VALUES(first_name),
              last_name = VALUES(last_name)";
    
    $stmt = $conn->prepare($query);

    foreach ($default_users as $user) {
        $stmt->bind_param(
            "sssssss", 
            $user['username'], 
            $user['email'], 
            $user['role'], 
            $user['password'], 
            $user['status'],
            $user['first_name'],
            $user['last_name']
        );
        $stmt->execute();
    }

    return true;
}

function initializeSystemDatabase($conn) {
    // Create necessary tables if not exists
    $tables = [
        "users" => "
            CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('attendee', 'organizer', 'staff', 'admin') NOT NULL,
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                phone_number VARCHAR(20),
                profile_picture VARCHAR(255),
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ",
        "events" => "
            CREATE TABLE IF NOT EXISTS events (
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
            )
        ",
        "tickets" => "
            CREATE TABLE IF NOT EXISTS tickets (
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
            )
        "
    ];

    // Create tables
    foreach ($tables as $table_name => $table_query) {
        if (!$conn->query($table_query)) {
            error_log("Failed to create table: $table_name - " . $conn->error);
            return false;
        }
    }

    // Create default users
    createDefaultUsers($conn);

    return true;
}

function buildUserQuery($search, $role_filter, $status_filter, $offset, $per_page) {
    $query = "SELECT user_id, username, email, role, is_active, created_at FROM users WHERE 1=1";
    
    if (!empty($search)) {
        $query .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
    }
    
    if (!empty($role_filter)) {
        $query .= " AND role = '$role_filter'";
    }
    
    if ($status_filter !== '') {
        $query .= " AND is_active = '$status_filter'";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT $offset, $per_page";
    
    return $query;
}

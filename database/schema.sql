-- Create Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('attendee', 'organizer', 'staff', 'admin') NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create Events Table
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    venue VARCHAR(100),
    total_tickets INT NOT NULL,
    available_tickets INT NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft',
    FOREIGN KEY (organizer_id) REFERENCES users(user_id)
);

-- Create Tickets Table
CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    attendee_id INT NOT NULL,
    ticket_code VARCHAR(50) UNIQUE NOT NULL,
    otp VARCHAR(6) NOT NULL,
    otp_created_at TIMESTAMP NULL,
    otp_expiry TIMESTAMP NULL,
    is_otp_verified BOOLEAN DEFAULT FALSE,
    is_otp_burned BOOLEAN DEFAULT FALSE,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_checked_in BOOLEAN DEFAULT FALSE,
    check_in_time TIMESTAMP NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (attendee_id) REFERENCES users(user_id)
);

-- Create Feedback Table
CREATE TABLE event_feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    attendee_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (attendee_id) REFERENCES users(user_id)
);

-- Create Lost and Found Table
CREATE TABLE lost_and_found (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    item_description TEXT NOT NULL,
    found_by_staff_id INT NOT NULL,
    found_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unclaimed', 'claimed') DEFAULT 'unclaimed',
    claimed_by_user_id INT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (found_by_staff_id) REFERENCES users(user_id),
    FOREIGN KEY (claimed_by_user_id) REFERENCES users(user_id)
);

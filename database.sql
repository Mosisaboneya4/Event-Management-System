CREATE TABLE attendee_photos (
    photo_id INT PRIMARY KEY AUTO_INCREMENT,
    attendee_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    description TEXT,
    upload_date DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    staff_comment TEXT,
    FOREIGN KEY (attendee_id) REFERENCES users(user_id)
);

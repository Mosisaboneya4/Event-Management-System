<?php
require_once '../config/database.php';

// Generate a secure random password
function generateSecurePassword($length = 16) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-=[]{}|;:,.<>?';
    $password = '';
    $charLength = strlen($characters);
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $charLength - 1)];
    }
    
    return $password;
}

try {
    // Establish database connection
    $conn = getDatabaseConnection();
    
    // New admin credentials
    $username = 'system_admin_new';
    $email = 'admin_new@ems.local';
    $role = 'admin';
    $rawPassword = generateSecurePassword();
    $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT);
    
    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            email = VALUES(email), 
                            password = VALUES(password)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    $stmt->execute();
    
    // Output credentials (in a real-world scenario, this would be securely communicated)
    echo "New System Admin Credentials:\n";
    echo "Username: $username\n";
    echo "Password: $rawPassword\n";
    echo "Email: $email\n";
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

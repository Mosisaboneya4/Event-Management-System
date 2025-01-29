<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/email_config.php';

define('STEP_PERSONAL_INFO', 1);
define('STEP_EMAIL_VERIFICATION', 2);
define('STEP_COMPLETE', 3);

if (!isset($_SESSION['registration_step'])) {
    $_SESSION['registration_step'] = STEP_PERSONAL_INFO;
}

$error = $success = '';

// Define all required functions first
function handlePersonalInfo($conn) {
    global $error, $success;
    
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        throw new Exception("All fields are required.");
    }

    if ($password !== $confirm_password) {
        throw new Exception("Passwords do not match.");
    }

    if (strlen($password) < 5) {
        throw new Exception("Password must be at least 5 characters long.");
    }

    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception("Password must contain at least one uppercase letter.");
    }

    if (!preg_match('/[a-z]/', $password)) {
        throw new Exception("Password must contain at least one lowercase letter.");
    }

    if (!preg_match('/[0-9]/', $password)) {
        throw new Exception("Password must contain at least one number.");
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        throw new Exception("Password must contain at least one special character.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['email'] === $email) {
            throw new Exception("This email is already registered");
        }
        if ($row['username'] === $username) {
            throw new Exception("This username is already taken");
        }
    }

    $verification_code = generateVerificationCode();
    $_SESSION['registration_data'] = [
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'verification_code' => $verification_code
    ];

    if (sendOTPEmail($email, $verification_code, "Account Verification")) {
        $_SESSION['registration_step'] = STEP_EMAIL_VERIFICATION;
        $success = "Verification code sent to your email.";
    } else {
        throw new Exception("Failed to send verification code.");
    }
}

function handleEmailVerification($conn) {
    global $error;
    
    $user_code = trim(filter_input(INPUT_POST, 'verification_code', FILTER_SANITIZE_STRING));
    
    if ($user_code === $_SESSION['registration_data']['verification_code']) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'attendee')");
        $stmt->bind_param("sss", 
            $_SESSION['registration_data']['username'],
            $_SESSION['registration_data']['email'],
            $_SESSION['registration_data']['password']
        );

        if ($stmt->execute()) {
            cleanup();
            header("Location: ../../index.php?registration=success");
            exit();
        } else {
            throw new Exception("Registration failed. Please try again.");
        }
    } else {
        throw new Exception("Invalid verification code.");
    }
}

function generateVerificationCode() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function cleanup() {
    unset($_SESSION['registration_data']);
    $_SESSION['registration_step'] = STEP_COMPLETE;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDatabaseConnection();
        if ($_SESSION['registration_step'] === STEP_PERSONAL_INFO) {
            handlePersonalInfo($conn);
        }
        elseif ($_SESSION['registration_step'] === STEP_EMAIL_VERIFICATION) {
            handleEmailVerification($conn);
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendee Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .registration-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }

        .registration-form h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 12px;
            color: #666;
        }

        .btn-register, .btn-verify {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-register:hover, .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .error-message {
            background-color: #ffdddd;
            color: #ff0000;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    /* Add to existing styles */
    .password-strength-meter {
        height: 4px;
        background: #ddd;
        margin: 5px 0;
        border-radius: 2px;
    }

    .strength-weak { background: #ff4d4d; }
    .strength-medium { background: #ffd700; }
    .strength-strong { background: #00cc00; }

    .password-requirements {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        font-size: 0.9em;
        text-align: left;
    }

    .requirement {
        display: flex;
        align-items: center;
        margin: 5px 0;
        color: #666;
    }

    .requirement.met {
        color: #00cc00;
    }

    .requirement i {
        margin-right: 5px;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102,126,234,0.2);
    }

    .show-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
    }

    </style>
</head>
<body>
    <div class="registration-container">
        <form method="POST" action="" class="registration-form">
    
                    <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($_SESSION['registration_step'] === STEP_PERSONAL_INFO): ?>
                <h2>Attendee Registration</h2>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" class="btn-register">Continue</button>

            <?php elseif ($_SESSION['registration_step'] === STEP_EMAIL_VERIFICATION): ?>
                <h2>Email Verification</h2>
                <p>Please check your email: <?php echo htmlspecialchars($_SESSION['registration_data']['email']); ?></p>
                <div class="form-group">
                    <input type="text" id="verification_code" name="verification_code" 
                           required maxlength="6" pattern="\d{6}" 
                           placeholder="Enter 6-digit code">
                </div>
                <button type="submit" class="btn-verify">Verify Code</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendOTPEmail($to_email, $otp, $event_name) {
    $mail = new PHPMailer(true);

    try {
        // Enable verbose debug output
        $mail->SMTPDebug = 2;  // Detailed debug output
        
        // SMTP configuration (replace with your email service details)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yon95.haile@gmail.com';  // Your email
        $mail->Password   = 'kahe kzmp ldmp tyxx';     // App password or generated token
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPSecure = 'tls';

        // Email content
        $mail->setFrom('mosisaboneya4@gmail.com', 'Event Management System');
        $mail->addAddress($to_email);
        $mail->isHTML(true);

        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "
            <html>
            <body>
                <h2>Verification Code for {$event_name}</h2>
                <p>Your verification code is: <strong>{$otp}</strong></p>
                <p>This code will expire in 15 minutes. Do not share this code with anyone.</p>
            </body>
            </html>
        ";
        $mail->AltBody = "Your verification code is: {$otp}. This code will expire in 15 minutes.";

        // Attempt to send email
        $mail_sent = $mail->send();
        
        // Log detailed information
        error_log("Email Sending Details:");
        error_log("To Email: {$to_email}");
        error_log("OTP: {$otp}");
        error_log("Email Sent Status: " . ($mail_sent ? 'Success' : 'Failed'));
        error_log("PHPMailer Error Info: " . $mail->ErrorInfo);

        return $mail_sent;
    } catch (Exception $e) {
        // Log the error with more details
        error_log("OTP Email Error: " . $mail->ErrorInfo);
        error_log("PHPMailer Exception: " . $e->getMessage());
        error_log("Email Details - To: {$to_email}, OTP: {$otp}, Event: {$event_name}");
        return false;
    }
}

function sendOTPSMS($phone_number, $otp, $event_name) {
    // Placeholder for SMS sending functionality
    // You would integrate with an SMS gateway like Twilio
    $message = "Your OTP for {$event_name} is: {$otp}. This OTP will expire in 1 hour.";
    
    // Simulate SMS sending (replace with actual SMS gateway)
    error_log("Sending SMS to {$phone_number}: {$message}");
    return true;
}
?>

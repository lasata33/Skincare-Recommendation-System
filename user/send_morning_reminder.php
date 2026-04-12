<?php
require __DIR__ . '/../vendor/autoload.php';
require_once "../config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get all non-admin users
$sql = "SELECT email, username FROM users_db WHERE email NOT LIKE 'admin%@%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipientEmail = $row['email'];
        $username = $row['username'];

        $mail = new PHPMailer(true);

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // From & To
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($recipientEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "🌞 Good Morning, {$username}!";
            $mail->Body    = "
                <p>Hi {$username},</p>
                <p>Rise and shine! Wishing you a productive and happy day ahead.</p>
                <p>☕ Stay positive and keep smiling!</p>
            ";

            $mail->send();
            echo "✅ Morning reminder sent to: {$recipientEmail}<br>";
        } catch (Exception $e) {
            echo "❌ Failed to send to {$recipientEmail}: {$mail->ErrorInfo}<br>";
        }
    }
} else {
    echo "ℹ No non-admin email addresses found.";
}

$conn->close();
?>

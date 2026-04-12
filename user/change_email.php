<?php
session_start();
require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = Database::connect();
$userObj = new User($db);
$user_id = $_SESSION['user_id'] ?? null;

$error_message = '';
$success_message = '';

// ✅ Step 1: Handle new email submission and send OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];
    $otp = rand(100000, 999999);

    // Store OTP per user
    $_SESSION['otp'][$user_id] = [
        'email' => $new_email,
        'code' => $otp,
        'stage' => true
    ];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skinsync11@gmail.com';
        $mail->Password = 'yhimdywprznxxmnm'; // 🔐 Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('skinsync11@gmail.com', 'SkinSync');
        $mail->addAddress($new_email);
        $mail->Subject = 'SkinSync Email Verification';
        $mail->Body    = "Your OTP is: $otp";

        $mail->send();
        $success_message = "OTP sent to $new_email";
    } catch (Exception $e) {
        $error_message = "Mailer Error: {$mail->ErrorInfo}";
    }
}

// ✅ Step 2: Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entered_otp'])) {
    $entered_otp = $_POST['entered_otp'];
    $stored = $_SESSION['otp'][$user_id] ?? null;

    if ($stored && $entered_otp == $stored['code']) {
        $updated = $userObj->updateEmail($user_id, $stored['email']);
        if ($updated) {
            unset($_SESSION['otp'][$user_id]);
            $_SESSION['success_message'] = "Email updated successfully!";
            header("Location: userdashboard.php?section=profile");
            exit();
        } else {
            $error_message = "Failed to update email.";
        }
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}

// ✅ Step 3: Fallback check — reset if session mismatched
if (isset($_SESSION['otp']) && !isset($_SESSION['otp'][$user_id])) {
    unset($_SESSION['otp']); // Clear stale OTPs
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Email</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<div class="profile-container">
    <h2>🔐 Change Email</h2>

    <?php if ($success_message): ?>
        <div class="alert success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['otp'][$user_id]['stage'])): ?>
        <form method="POST">
            <label for="entered_otp">Enter OTP sent to <?= htmlspecialchars($_SESSION['otp'][$user_id]['email']) ?></label>
            <input type="text" name="entered_otp" required>
            <button type="submit">Verify & Update</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <label for="new_email">Enter New Email</label>
            <input type="email" name="new_email" required>
            <button type="submit">Send OTP</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

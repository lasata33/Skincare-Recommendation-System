<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ReminderMailer {
    private $logFile;
    private $dailyGoal = 2000;

    public function __construct($logPath) {
        $this->logFile = $logPath;
        file_put_contents($this->logFile, "[" . date("Y-m-d H:i:s") . "] Script started\n", FILE_APPEND);
    }

    public function sendWaterReminder($email, $username) {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                file_put_contents($this->logFile, "DEBUG: $str\n", FILE_APPEND);
            };

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'skinsync11@gmail.com';
            $mail->Password   = 'yhimdywprznxxmnm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('skinsync11@gmail.com', 'Water Reminder');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Hello {$username}, time to drink water!";
            $mail->Body    = "
                <p>Hi {$username},</p>
                <p>Remember to drink at least <strong>{$this->dailyGoal} ml</strong> of water today to stay hydrated 💙.</p>
                <p>Your body will thank you!</p>
            ";

            try {
                $mail->send();
                $this->log("✅ Water reminder sent to: {$email}");
            } catch (Exception $e) {
                $this->log("⚠ Port 587 failed for {$email}, trying 465...");
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->send();
                $this->log("✅ Water reminder sent via Port 465 to: {$email}");
            }

        } catch (Exception $e) {
            $this->log("❌ Failed to send to {$email}: {$mail->ErrorInfo}");
        }
    }

    public function log($message) {
        echo $message . "\n";
        file_put_contents($this->logFile, $message . "\n", FILE_APPEND);
    }

    public function finish() {
        file_put_contents($this->logFile, "[" . date("Y-m-d H:i:s") . "] Script finished\n\n", FILE_APPEND);
    }
}
?>

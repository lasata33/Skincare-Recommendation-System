<?php
require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/ReminderMailer.php";
require_once __DIR__ . '/../vendor/autoload.php';

$logPath = __DIR__ . '/water_reminder_log.txt';

$db = Database::connect();
$userObj = new User($db);
$mailer = new ReminderMailer($logPath);

$users = $userObj->getAllNonAdminUsers();

if (count($users) > 0) {
    foreach ($users as $user) {
        $mailer->sendWaterReminder($user['email'], $user['username']);
    }
} else {
    $mailer->log("ℹ No non-admin email addresses found.");
}

$mailer->finish();
?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/WaterTracker.php";

header("Content-Type: application/json");

$db = Database::connect();
$userObj = new User($db);
$waterObj = new WaterTracker($db);

$user_id = $_SESSION['user_id'] ?? null;
$amount = $_POST['amount'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid amount"]);
    exit;
}

$amount = (int) $amount;

if ($amount > 5000) {
    echo json_encode(["success" => false, "error" => "🚫 You can't enter more than 5000ml. That's a waterfall 😭💧"]);
    exit;
}

$success = $waterObj->addWater($user_id, $amount);
if (!$success) {
    echo json_encode(["success" => false, "error" => "Database error"]);
    exit;
}

$todayTotal = $waterObj->getTodayTotal($user_id);
$skinType = $userObj->getSkinType($user_id);
$dailyGoal = $waterObj->getGoalForSkinType($skinType);

echo json_encode([
    "success" => true,
    "todayTotal" => $todayTotal,
    "dailyGoal" => $dailyGoal
]);
?>

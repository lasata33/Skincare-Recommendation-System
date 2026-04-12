<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/WeatherService.php";

$db = Database::connect();
$userObj = new User($db);
$weatherObj = new WeatherService($db);

$user_id = $_SESSION['user_id'];
$city = $userObj->getCity($user_id);
$skin_type = strtolower(trim($userObj->getSkinType($user_id)));

$weather = $weatherObj->getWeather($city);
if (!$weather) {
    $weather = ['temp' => null, 'humidity' => null, 'condition' => 'Unknown'];
}

$temp = $weather['temp'];
$humidity = $weather['humidity'];
$condition = $weather['condition'];
$weather_condition = $weatherObj->simplifyCondition($temp, $humidity, $condition);
$tip = $weatherObj->getTip($skin_type, $weather_condition);

// ✅ JSON output for home.php
if (isset($_GET['mode']) && $_GET['mode'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        "temp" => $temp,
        "humidity" => $humidity,
        "condition" => $condition,
        "skin_type" => $skin_type,
        "tip" => $tip
    ]);
    exit;
}
?>

<h2>☀️ Weather-Based Skincare Tips</h2>

<div class="weather-box">
    <h2>Weather in <?= htmlspecialchars($city) ?> 🌤️</h2>
    <p><strong>Temperature:</strong> <?= $temp ?>°C</p>
    <p><strong>Humidity:</strong> <?= $humidity ?>%</p>
    <p><strong>Condition:</strong> <?= $condition ?></p>
    <div class="weather-tip">
        <h3>💡 Skincare Tip:</h3>
        <p><?= htmlspecialchars($tip) ?></p>
    </div>
</div>

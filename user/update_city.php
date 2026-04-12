<?php
session_start();
require_once "../classes/Database.php";
require_once "../classes/User.php";

$db = Database::connect();
$userObj = new User($db);

$user_id = $_SESSION['user_id'] ?? null;
$city = $_POST['city'] ?? '';

if ($user_id && $city) {
    $userObj->updateCity($user_id, $city);
}

header("Location: profile.php");
exit;

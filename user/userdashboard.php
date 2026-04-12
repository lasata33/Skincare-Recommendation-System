<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/User.php';

$db = Database::connect();
$user = new User($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userData = $user->getUserById($userId);
$quizLevel = $userData['quiz_level_completed'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="stylesheet" href="userdash.css">
</head>
<body>

<div class="sidebar">
    <h2>Skinsync</h2>
    <a href="?section=home">🏠 Home</a>
    <a href="?section=profile">👤 Profile</a>
    <a href="?section=recommends">✨ Skinsync Recommends</a>
    <a href="?section=ingredient">🔍 Ingredient Analyzer</a>
    <a href="?section=water">💧 Water Tracker</a>
    <a href="?section=weather">☀ Weather Tips</a>
    <a href="?section=history">📜 History</a>
    <a href="../index.php">🚪 Logout</a>
</div>

<div class="main">


    <div class="section">
        <?php
        $section = $_GET['section'] ?? 'home';
        $file = __DIR__ . '/' . $section . '.php';

        if (file_exists($file)) {
            include $file;
        } else {
            echo "<h2>Page not found</h2>";
        }
        ?>
    </div>
</div>

</body>
</html>

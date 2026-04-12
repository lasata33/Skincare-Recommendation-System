<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/AdminDashboard.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$dashboard = new AdminDashboard($db);
$stats = $dashboard->getStats();
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="admin_base.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
  <li><a href="manage_users.php">👥 Manage Users</a></li>
  <li><a href="manage_quiz.php">❓ Manage Quiz </a></li>
  <li><a href="../products/view_products.php">🛍️ Manage Products</a></li>
  <li><a href="../index.php" class="logout">🚪 Logout</a></li>
</ul>

</div>

<div class="main-content">
    <header>
        <h1>Welcome, Admin</h1>
        <p>Manage the skincare recommendation system here.</p>
    </header>

    <section class="cards">
    <div class="card">
        <h3>Total Users</h3>
        <p><?= $stats['total_users'] ?></p>
    </div>
    <div class="card">
        <h3>Quiz Taken</h3>
        <p><?= $stats['quiz_taken'] ?></p>
    </div>
    <div class="card">
        <h3>Products Listed</h3>
        <p><?= $stats['total_products'] ?></p>
    </div>
</section>

</div>

</body>
</html>

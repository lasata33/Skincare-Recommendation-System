<?php
session_start();
$userChange = $_SESSION['skin_type_changed'] ?? null;
if (!$userChange) {
    header("Location: userdashboard.php?section=recommends");
    exit();
}
$username = $_SESSION['username'] ?? 'User';
?>
<div class="result-card">
    <h2>🎉 Hey <?= htmlspecialchars($username) ?>!</h2>
    <p>Your skin type has changed from 
       <strong><?= htmlspecialchars($userChange['old'] ?: 'Unknown') ?></strong> 
       to <strong><?= htmlspecialchars($userChange['new']) ?></strong> 💖</p>
    <a href="userdashboard.php?section=recommends" class="btn">Go to Recommendations</a>
</div>

<style>
.result-card {
    background: #fff0f5;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    max-width: 500px;
    margin: 40px auto;
    color: #d868a9;
    font-family: 'Poppins', sans-serif;
    box-shadow: 0 4px 10px rgba(216, 104, 169, 0.2);
}
.result-card h2 {
    margin-bottom: 10px;
    color: #c7579a;
}
.result-card .btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #d868a9;
    color: #fff;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.3s ease;
}
.result-card .btn:hover {
    background: #c7579a;
}
</style>

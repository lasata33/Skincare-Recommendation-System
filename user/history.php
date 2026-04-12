<?php

require_once "../classes/Database.php";
require_once "../classes/IngredientAnalyzer.php";
require_once "../classes/User.php";

$db = Database::connect();
$analyzer = new IngredientAnalyzer($db);
$userObj = new User($db);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<p>User not found. Please login.</p>";
    exit();
}

// Get scan history
$scanHistory = $analyzer->getScanHistory($user_id, 20);

// Get user's current and past skin types from users_db
$currentSkinType = $userObj->getSkinType($user_id);

?>

<h2>📜 Your Activity History</h2>
<p>Here's your recent product scans and skin information:</p>

<div style="margin-bottom: 30px; padding: 15px; background: #fff6fa; border-radius: 12px; border: 2px solid #ffe0ef;">
    <h3 style="color: #d868a9; margin-top: 0;">Current Skin Type</h3>
    <p style="font-size: 1.2em; color: #a14c7e;"><strong><?php echo ucfirst(htmlspecialchars($currentSkinType)); ?></strong></p>
    <p style="color: #a14c7e; font-size: 0.9em;">This is based on your latest quiz result.</p>
</div>

<h3 style="color: #d868a9;">Product Scan History</h3>

<?php if (!empty($scanHistory)): ?>
<div class="history-container">
    <?php foreach ($scanHistory as $entry): ?>
        <div class="history-card">
            <div class="history-badge scan-badge">📷 Product Scan</div>
            <h4><?php echo htmlspecialchars($entry['product_name']); ?></h4>
            <p><strong>Match Score:</strong> <span class="score-badge"><?php echo $entry['analysis_result']; ?>%</span></p>
            <p><strong>Date:</strong> <?php echo date("M d, Y • H:i", strtotime($entry['date_scanned'])); ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p>😢 No product scans yet. Try analyzing a product to get started!</p>
<?php endif; ?>


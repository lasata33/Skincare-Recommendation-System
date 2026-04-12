<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/IngredientAnalyzer.php';
require_once 'classes/User.php';

$db = Database::connect();

// Simulate user_id = 1 for testing
$user_id = 1;

echo "<h2>Testing Combined History</h2>";
echo "<h3>User ID: " . $user_id . "</h3>";

// Test 1: Check scan history
echo "<h3>1. Scan History from ingredientanalysis table:</h3>";
$sql = "SELECT product_name, analysis_result, date_scanned FROM ingredientanalysis WHERE user_id = $user_id";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " scans<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['product_name'] . " (" . $row['analysis_result'] . "%) on " . $row['date_scanned'] . "<br>";
    }
} else {
    echo "❌ No scans found<br>";
}

// Test 2: Check skin type history
echo "<h3>2. Skin Type History from skintype_history table:</h3>";
$sql = "SELECT old_skintype, new_skintype, changed_at FROM skintype_history WHERE user_id = $user_id";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " changes<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- Changed from " . $row['old_skintype'] . " to " . $row['new_skintype'] . " on " . $row['changed_at'] . "<br>";
    }
} else {
    echo "❌ No skin type changes found<br>";
}

// Test 3: Test getCombinedHistory method
echo "<h3>3. getCombinedHistory() method result:</h3>";
$analyzer = new IngredientAnalyzer($db);
$combined = $analyzer->getCombinedHistory($user_id, 20);

if (!empty($combined)) {
    echo "Found " . count($combined) . " total items<br>";
    echo "<pre>";
    print_r($combined);
    echo "</pre>";
} else {
    echo "❌ getCombinedHistory returned empty<br>";
}

$db->close();
?>

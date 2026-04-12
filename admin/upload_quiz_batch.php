<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
AdminAuth::requireAdmin();

$db = Database::connect();

$level = $_GET['level'] ?? null;
if (!$level || !in_array($level, ['basic', 'intermediate', 'advanced'])) {
    die("Invalid level");
}

$csvPath = __DIR__ . "/quiz_csv/{$level}_quiz.csv";
$progressFile = __DIR__ . "/quiz_csv/quiz_progress.json";

if (!file_exists($csvPath)) die("CSV not found");

$progress = file_exists($progressFile) ? json_decode(file_get_contents($progressFile), true) : [];
$start = $progress[$level] ?? 0;

$handle = fopen($csvPath, "r");
$rows = [];
$index = 0;
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if ($index === 0) { $index++; continue; } // skip header
    if ($index > $start && count($rows) < 5) $rows[] = $data;
    $index++;
}
fclose($handle);

if (empty($rows)) {
    echo "<p style='color:orange;'>⚠️ No new questions to insert for <strong>$level</strong>.</p>";
    exit;
}

// Check for duplicates before inserting
$checkStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM quiz_questions WHERE question = ?");
$duplicates = [];

foreach ($rows as $q) {
    $checkStmt->bind_param("s", $q[0]);
    $checkStmt->execute();
    $result = $checkStmt->get_result()->fetch_assoc();
    if ($result['cnt'] > 0) {
        $duplicates[] = $q[0];
    }
}

if (!empty($duplicates)) {
    echo "<p style='color:red; font-weight:bold;'>❌ Found " . count($duplicates) . " duplicate question(s):</p>";
    echo "<ul style='color:red;'>";
    foreach ($duplicates as $dup) {
        echo "<li>" . htmlspecialchars($dup) . "</li>";
    }
    echo "</ul>";
    echo "<p style='color:orange;'>Please remove duplicates from your CSV file and try again.</p>";
    echo '<a href="manage_quiz.php">← Back to Manage Quiz</a>';
    exit;
}

$checkStmt->close();

$stmt = $db->prepare("INSERT INTO quiz_questions 
    (question, option_a, option_b, option_c, option_d, map_a, map_b, map_c, map_d, level) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$inserted = 0;
foreach ($rows as $q) {
    $stmt->bind_param("ssssssssss", 
        $q[0], $q[1], $q[2], $q[3], $q[4],
        $q[5], $q[6], $q[7], $q[8], $q[9]
    );
    $stmt->execute();
    $inserted++;
}

$progress[$level] = $start + $inserted;
file_put_contents($progressFile, json_encode($progress));

echo "<p style='color:green; font-weight:bold;'>✅ Added $inserted new <strong>$level</strong> questions!</p>";
echo '<a href="manage_quiz.php">← Back to Manage Quiz</a>';
?>

<?php


require_once 'classes/Database.php';

$db = Database::connect();

if (!$db) {
    echo "❌ Error: Could not connect to database.";
    exit();
}

// Check if table exists
$sql = "SHOW TABLES LIKE 'skintype_history'";
$result = $db->query($sql);

if ($result && $result->num_rows > 0) {
    echo "✅ Table 'skintype_history' EXISTS<br><br>";
    
    // Show table structure
    $sql = "DESCRIBE skintype_history";
    $result = $db->query($sql);
    
    echo "<strong>Table Structure:</strong><br>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " (" . $row['Null'] . ", " . $row['Key'] . ")<br>";
    }
    echo "</pre>";
    
    // Count records
    $sql = "SELECT COUNT(*) as count FROM skintype_history";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    echo "<br><strong>Total Records:</strong> " . $row['count'] . "<br>";
    
    // Show recent records
    if ($row['count'] > 0) {
        echo "<br><strong>Recent Skin Type Changes:</strong><br>";
        $sql = "SELECT user_id, old_skintype, new_skintype, changed_at FROM skintype_history ORDER BY changed_at DESC LIMIT 5";
        $result = $db->query($sql);
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>User ID</th><th>Old Skin Type</th><th>New Skin Type</th><th>Changed At</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['old_skintype'] . "</td>";
            echo "<td>" . $row['new_skintype'] . "</td>";
            echo "<td>" . $row['changed_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "❌ Table 'skintype_history' DOES NOT EXIST<br><br>";
    echo "Please run this SQL query in phpMyAdmin:<br><br>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "CREATE TABLE IF NOT EXISTS skintype_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    old_skintype VARCHAR(50) NOT NULL,
    new_skintype VARCHAR(50) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_db(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (changed_at)
);";
    echo "</pre>";
}

$db->close();
?>

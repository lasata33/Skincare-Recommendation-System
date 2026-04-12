<?php
/**
 * Setup Script for Skin Type History Tracking
 * Run this once to create the skintype_history table
 */

require_once 'classes/Database.php';

$db = Database::connect();

if (!$db) {
    echo "Error: Could not connect to database.";
    exit();
}

// Create skintype_history table
$sql = "CREATE TABLE IF NOT EXISTS skintype_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    old_skintype VARCHAR(50) NOT NULL,
    new_skintype VARCHAR(50) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_db(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (changed_at)
)";

if ($db->query($sql) === TRUE) {
    echo "✅ Successfully created 'skintype_history' table!<br>";
    echo "Skin type change tracking is now enabled.<br>";
    echo "Users will now be able to see their skin type change history.";
} else {
    echo "❌ Error creating table: " . $db->error;
}

$db->close();
?>

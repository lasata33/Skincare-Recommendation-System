<?php
require_once 'config.php';

$email = 'admin11@gmail.com';
$password = 'admin123';
$username = 'Admin';
$role = 'admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$stmt = $conn->prepare("SELECT id FROM users_db WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing admin
    $stmt->close();
    $stmt = $conn->prepare("UPDATE users_db SET username=?, password=?, role=? WHERE email=?");
    $stmt->bind_param("ssss", $username, $hashedPassword, $role, $email);
    $stmt->execute();
    echo "✅ Admin updated successfully!";
} else {
    // Insert new admin
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO users_db (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    $stmt->execute();
    echo "✅ Admin created successfully!";
}
$stmt->close();
$conn->close();

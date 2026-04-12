<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/AdminUserManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$userManager = new AdminUserManager($db);

$id = intval($_GET['id']);
$user = $userManager->getUserById($id);

// 🚫 Prevent deleting admin accounts
if ($user['role'] === 'admin') {
    header("Location: manage_users.php?msg=Cannot delete admin accounts");
    exit();
}

// ✅ Delete user
$userManager->deleteUser($id);

header("Location: manage_users.php?msg=User deleted successfully");
exit();

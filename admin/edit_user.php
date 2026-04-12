<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/AdminUserManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$userManager = new AdminUserManager($db);

$id = intval($_GET['id']);
$user = $userManager->getUserById($id);
if ($user['role'] === 'admin') {
    header("Location: manage_users.php?msg=Cannot modify admin accounts");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $role      = $_POST['role'];
    $skintype  = $_POST['skintype'];

    $userManager->updateUser($id, $username, $email, $role, $skintype);
    header("Location: manage_users.php?msg=User updated successfully");
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f0f6ff;
            padding: 40px;
            color: #374151;
        }

        h1 {
            color: #3b82f6;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            background-color: #eaf4ff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(100, 149, 237, 0.1);
            display: grid;
            gap: 15px;
            max-width: 600px;
            margin: auto;
        }

        form label {
            font-weight: 500;
            color: #374151;
        }

        form input, form select {
            padding: 10px;
            border: 1.5px solid #cfe9ff;
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #fff;
            color: #374151;
        }

        form input:focus, form select:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(100, 149, 237, 0.1);
        }

        form button {
            background-color: #3b82f6;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            justify-self: start;
        }

        form button:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(100, 149, 237, 0.3);
        }
    </style>
</head>
<body>

<h1>Edit User</h1>

<form method="POST">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>Role</label>
    <select name="role" required>
        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <label>Skin Type</label>
    <input type="text" name="skintype" value="<?= htmlspecialchars($user['skintype']) ?>">

    <button type="submit">Update User</button>
</form>

</body>
</html>

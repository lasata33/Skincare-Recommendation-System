<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/AdminUserManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$userManager = new AdminUserManager($db);

// Handle block/unblock action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $userId = intval($_GET['id']);
    
    if ($action === 'block') {
        $userManager->blockUser($userId);
        $_GET['msg'] = "User blocked successfully!";
    } elseif ($action === 'unblock') {
        $userManager->unblockUser($userId);
        $_GET['msg'] = "User unblocked successfully!";
    }
    header("Location: manage_users.php?msg=" . urlencode($_GET['msg']));
    exit();
}

$users = $userManager->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<link rel="stylesheet" href="manage_users.css">
<link rel="stylesheet" href="admin_base.css">
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: 600;
    }
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    .status-blocked {
        background: #f8d7da;
        color: #721c24;
    }
    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        font-size: 0.9em;
    }
    .btn-block {
        background: #dc3545;
        color: white;
    }
    .btn-block:hover {
        background: #c82333;
    }
    .btn-unblock {
        background: #28a745;
        color: white;
    }
    .btn-unblock:hover {
        background: #218838;
    }
    
    /* Custom Confirmation Modal */
    .confirm-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }
    
    .confirm-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .confirm-content {
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 400px;
    }
    
    .confirm-content h2 {
        margin-top: 0;
        color: #2563eb;
        margin-bottom: 15px;
    }
    
    .confirm-content p {
        color: #666;
        margin-bottom: 25px;
        font-size: 1em;
    }
    
    .confirm-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .confirm-buttons button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.3s ease;
    }
    
    .btn-confirm {
        background: #dc3545;
        color: white;
    }
    
    .btn-confirm:hover {
        background: #c82333;
    }
    
    .btn-cancel {
        background: #6c757d;
        color: white;
    }
    
    .btn-cancel:hover {
        background: #5a6268;
    }
</style>
<script>
    let confirmAction = null;
    
    function showConfirmDialog(action, userId, username) {
        confirmAction = { action, userId, username };
        const modal = document.getElementById('confirmModal');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        
        if (action === 'block') {
            title.textContent = 'Block User?';
            message.textContent = `Are you sure you want to block ${username}? They won't be able to login to their account.`;
        } else {
            title.textContent = 'Unblock User?';
            message.textContent = `Are you sure you want to unblock ${username}? They will be able to login again.`;
        }
        
        modal.classList.add('show');
    }
    
    function confirmAction_func() {
        if (confirmAction) {
            window.location.href = `manage_users.php?action=${confirmAction.action}&id=${confirmAction.userId}`;
        }
        closeConfirmDialog();
    }
    
    function closeConfirmDialog() {
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('show');
        confirmAction = null;
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('confirmModal');
        if (event.target === modal) {
            closeConfirmDialog();
        }
    });
</script>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="admindashboard.php">Dashboard</a></li>
        <li><a href="manage_users.php" class="active">Manage Users</a></li>
        <li><a href="manage_quiz.php">View Quiz Results</a></li>
        <li><a href="add_products.php">Manage Products</a></li>
        <li><a href="../index.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <header>
        <h1>Manage Users</h1>
    </header>
<?php if (isset($_GET['msg'])): ?>
    <div class="success-msg">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

    <table>
  <thead>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Skin Type</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($users as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['role'] ?></td>
            <td><?= $row['skintype'] ?></td>
            <td>
                <span class="status-badge status-<?= $row['status'] === 'blocked' ? 'blocked' : 'active' ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </td>
            <td>
                <?php if ($row['role'] !== 'admin'): ?>
                    <?php if ($row['status'] === 'active'): ?>
                        <a href="#" class="action-btn btn-block" onclick="showConfirmDialog('block', <?= $row['id'] ?>, '<?= htmlspecialchars($row['username']) ?>'); return false;">Block</a>
                    <?php else: ?>
                        <a href="#" class="action-btn btn-unblock" onclick="showConfirmDialog('unblock', <?= $row['id'] ?>, '<?= htmlspecialchars($row['username']) ?>'); return false;">Unblock</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: #9ca3af;">Admin</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

    </table>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-content">
        <h2 id="confirmTitle">Confirm Action</h2>
        <p id="confirmMessage"></p>
        <div class="confirm-buttons">
            <button class="btn-confirm" onclick="confirmAction_func()">Yes, Confirm</button>
            <button class="btn-cancel" onclick="closeConfirmDialog()">Cancel</button>
        </div>
    </div>
</div>

</body>
</html>

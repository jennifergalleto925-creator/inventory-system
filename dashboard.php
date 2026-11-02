<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');

// Admin approves / rejects pending users
if ($isAdmin && isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $map = ['approve' => 'approved', 'reject' => 'rejected'];
    if (isset($map[$_GET['action']])) {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
        $stmt->execute([$map[$_GET['action']], $id]);
    }
    header("Location: dashboard.php");
    exit;
}

$users = $isAdmin
    ? $pdo->query("SELECT id, username, status, role, created_at FROM users ORDER BY created_at DESC")->fetchAll()
    : [];
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card wide">
        <div class="nav">
            <h1>Dashboard</h1>
            <a class="btn" href="logout.php">Logout</a>
        </div>
        <p class="sub">Welcome, <strong><?= e($_SESSION['username']) ?></strong> (<?= e($_SESSION['role']) ?>)</p>
        <p>Total products: <strong><?= (int) $productCount ?></strong></p>

        <?php if ($isAdmin): ?>
            <h3>Users</h3>
            <table>
                <tr><th>ID</th><th>Username</th><th>Role</th><th>Status</th><th>Registered</th><th>Action</th></tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td><?= e($u['status']) ?></td>
                    <td><?= e($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'user' && $u['status'] !== 'approved'): ?>
                            <a class="btn-sm green" href="?action=approve&id=<?= (int) $u['id'] ?>">Approve</a>
                        <?php endif; ?>
                        <?php if ($u['role'] === 'user' && $u['status'] !== 'rejected'): ?>
                            <a class="btn-sm red" href="?action=reject&id=<?= (int) $u['id'] ?>">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

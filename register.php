<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $error = "Username must be 3-50 characters (letters, numbers, underscore only).";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Username is already taken.";
        } else {
            // New accounts are 'pending' until an admin approves them
            $stmt = $pdo->prepare("INSERT INTO users (username, password, status, role) VALUES (?, ?, 'pending', 'user')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header("Location: index.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card">
        <h1>Create Account</h1>
        <p class="sub">Registration requires admin approval</p>

        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <div class="link">Already have an account? <a href="index.php">Log in</a></div>
    </div>
</body>
</html>

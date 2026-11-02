<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = isset($_GET['registered']) ? "Registration successful! Please wait for admin approval before logging in." : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid username or password.";
        } elseif ($user['status'] === 'pending') {
            $error = "Your account is still pending admin approval.";
        } elseif ($user['status'] === 'rejected') {
            $error = "Your registration was rejected by the admin.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
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
    <title>Log In - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card">
        <h1>Inventory System</h1>
        <p class="sub">Log in to your account</p>

        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
        </form>

        <div class="link">No account yet? <a href="register.php">Register here</a></div>
    </div>
</body>
</html>

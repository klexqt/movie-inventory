<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireGuest();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Login — MovieHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Movie<span>Hub</span></div>
        <p class="auth-subtitle">Your personal movie collection</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-primary btn-full">Login</button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
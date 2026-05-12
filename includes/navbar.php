<?php require_once __DIR__ . '/auth.php'; ?>
<nav class="navbar">
    <a href="dashboard.php" class="nav-logo">Movie<span>Hub</span></a>
    <div class="nav-right">
        <span class="nav-user">👤 <?= htmlspecialchars(currentUsername()) ?></span>
        <a href="dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>">Movies</a>
        <a href="logout.php" class="nav-btn-logout">Logout</a>
    </div>
</nav>
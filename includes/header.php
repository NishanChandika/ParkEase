<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo ucfirst($current_page); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAJV5vo1mKNQ1w0EITrjikceqyMk93CWCw"></script>
</head>
<body>
    <header>
    <nav>
    <div class="logo"><?php echo SITE_NAME; ?></div>
    <ul>
        <li><a href="index.php" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">Home</a></li>
        <?php if (isset($user) && is_array($user)): ?>
            <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <?php if (isset($user['role']) && $user['role'] == 'admin'): ?>
                <li><a href="admin.php" class="<?php echo ($current_page == 'admin') ? 'active' : ''; ?>">Admin</a></li>
            <?php endif; ?>
            <li><a href="logout.php" class="btn-account">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php" class="<?php echo ($current_page == 'login') ? 'active' : ''; ?>">Login</a></li>
            <li><a href="register.php" class="<?php echo ($current_page == 'register') ? 'active' : ''; ?>">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
    </header>
    <main>
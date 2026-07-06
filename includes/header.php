<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📘</text></svg>">
    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
            <span class="logo-icon">SH</span>
            <?php echo SITE_NAME; ?>
        </a>

        <nav class="nav">
            <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/jobs/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/jobs/') !== false ? 'active' : ''; ?>">Jobs</a>
            <a href="<?php echo SITE_URL; ?>/hall_tickets/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/hall_tickets/') !== false ? 'active' : ''; ?>">Hall Ticket</a>
            <a href="<?php echo SITE_URL; ?>/answers/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/answers/') !== false ? 'active' : ''; ?>">Answer Key</a>
            <a href="<?php echo SITE_URL; ?>/results/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/results/') !== false ? 'active' : ''; ?>">Result</a>
            <a href="<?php echo SITE_URL; ?>/exams/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/exams/') !== false ? 'active' : ''; ?>">Mock Test</a>
            <a href="<?php echo SITE_URL; ?>/notes/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/notes/') !== false ? 'active' : ''; ?>">Notes</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : ''; ?>" style="color:var(--warning);font-weight:700;">⚙ Admin Panel</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/dashboard/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/dashboard/') !== false ? 'active' : ''; ?>">Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" style="display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:var(--danger);color:white;font-size:1.1rem;padding:0;margin-left:4px;" title="Logout">⏻</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </nav>

        <button class="hamburger" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main>

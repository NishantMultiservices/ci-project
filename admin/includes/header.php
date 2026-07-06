<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
if (!isLoggedIn() || !isAdmin()) redirect('/index.php');

$current_page = basename($_SERVER['PHP_SELF']);
$admin_user = getUser($conn, $_SESSION['user_id']);
$page_title = isset($page_title) ? $page_title . ' - Admin' : 'Admin Panel';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <div class="logo-icon">SH</div>
            <div>
                <h3>StudyHub</h3>
                <small>Admin Panel</small>
            </div>
        </div>

        <div class="nav-section">Main</div>
        <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span> <span>Dashboard</span>
        </a>

        <div class="nav-section">Content</div>
        <a href="jobs.php" class="<?php echo $current_page == 'jobs.php' || $current_page == 'job_add.php' || $current_page == 'job_edit.php' ? 'active' : ''; ?>">
            <span class="icon">💼</span> <span>Job Listings</span>
        </a>
        <a href="hall_tickets.php" class="<?php echo $current_page == 'hall_tickets.php' || $current_page == 'hall_ticket_add.php' || $current_page == 'hall_ticket_edit.php' ? 'active' : ''; ?>">
            <span class="icon">🎫</span> <span>Hall Tickets</span>
        </a>
        <a href="answer_keys.php" class="<?php echo $current_page == 'answer_keys.php' || $current_page == 'answer_key_add.php' || $current_page == 'answer_key_edit.php' ? 'active' : ''; ?>">
            <span class="icon">🔑</span> <span>Answer Keys</span>
        </a>
        <a href="results.php" class="<?php echo $current_page == 'results.php' || $current_page == 'result_add.php' || $current_page == 'result_edit.php' ? 'active' : ''; ?>">
            <span class="icon">📢</span> <span>Results & Notifications</span>
        </a>
        <a href="exams.php" class="<?php echo $current_page == 'exams.php' || $current_page == 'exam_add.php' || $current_page == 'exam_edit.php' || $current_page == 'questions.php' ? 'active' : ''; ?>">
            <span class="icon">📝</span> <span>Mock Exams</span>
        </a>
        <a href="notes.php" class="<?php echo $current_page == 'notes.php' || $current_page == 'note_add.php' || $current_page == 'note_edit.php' ? 'active' : ''; ?>">
            <span class="icon">📖</span> <span>Study Notes</span>
        </a>

        <div class="nav-section">System</div>
        <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <span class="icon">👥</span> <span>Users</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/index.php">
            <span class="icon">🏠</span> <span>Back to Site</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/auth/logout.php">
            <span class="icon">🚪</span> <span>Logout</span>
        </a>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="admin-sidebar-toggle" onclick="toggleSidebar()">☰</button>
                <h2><?php echo $page_title ?? 'Dashboard'; ?></h2>
            </div>
            <div class="admin-topbar-right">
                <span class="admin-user">
                    <span class="admin-avatar"><?php echo strtoupper(substr($admin_user['full_name'] ?: $admin_user['username'], 0, 2)); ?></span>
                    <?php echo htmlspecialchars($admin_user['full_name'] ?: $admin_user['username']); ?>
                </span>
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-sm" style="background:var(--danger);color:white;">Logout</a>
            </div>
        </div>
        <div class="admin-content">

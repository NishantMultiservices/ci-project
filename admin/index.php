<?php
$page_title = 'Dashboard';
include 'includes/header.php';

$stats = [
    'users' => $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'],
    'exams' => $conn->query("SELECT COUNT(*) as c FROM exams")->fetch_assoc()['c'],
    'questions' => $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'],
    'results' => $conn->query("SELECT COUNT(*) as c FROM exam_results")->fetch_assoc()['c'],
    'hall_tickets' => $conn->query("SELECT COUNT(*) as c FROM hall_tickets")->fetch_assoc()['c'],
    'answer_keys' => $conn->query("SELECT COUNT(*) as c FROM answer_keys")->fetch_assoc()['c'],
    'notifications' => $conn->query("SELECT COUNT(*) as c FROM exam_notifications")->fetch_assoc()['c'],
    'jobs' => $conn->query("SELECT COUNT(*) as c FROM job_listings")->fetch_assoc()['c'],
    'notes' => $conn->query("SELECT COUNT(*) as c FROM study_notes")->fetch_assoc()['c'],
];

$recent_users = $conn->query("SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_exam_results = $conn->query("SELECT r.*, u.full_name, e.title FROM exam_results r JOIN users u ON r.user_id = u.id JOIN exams e ON r.exam_id = e.id ORDER BY r.completed_at DESC LIMIT 5");
?>

<div class="admin-stats-grid">
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#EEF1FF;color:var(--primary);">👥</div>
        <div class="admin-stat-info"><h4><?php echo $stats['users']; ?></h4><p>Users</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#EEF1FF;color:var(--primary);">📝</div>
        <div class="admin-stat-info"><h4><?php echo $stats['exams']; ?></h4><p>Exams</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#FEF3C7;color:var(--warning);">❓</div>
        <div class="admin-stat-info"><h4><?php echo $stats['questions']; ?></h4><p>Questions</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#D1FAE5;color:var(--success);">📊</div>
        <div class="admin-stat-info"><h4><?php echo $stats['results']; ?></h4><p>Exam Results</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#FEF3C7;color:var(--warning);">🎫</div>
        <div class="admin-stat-info"><h4><?php echo $stats['hall_tickets']; ?></h4><p>Hall Tickets</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#D1FAE5;color:var(--success);">🔑</div>
        <div class="admin-stat-info"><h4><?php echo $stats['answer_keys']; ?></h4><p>Answer Keys</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#DBEAFE;color:var(--info);">📢</div>
        <div class="admin-stat-info"><h4><?php echo $stats['notifications']; ?></h4><p>Notifications</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#FEF3C7;color:var(--warning);">💼</div>
        <div class="admin-stat-info"><h4><?php echo $stats['jobs']; ?></h4><p>Jobs</p></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon" style="background:#EEF1FF;color:var(--primary);">📖</div>
        <div class="admin-stat-info"><h4><?php echo $stats['notes']; ?></h4><p>Study Notes</p></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="admin-card">
        <div class="admin-card-header"><h3>Recent Users</h3><a href="users.php" class="btn btn-sm btn-primary">View All</a></div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                    <?php while ($u = $recent_users->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['full_name'] ?: $u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="admin-badge" style="background:<?php echo $u['role'] == 'admin' ? 'var(--warning-light)' : 'var(--gray-100)'; ?>;color:<?php echo $u['role'] == 'admin' ? 'var(--warning)' : 'var(--gray-700)'; ?>;"><?php echo $u['role']; ?></span></td>
                        <td style="color:var(--gray-500);font-size:0.85rem;"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h3>Recent Exam Results</h3></div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>User</th><th>Exam</th><th>Score</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while ($r = $recent_exam_results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['full_name'] ?: 'User#' . $r['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                        <td><strong><?php echo $r['percentage']; ?>%</strong></td>
                        <td><span class="admin-badge" style="background:<?php echo $r['status'] == 'passed' ? 'var(--success-light)' : 'var(--danger-light)'; ?>;color:<?php echo $r['status'] == 'passed' ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo ucfirst($r['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

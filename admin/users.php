<?php
$page_title = 'Manage Users';
include 'includes/header.php';

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$id");
    }
    echo '<script>window.location.href="users.php?msg=deleted";</script>'; exit;
}

$users = $conn->query("SELECT id, username, email, full_name, role, education, created_at FROM users ORDER BY created_at DESC");
$msg = $_GET['msg'] ?? '';
?>
<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">User <?php echo $msg; ?>.</div><?php endif; ?>
<div class="admin-card">
    <div class="admin-card-header"><h3>All Users (<?php echo $users->num_rows; ?>)</h3></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Education</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['full_name'] ?: '-'); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="admin-badge" style="background:<?php echo $u['role']=='admin'?'var(--warning-light)':'var(--gray-100)'; ?>;color:<?php echo $u['role']=='admin'?'var(--warning)':'var(--gray-700)'; ?>;"><?php echo $u['role']; ?></span></td>
                    <td><?php echo htmlspecialchars($u['education'] ?? '-'); ?></td>
                    <td style="font-size:0.85rem;"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                    <td class="actions">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?del=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete user <?php echo htmlspecialchars($u['username']); ?>?')">🗑</a>
                        <?php else: ?>
                            <span style="font-size:0.8rem;color:var(--gray-500);">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = 'Manage Hall Tickets';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $exam_name = $conn->real_escape_string($_POST['exam_name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $org = $conn->real_escape_string($_POST['organization']);
    $exam_date = $conn->real_escape_string($_POST['exam_date']);
    $url = $conn->real_escape_string($_POST['download_url']);
    $instructions = $conn->real_escape_string($_POST['instructions']);
    $featured = isset($_POST['is_featured']) ? 1 : 0;

    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO hall_tickets (title, exam_name, description, organization, exam_date, download_url, instructions, is_featured) VALUES ('$title', '$exam_name', '$desc', '$org', '$exam_date', '$url', '$instructions', $featured)");
        echo '<script>window.location.href="hall_tickets.php?msg=added";</script>'; exit;
    }
    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $active = isset($_POST['is_active']) ? 1 : 0;
        $conn->query("UPDATE hall_tickets SET title='$title', exam_name='$exam_name', description='$desc', organization='$org', exam_date='$exam_date', download_url='$url', instructions='$instructions', is_featured=$featured, is_active=$active WHERE id=$id");
        echo '<script>window.location.href="hall_tickets.php?msg=updated";</script>'; exit;
    }
}

if (isset($_GET['del'])) {
    $conn->query("DELETE FROM hall_tickets WHERE id=" . intval($_GET['del']));
    echo '<script>window.location.href="hall_tickets.php?msg=deleted";</script>'; exit;
}

$items = $conn->query("SELECT * FROM hall_tickets ORDER BY exam_date ASC");
$edit_item = $edit_id ? $conn->query("SELECT * FROM hall_tickets WHERE id=$edit_id")->fetch_assoc() : null;
$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">Hall ticket <?php echo $msg; ?>.</div><?php endif; ?>

<?php if ($action == 'add' || $edit_item): ?>
<a href="hall_tickets.php" class="back-link">← Back to Hall Tickets</a>
<div class="admin-card">
    <div class="admin-card-header"><h3><?php echo $edit_item ? 'Edit Hall Ticket' : 'Add Hall Ticket'; ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" class="admin-form">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Exam Name</label>
                    <input type="text" name="exam_name" class="form-input" required value="<?php echo htmlspecialchars($edit_item['exam_name'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Organization</label>
                <input type="text" name="organization" class="form-input" required value="<?php echo htmlspecialchars($edit_item['organization'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Exam Date</label>
                    <input type="date" name="exam_date" class="form-input" required value="<?php echo $edit_item['exam_date'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Download URL</label>
                    <input type="text" name="download_url" class="form-input" placeholder="https://..." value="<?php echo htmlspecialchars($edit_item['download_url'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Instructions</label>
                <textarea name="instructions" class="form-textarea"><?php echo htmlspecialchars($edit_item['instructions'] ?? ''); ?></textarea>
            </div>
            <div style="display:flex;gap:20px;margin-bottom:20px;">
                <label><input type="checkbox" name="is_featured" value="1" <?php echo ($edit_item['is_featured'] ?? 0) ? 'checked' : ''; ?>> Featured</label>
                <?php if ($edit_item): ?>
                <label><input type="checkbox" name="is_active" value="1" <?php echo $edit_item['is_active'] ? 'checked' : ''; ?>> Active</label>
                <?php endif; ?>
            </div>
            <button type="submit" name="<?php echo $edit_item ? 'edit' : 'add'; ?>" class="btn btn-primary"><?php echo $edit_item ? 'Update' : 'Add Hall Ticket'; ?></button>
        </form>
    </div>
</div>

<?php if ($edit_item):
    $hp = $conn->query("SELECT * FROM hall_ticket_posts WHERE hall_ticket_id = {$edit_item['id']} ORDER BY id");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
        $h_id = intval($_POST['ht_id']);
        $pname = $conn->real_escape_string($_POST['post_name']);
        $etime = $conn->real_escape_string($_POST['exam_time']);
        $durl = $conn->real_escape_string($_POST['download_url']);
        $conn->query("INSERT INTO hall_ticket_posts (hall_ticket_id, post_name, exam_time, download_url) VALUES ($h_id, '$pname', '$etime', '$durl')");
        echo '<script>window.location.href="hall_tickets.php?id='.$edit_item['id'].'&msg=post_added";</script>'; exit;
    }
    if (isset($_GET['del_post'])) {
        $conn->query("DELETE FROM hall_ticket_posts WHERE id=".intval($_GET['del_post']));
        echo '<script>window.location.href="hall_tickets.php?id='.$edit_item['id'].'&msg=post_deleted";</script>'; exit;
    }
    $hp = $conn->query("SELECT * FROM hall_ticket_posts WHERE hall_ticket_id = {$edit_item['id']} ORDER BY id");
?>
<div class="admin-card" style="margin-top:20px;">
    <div class="admin-card-header"><h3>Exam Posts / Papers</h3></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Post Name</th><th>Time</th><th>Download URL</th><th>Actions</th></tr></thead>
            <tbody><?php if ($hp->num_rows==0): ?><tr><td colspan="4" style="text-align:center;color:#94a3b8;">No posts added yet.</td></tr><?php endif; while($p=$hp->fetch_assoc()): ?>
                <tr><td><?php echo htmlspecialchars($p['post_name']); ?></td><td><?php echo htmlspecialchars($p['exam_time'] ?: '-'); ?></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['download_url'] ?: '-'); ?></td>
                <td class="actions"><a href="hall_tickets.php?id=<?php echo $edit_item['id']; ?>&del_post=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete post?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
    <div class="admin-card-body" style="border-top:1px solid #eef2f6;">
        <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="ht_id" value="<?php echo $edit_item['id']; ?>">
            <div><label class="form-label" style="font-size:.78rem;">Post Name</label><input type="text" name="post_name" class="form-input" required style="min-width:180px;"></div>
            <div><label class="form-label" style="font-size:.78rem;">Time</label><input type="text" name="exam_time" class="form-input" placeholder="e.g. 10:00 AM" style="min-width:130px;"></div>
            <div><label class="form-label" style="font-size:.78rem;">Download URL</label><input type="text" name="download_url" class="form-input" placeholder="https://..." style="min-width:200px;"></div>
            <button type="submit" name="add_post" class="btn btn-primary btn-sm">+ Add</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php else: ?>

<div class="admin-card">
    <div class="admin-card-header"><h3>All Hall Tickets</h3><a href="hall_tickets.php?action=add" class="btn btn-primary btn-sm">+ Add New</a></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Exam</th><th>Organization</th><th>Date</th><th>Featured</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while ($i = $items->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($i['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($i['exam_name']); ?></td>
                    <td><?php echo htmlspecialchars($i['organization']); ?></td>
                    <td><?php echo date('d M Y', strtotime($i['exam_date'])); ?></td>
                    <td><?php echo $i['is_featured'] ? '⭐' : '-'; ?></td>
                    <td><span class="admin-badge" style="background:<?php echo $i['is_active']?'var(--success-light)':'var(--danger-light)'; ?>;color:<?php echo $i['is_active']?'var(--success)':'var(--danger)'; ?>;"><?php echo $i['is_active']?'Active':'Inactive'; ?></span></td>
                    <td class="actions">
                        <a href="hall_tickets.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a>
                        <a href="hall_tickets.php?del=<?php echo $i['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<?php
$page_title = 'Manage Results & Notifications';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $exam_name = $conn->real_escape_string($_POST['exam_name']);
    $org = $conn->real_escape_string($_POST['organization']);
    $type = $conn->real_escape_string($_POST['notification_type']);
    $url = $conn->real_escape_string($_POST['download_url']);
    $rdate = $_POST['result_date'] ? $conn->real_escape_string($_POST['result_date']) : 'NULL';
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO exam_notifications (title, description, exam_name, organization, notification_type, download_url, result_date, is_featured) VALUES ('$title','$desc','$exam_name','$org','$type','$url', $rdate, $featured)");
        echo '<script>window.location.href="results.php?msg=added";</script>'; exit;
    }
    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']); $active = isset($_POST['is_active'])?1:0;
        $conn->query("UPDATE exam_notifications SET title='$title', description='$desc', exam_name='$exam_name', organization='$org', notification_type='$type', download_url='$url', result_date=$rdate, is_featured=$featured, is_active=$active WHERE id=$id");
        echo '<script>window.location.href="results.php?msg=updated";</script>'; exit;
    }
}
if (isset($_GET['del'])) { $conn->query("DELETE FROM exam_notifications WHERE id=".intval($_GET['del'])); echo '<script>window.location.href="results.php?msg=deleted";</script>'; exit; }

$items = $conn->query("SELECT * FROM exam_notifications ORDER BY created_at DESC");
$edit_item = $edit_id ? $conn->query("SELECT * FROM exam_notifications WHERE id=$edit_id")->fetch_assoc() : null;
$msg = $_GET['msg'] ?? '';
$types = ['result'=>'📊 Result','admit_card'=>'🎫 Admit Card','exam_date'=>'📅 Exam Date','syllabus'=>'📚 Syllabus','other'=>'📢 Other'];
?>
<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">Notification <?php echo $msg; ?>.</div><?php endif; ?>
<?php if ($action == 'add' || $edit_item): ?>
<a href="results.php" class="back-link">← Back to Notifications</a>
<div class="admin-card">
    <div class="admin-card-header"><h3><?php echo $edit_item ? 'Edit Notification' : 'Add Notification'; ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" class="admin-form">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
            <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Exam Name</label><input type="text" name="exam_name" class="form-input" value="<?php echo htmlspecialchars($edit_item['exam_name'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Organization</label><input type="text" name="organization" class="form-input" value="<?php echo htmlspecialchars($edit_item['organization'] ?? ''); ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Type</label><select name="notification_type" class="form-select"><?php foreach($types as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo ($edit_item['notification_type']??'')==$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label">Result Date</label><input type="date" name="result_date" class="form-input" value="<?php echo $edit_item['result_date'] ?? ''; ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" required><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea></div>
            <div class="form-group"><label class="form-label">Download URL (optional)</label><input type="text" name="download_url" class="form-input" value="<?php echo htmlspecialchars($edit_item['download_url'] ?? ''); ?>"></div>
            <div style="display:flex;gap:20px;margin-bottom:20px;">
                <label><input type="checkbox" name="is_featured" value="1" <?php echo ($edit_item['is_featured']??0)?'checked':''; ?>> Featured</label>
                <?php if ($edit_item): ?><label><input type="checkbox" name="is_active" value="1" <?php echo $edit_item['is_active']?'checked':''; ?>> Active</label><?php endif; ?>
            </div>
            <button type="submit" name="<?php echo $edit_item?'edit':'add'; ?>" class="btn btn-primary"><?php echo $edit_item?'Update':'Add Notification'; ?></button>
        </form>
    </div>
</div>

<?php if ($edit_item):
    $np = $conn->query("SELECT * FROM notification_posts WHERE notification_id = {$edit_item['id']} ORDER BY id");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
        $n_id = intval($_POST['notif_id']);
        $pname = $conn->real_escape_string($_POST['post_name']);
        $durl = $conn->real_escape_string($_POST['download_url']);
        $rdate = $_POST['result_date'] ? $conn->real_escape_string($_POST['result_date']) : 'NULL';
        $conn->query("INSERT INTO notification_posts (notification_id, post_name, download_url, result_date) VALUES ($n_id, '$pname', '$durl', $rdate)");
        echo '<script>window.location.href="results.php?id='.$edit_item['id'].'&msg=post_added";</script>'; exit;
    }
    if (isset($_GET['del_post'])) {
        $conn->query("DELETE FROM notification_posts WHERE id=".intval($_GET['del_post']));
        echo '<script>window.location.href="results.php?id='.$edit_item['id'].'&msg=post_deleted";</script>'; exit;
    }
    $np = $conn->query("SELECT * FROM notification_posts WHERE notification_id = {$edit_item['id']} ORDER BY id");
?>
<div class="admin-card" style="margin-top:20px;">
    <div class="admin-card-header"><h3>Posts / Categories</h3></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Post Name</th><th>Result Date</th><th>Download URL</th><th>Actions</th></tr></thead>
            <tbody><?php if ($np->num_rows==0): ?><tr><td colspan="4" style="text-align:center;color:#94a3b8;">No posts added yet.</td></tr><?php endif; while($p=$np->fetch_assoc()): ?>
                <tr><td><?php echo htmlspecialchars($p['post_name']); ?></td><td><?php echo $p['result_date'] ? date('d M Y', strtotime($p['result_date'])) : '-'; ?></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['download_url'] ?: '-'); ?></td>
                <td class="actions"><a href="results.php?id=<?php echo $edit_item['id']; ?>&del_post=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
    <div class="admin-card-body" style="border-top:1px solid #eef2f6;">
        <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="notif_id" value="<?php echo $edit_item['id']; ?>">
            <div><label class="form-label" style="font-size:.78rem;">Post Name</label><input type="text" name="post_name" class="form-input" required style="min-width:180px;"></div>
            <div><label class="form-label" style="font-size:.78rem;">Result Date</label><input type="date" name="result_date" class="form-input" style="min-width:140px;"></div>
            <div><label class="form-label" style="font-size:.78rem;">Download URL</label><input type="text" name="download_url" class="form-input" placeholder="https://..." style="min-width:200px;"></div>
            <button type="submit" name="add_post" class="btn btn-primary btn-sm">+ Add</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="admin-card">
    <div class="admin-card-header"><h3>All Notifications</h3><a href="results.php?action=add" class="btn btn-primary btn-sm">+ Add New</a></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Exam</th><th>Type</th><th>Organization</th><th>Date</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody><?php while ($i = $items->fetch_assoc()): ?>
                <tr><td><strong><?php echo htmlspecialchars($i['title']); ?></strong></td><td><?php echo htmlspecialchars($i['exam_name'] ?? '-'); ?></td>
                <td><span class="admin-badge" style="background:var(--info-light);color:var(--info);"><?php echo str_replace('_',' ',ucfirst($i['notification_type'])); ?></span></td>
                <td><?php echo htmlspecialchars($i['organization'] ?? '-'); ?></td>
                <td style="font-size:0.85rem;"><?php echo $i['result_date'] ? date('d M Y', strtotime($i['result_date'])) : '-'; ?></td>
                <td><span class="admin-badge" style="background:<?php echo $i['is_active']?'var(--success-light)':'var(--danger-light)'; ?>;color:<?php echo $i['is_active']?'var(--success)':'var(--danger)'; ?>;"><?php echo $i['is_active']?'Active':'Inactive'; ?></span></td>
                <td class="actions"><a href="results.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a><a href="results.php?del=<?php echo $i['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = 'Manage Answer Keys';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $exam_name = $conn->real_escape_string($_POST['exam_name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $org = $conn->real_escape_string($_POST['organization']);
    $url = $conn->real_escape_string($_POST['download_url']);
    $file_type = $conn->real_escape_string($_POST['file_type']);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO answer_keys (title, exam_name, description, subject, organization, download_url, file_type, is_featured) VALUES ('$title','$exam_name','$desc','$subject','$org','$url','$file_type',$featured)");
        echo '<script>window.location.href="answer_keys.php?msg=added";</script>'; exit;
    }
    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']); $active = isset($_POST['is_active'])?1:0;
        $conn->query("UPDATE answer_keys SET title='$title', exam_name='$exam_name', description='$desc', subject='$subject', organization='$org', download_url='$url', file_type='$file_type', is_featured=$featured, is_active=$active WHERE id=$id");
        echo '<script>window.location.href="answer_keys.php?msg=updated";</script>'; exit;
    }
}
if (isset($_GET['del'])) { $conn->query("DELETE FROM answer_keys WHERE id=".intval($_GET['del'])); echo '<script>window.location.href="answer_keys.php?msg=deleted";</script>'; exit; }

$items = $conn->query("SELECT * FROM answer_keys ORDER BY created_at DESC");
$edit_item = $edit_id ? $conn->query("SELECT * FROM answer_keys WHERE id=$edit_id")->fetch_assoc() : null;
$msg = $_GET['msg'] ?? '';
?>
<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">Answer key <?php echo $msg; ?>.</div><?php endif; ?>
<?php if ($action == 'add' || $edit_item): ?>
<a href="answer_keys.php" class="back-link">← Back to Answer Keys</a>
<div class="admin-card">
    <div class="admin-card-header"><h3><?php echo $edit_item ? 'Edit Answer Key' : 'Add Answer Key'; ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" class="admin-form">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Exam Name</label><input type="text" name="exam_name" class="form-input" required value="<?php echo htmlspecialchars($edit_item['exam_name'] ?? ''); ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Organization</label><input type="text" name="organization" class="form-input" required value="<?php echo htmlspecialchars($edit_item['organization'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-input" value="<?php echo htmlspecialchars($edit_item['subject'] ?? ''); ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Download URL</label><input type="text" name="download_url" class="form-input" value="<?php echo htmlspecialchars($edit_item['download_url'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">File Type</label><select name="file_type" class="form-select"><?php foreach(['pdf','jpg','png','doc'] as $f): ?><option value="<?php echo $f; ?>" <?php echo ($edit_item['file_type']??'pdf')==$f?'selected':''; ?>><?php echo strtoupper($f); ?></option><?php endforeach; ?></select></div>
            </div>
            <div style="display:flex;gap:20px;margin-bottom:20px;">
                <label><input type="checkbox" name="is_featured" value="1" <?php echo ($edit_item['is_featured']??0)?'checked':''; ?>> Featured</label>
                <?php if ($edit_item): ?><label><input type="checkbox" name="is_active" value="1" <?php echo $edit_item['is_active']?'checked':''; ?>> Active</label><?php endif; ?>
            </div>
            <button type="submit" name="<?php echo $edit_item?'edit':'add'; ?>" class="btn btn-primary"><?php echo $edit_item?'Update':'Add Answer Key'; ?></button>
        </form>
    </div>
</div>

<?php if ($edit_item):
    $ap = $conn->query("SELECT * FROM answer_key_posts WHERE answer_key_id = {$edit_item['id']} ORDER BY id");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
        $a_id = intval($_POST['ak_id']);
        $subj = $conn->real_escape_string($_POST['subject']);
        $durl = $conn->real_escape_string($_POST['download_url']);
        $ft = $conn->real_escape_string($_POST['file_type']);
        $conn->query("INSERT INTO answer_key_posts (answer_key_id, subject, download_url, file_type) VALUES ($a_id, '$subj', '$durl', '$ft')");
        echo '<script>window.location.href="answer_keys.php?id='.$edit_item['id'].'&msg=post_added";</script>'; exit;
    }
    if (isset($_GET['del_post'])) {
        $conn->query("DELETE FROM answer_key_posts WHERE id=".intval($_GET['del_post']));
        echo '<script>window.location.href="answer_keys.php?id='.$edit_item['id'].'&msg=post_deleted";</script>'; exit;
    }
    $ap = $conn->query("SELECT * FROM answer_key_posts WHERE answer_key_id = {$edit_item['id']} ORDER BY id");
?>
<div class="admin-card" style="margin-top:20px;">
    <div class="admin-card-header"><h3>Subject-wise Answer Keys</h3></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Subject</th><th>File Type</th><th>Download URL</th><th>Actions</th></tr></thead>
            <tbody><?php if ($ap->num_rows==0): ?><tr><td colspan="4" style="text-align:center;color:#94a3b8;">No subjects added yet.</td></tr><?php endif; while($p=$ap->fetch_assoc()): ?>
                <tr><td><?php echo htmlspecialchars($p['subject']); ?></td><td><?php echo strtoupper($p['file_type']); ?></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['download_url'] ?: '-'); ?></td>
                <td class="actions"><a href="answer_keys.php?id=<?php echo $edit_item['id']; ?>&del_post=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
    <div class="admin-card-body" style="border-top:1px solid #eef2f6;">
        <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="ak_id" value="<?php echo $edit_item['id']; ?>">
            <div><label class="form-label" style="font-size:.78rem;">Subject</label><input type="text" name="subject" class="form-input" required style="min-width:180px;"></div>
            <div><label class="form-label" style="font-size:.78rem;">File Type</label><select name="file_type" class="form-select"><?php foreach(['pdf','jpg','png','doc'] as $f): ?><option value="<?php echo $f; ?>"><?php echo strtoupper($f); ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label" style="font-size:.78rem;">Download URL</label><input type="text" name="download_url" class="form-input" placeholder="https://..." style="min-width:200px;"></div>
            <button type="submit" name="add_post" class="btn btn-primary btn-sm">+ Add</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="admin-card">
    <div class="admin-card-header"><h3>All Answer Keys</h3><a href="answer_keys.php?action=add" class="btn btn-primary btn-sm">+ Add New</a></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Exam</th><th>Subject</th><th>Organization</th><th>Type</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody><?php while ($i = $items->fetch_assoc()): ?>
                <tr><td><strong><?php echo htmlspecialchars($i['title']); ?></strong></td><td><?php echo htmlspecialchars($i['exam_name']); ?></td><td><?php echo htmlspecialchars($i['subject'] ?? '-'); ?></td><td><?php echo htmlspecialchars($i['organization']); ?></td><td><?php echo strtoupper($i['file_type']); ?></td>
                <td><span class="admin-badge" style="background:<?php echo $i['is_active']?'var(--success-light)':'var(--danger-light)'; ?>;color:<?php echo $i['is_active']?'var(--success)':'var(--danger)'; ?>;"><?php echo $i['is_active']?'Active':'Inactive'; ?></span></td>
                <td class="actions"><a href="answer_keys.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a><a href="answer_keys.php?del=<?php echo $i['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>

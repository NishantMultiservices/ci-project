<?php
$page_title = 'Manage Study Notes';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $cat = intval($_POST['category_id']);
    $file_path = $conn->real_escape_string($_POST['file_path']);
    $file_type = $conn->real_escape_string($_POST['file_type']);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $public = isset($_POST['is_public']) ? 1 : 0;
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $price = $is_free ? 0 : floatval($_POST['price']);
    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO study_notes (title, description, category_id, file_path, file_type, is_featured, is_public, is_free, price) VALUES ('$title','$desc',$cat,'$file_path','$file_type',$featured,$public,$is_free,$price)");
        echo '<script>window.location.href="notes.php?msg=added";</script>'; exit;
    }
    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $conn->query("UPDATE study_notes SET title='$title', description='$desc', category_id=$cat, file_path='$file_path', file_type='$file_type', is_featured=$featured, is_public=$public, is_free=$is_free, price=$price WHERE id=$id");
        echo '<script>window.location.href="notes.php?msg=updated";</script>'; exit;
    }
}
if (isset($_GET['del'])) { $conn->query("DELETE FROM study_notes WHERE id=".intval($_GET['del'])); echo '<script>window.location.href="notes.php?msg=deleted";</script>'; exit; }

$cats = $conn->query("SELECT id, name FROM categories WHERE type='note' ORDER BY name");
$items = $conn->query("SELECT n.*, c.name as cat_name FROM study_notes n LEFT JOIN categories c ON n.category_id=c.id ORDER BY n.created_at DESC");
$edit_item = $edit_id ? $conn->query("SELECT * FROM study_notes WHERE id=$edit_id")->fetch_assoc() : null;
$msg = $_GET['msg'] ?? '';
?>
<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">Note <?php echo $msg; ?>.</div><?php endif; ?>
<?php if ($action == 'add' || $edit_item): ?>
<a href="notes.php" class="back-link">← Back to Notes</a>
<div class="admin-card">
    <div class="admin-card-header"><h3><?php echo $edit_item ? 'Edit Note' : 'Add Note'; ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" class="admin-form">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
            <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Category</label><select name="category_id" class="form-select"><?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?><option value="<?php echo $c['id']; ?>" <?php echo ($edit_item['category_id']??0)==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option><?php endwhile; ?></select></div>
                <div class="form-group"><label class="form-label">File Type</label><select name="file_type" class="form-select"><?php foreach(['pdf','doc','txt','ppt'] as $f): ?><option value="<?php echo $f; ?>" <?php echo ($edit_item['file_type']??'pdf')==$f?'selected':''; ?>><?php echo strtoupper($f); ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea></div>
            <div class="form-group"><label class="form-label">File Path / URL</label><input type="text" name="file_path" class="form-input" value="<?php echo htmlspecialchars($edit_item['file_path'] ?? ''); ?>"></div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pricing</label>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="is_free" value="1" id="noteIsFree" <?php echo ($edit_item['is_free'] ?? 1) ? 'checked' : ''; ?> onchange="document.getElementById('notePriceRow').style.display=this.checked?'none':'flex';"> Free</label>
                    </div>
                </div>
                <div class="form-group" id="notePriceRow" style="display:<?php echo (isset($edit_item) && !$edit_item['is_free']) ? 'flex' : 'none'; ?>;">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" name="price" class="form-input" step="0.01" min="0" value="<?php echo $edit_item['price'] ?? 0; ?>">
                </div>
            </div>
            <div style="display:flex;gap:20px;margin-bottom:20px;">
                <label><input type="checkbox" name="is_featured" value="1" <?php echo ($edit_item['is_featured']??0)?'checked':''; ?>> Featured</label>
                <label><input type="checkbox" name="is_public" value="1" <?php echo ($edit_item['is_public']??1)?'checked':''; ?>> Public</label>
            </div>
            <button type="submit" name="<?php echo $edit_item?'edit':'add'; ?>" class="btn btn-primary"><?php echo $edit_item?'Update':'Add Note'; ?></button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="admin-card">
    <div class="admin-card-header"><h3>All Study Notes</h3><a href="notes.php?action=add" class="btn btn-primary btn-sm">+ Add New</a></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Category</th><th>Type</th><th>Downloads</th><th>Price</th><th>Featured</th><th>Public</th><th>Actions</th></tr></thead>
            <tbody><?php while ($i = $items->fetch_assoc()): ?>
                <tr><td><strong><?php echo htmlspecialchars($i['title']); ?></strong></td><td><?php echo htmlspecialchars($i['cat_name'] ?? '-'); ?></td><td><?php echo strtoupper($i['file_type']); ?></td><td><?php echo $i['download_count']; ?></td>
                <td><?php echo $i['is_free'] ? '<span class="admin-badge" style="background:var(--success-light);color:var(--success);">Free</span>' : '₹'.number_format($i['price'],0); ?></td>
                <td><?php echo $i['is_featured']?'⭐':'-'; ?></td><td><?php echo $i['is_public']?'<span class="admin-badge" style="background:var(--success-light);color:var(--success);">Public</span>':'<span class="admin-badge" style="background:var(--danger-light);color:var(--danger);">Private</span>'; ?></td>
                <td class="actions"><a href="notes.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a><a href="notes.php?del=<?php echo $i['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>

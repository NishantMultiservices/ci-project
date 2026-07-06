<?php
$page_title = 'Manage Job Listings';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $company = $conn->real_escape_string($_POST['company']);
    $advt = $conn->real_escape_string($_POST['advertise_no']);
    $total_vacancy = intval($_POST['total_vacancy']);
    $exam_name = $conn->real_escape_string($_POST['exam_name']);
    $app_type = $conn->real_escape_string($_POST['application_type']);
    $desc = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['job_location']);
    $salary = $conn->real_escape_string($_POST['salary_range']);
    $deadline = $conn->real_escape_string($_POST['application_deadline']);
    $advt_url = $conn->real_escape_string($_POST['advertisement_url']);
    $website = $conn->real_escape_string($_POST['official_website']);
    $app_link = $conn->real_escape_string($_POST['application_link']);
    $other_link = $conn->real_escape_string($_POST['other_link']);
    $other_text = $conn->real_escape_string($_POST['other_link_text']);

    $dates = [];
    foreach (['application_start','application_end','exam_date_prelims','exam_date_mains','fee_payment_last','result_date','interview'] as $dk) {
        if (!empty($_POST[$dk])) $dates[$dk] = $_POST[$dk];
    }
    $important_dates = $conn->real_escape_string(json_encode($dates));

    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO job_listings (title, company, advertise_no, total_vacancy, exam_name, application_type, description, job_location, salary_range, important_dates, advertisement_url, official_website, application_link, other_link, other_link_text, application_deadline, is_featured) VALUES ('$title','$company','$advt',$total_vacancy,'$exam_name','$app_type','$desc','$location','$salary','$important_dates','$advt_url','$website','$app_link','$other_link','$other_text','$deadline',0)");
        $jid = $conn->insert_id;
        // posts
        if (isset($_POST['post_name'])) foreach ($_POST['post_name'] as $i => $pn) {
            if (!empty(trim($pn))) { $pno = intval($_POST['post_no'][$i]); $vac = intval($_POST['vacancy'][$i]); $conn->query("INSERT INTO job_posts (job_id, post_no, post_name, vacancy) VALUES ($jid, $pno, '$pn', $vac)"); }
        }
        // qualifications
        if (isset($_POST['q_post_no'])) foreach ($_POST['q_post_no'] as $i => $qpn) {
            if (!empty(trim($_POST['education'][$i]))) { $edu = $conn->real_escape_string($_POST['education'][$i]); $conn->query("INSERT INTO job_qualifications (job_id, post_no, education) VALUES ($jid, $qpn, '$edu')"); }
        }
        // fees
        if (isset($_POST['f_post_no'])) foreach ($_POST['f_post_no'] as $i => $fpn) {
            if (!empty(trim($_POST['fee_category'][$i]))) { $cat = $conn->real_escape_string($_POST['fee_category'][$i]); $fee = $conn->real_escape_string($_POST['fee_amount'][$i]); $conn->query("INSERT INTO job_fees (job_id, post_no, category, fee) VALUES ($jid, $fpn, '$cat', '$fee')"); }
        }
        echo '<script>window.location.href="jobs.php?msg=added";</script>'; exit;
    }

    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']); $active = isset($_POST['is_active'])?1:0; $featured = isset($_POST['is_featured'])?1:0;
        $conn->query("UPDATE job_listings SET title='$title', company='$company', advertise_no='$advt', total_vacancy=$total_vacancy, exam_name='$exam_name', application_type='$app_type', description='$desc', job_location='$location', salary_range='$salary', important_dates='$important_dates', advertisement_url='$advt_url', official_website='$website', application_link='$app_link', other_link='$other_link', other_link_text='$other_text', application_deadline='$deadline', is_featured=$featured, is_active=$active WHERE id=$id");
        // clear and re-insert child tables
        $conn->query("DELETE FROM job_posts WHERE job_id=$id");
        $conn->query("DELETE FROM job_qualifications WHERE job_id=$id");
        $conn->query("DELETE FROM job_fees WHERE job_id=$id");
        if (isset($_POST['post_name'])) foreach ($_POST['post_name'] as $i => $pn) {
            if (!empty(trim($pn))) { $pno = intval($_POST['post_no'][$i]); $vac = intval($_POST['vacancy'][$i]); $conn->query("INSERT INTO job_posts (job_id, post_no, post_name, vacancy) VALUES ($id, $pno, '$pn', $vac)"); }
        }
        if (isset($_POST['q_post_no'])) foreach ($_POST['q_post_no'] as $i => $qpn) {
            if (!empty(trim($_POST['education'][$i]))) { $edu = $conn->real_escape_string($_POST['education'][$i]); $conn->query("INSERT INTO job_qualifications (job_id, post_no, education) VALUES ($id, $qpn, '$edu')"); }
        }
        if (isset($_POST['f_post_no'])) foreach ($_POST['f_post_no'] as $i => $fpn) {
            if (!empty(trim($_POST['fee_category'][$i]))) { $cat = $conn->real_escape_string($_POST['fee_category'][$i]); $fee = $conn->real_escape_string($_POST['fee_amount'][$i]); $conn->query("INSERT INTO job_fees (job_id, post_no, category, fee) VALUES ($id, $fpn, '$cat', '$fee')"); }
        }
        echo '<script>window.location.href="jobs.php?msg=updated";</script>'; exit;
    }
}

if (isset($_GET['del'])) { $conn->query("DELETE FROM job_listings WHERE id=".intval($_GET['del'])); echo '<script>window.location.href="jobs.php?msg=deleted";</script>'; exit; }

$items = $conn->query("SELECT * FROM job_listings ORDER BY created_at DESC");
$edit_item = $edit_id ? $conn->query("SELECT * FROM job_listings WHERE id=$edit_id")->fetch_assoc() : null;
$edit_posts = $edit_id ? $conn->query("SELECT * FROM job_posts WHERE job_id=$edit_id ORDER BY post_no") : null;
$edit_quals = $edit_id ? $conn->query("SELECT * FROM job_qualifications WHERE job_id=$edit_id ORDER BY post_no") : null;
$edit_fees = $edit_id ? $conn->query("SELECT * FROM job_fees WHERE job_id=$edit_id ORDER BY id") : null;
$msg = $_GET['msg'] ?? '';

function datesFromJson($json) {
    $d = json_decode($json, true);
    return is_array($d) ? $d : [];
}
$dates_val = $edit_item ? datesFromJson($edit_item['important_dates']) : [];
?>
<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:16px;">Job <?php echo $msg; ?>.</div><?php endif; ?>

<?php if ($action == 'add' || $edit_item): ?>
<a href="jobs.php" class="back-link">← Back to Jobs</a>

<div class="admin-card">
    <div class="admin-card-header"><h3><?php echo $edit_item ? 'Edit Job' : 'Add Job'; ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" class="admin-form" id="jobForm">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>

            <h4 style="margin-bottom:16px;color:var(--primary);">Basic Details</h4>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Recruitment / Job Title *</label><input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Organization *</label><input type="text" name="company" class="form-input" required value="<?php echo htmlspecialchars($edit_item['company'] ?? ''); ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Advertisement No.</label><input type="text" name="advertise_no" class="form-input" value="<?php echo htmlspecialchars($edit_item['advertise_no'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Total Vacancy</label><input type="number" name="total_vacancy" class="form-input" value="<?php echo $edit_item['total_vacancy'] ?? 0; ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Examination Name</label><input type="text" name="exam_name" class="form-input" value="<?php echo htmlspecialchars($edit_item['exam_name'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Application Type</label><select name="application_type" class="form-select">
                    <option value="online" <?php echo ($edit_item['application_type']??'')=='online'?'selected':''; ?>>Online</option>
                    <option value="offline" <?php echo ($edit_item['application_type']??'')=='offline'?'selected':''; ?>>Offline</option>
                    <option value="both" <?php echo ($edit_item['application_type']??'')=='both'?'selected':''; ?>>Both</option>
                </select></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="4"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Job Location (Optional)</label><input type="text" name="job_location" class="form-input" value="<?php echo htmlspecialchars($edit_item['job_location'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Salary (Optional)</label><input type="text" name="salary_range" class="form-input" value="<?php echo htmlspecialchars($edit_item['salary_range'] ?? ''); ?>"></div>
            </div>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <h4 style="margin-bottom:16px;color:var(--primary);">Post Details (Table 1: Post No., Post Name, Vacancy)</h4>
            <div id="postsContainer">
                <?php if ($edit_posts && $edit_posts->num_rows > 0): $pi=0; while($p=$edit_posts->fetch_assoc()): ?>
                <div class="form-row post-row" style="margin-bottom:8px;">
                    <div class="form-group"><input type="number" name="post_no[]" class="form-input" placeholder="Post No." value="<?php echo $p['post_no']; ?>" style="width:80px;"></div>
                    <div class="form-group"><input type="text" name="post_name[]" class="form-input" placeholder="Post Name" value="<?php echo htmlspecialchars($p['post_name']); ?>"></div>
                    <div class="form-group"><input type="number" name="vacancy[]" class="form-input" placeholder="Vacancy" value="<?php echo $p['vacancy']; ?>" style="width:100px;"></div>
                    <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.post-row').remove()" style="padding:10px 12px;">✕</button></div>
                </div>
                <?php $pi++; endwhile; endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addPostRow()" style="margin-bottom:16px;">+ Add Post</button>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <h4 style="margin-bottom:16px;color:var(--primary);">Education / Qualification (Table 2: Post No., Education)</h4>
            <div id="qualsContainer">
                <?php if ($edit_quals && $edit_quals->num_rows > 0): $qi=0; while($q=$edit_quals->fetch_assoc()): ?>
                <div class="form-row qual-row" style="margin-bottom:8px;">
                    <div class="form-group"><input type="number" name="q_post_no[]" class="form-input" placeholder="Post No." value="<?php echo $q['post_no']; ?>" style="width:80px;"></div>
                    <div class="form-group"><input type="text" name="education[]" class="form-input" placeholder="Education qualification required" value="<?php echo htmlspecialchars($q['education']); ?>"></div>
                    <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.qual-row').remove()" style="padding:10px 12px;">✕</button></div>
                </div>
                <?php $qi++; endwhile; endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addQualRow()" style="margin-bottom:16px;">+ Add Qualification</button>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <h4 style="margin-bottom:16px;color:var(--primary);">Application Fee (Table 3: Post No., Category, Fee)</h4>
            <p style="font-size:0.85rem;color:var(--gray-500);margin-bottom:12px;">Use Post No. 0 for fees that apply to all posts.</p>
            <div id="feesContainer">
                <?php if ($edit_fees && $edit_fees->num_rows > 0): $fi=0; while($f=$edit_fees->fetch_assoc()): ?>
                <div class="form-row fee-row" style="margin-bottom:8px;">
                    <div class="form-group"><input type="number" name="f_post_no[]" class="form-input" placeholder="Post No." value="<?php echo $f['post_no']; ?>" style="width:80px;"></div>
                    <div class="form-group"><input type="text" name="fee_category[]" class="form-input" placeholder="Category (e.g. General, SC/ST)" value="<?php echo htmlspecialchars($f['category']); ?>"></div>
                    <div class="form-group"><input type="text" name="fee_amount[]" class="form-input" placeholder="Fee (e.g. ₹100, Exempted)" value="<?php echo htmlspecialchars($f['fee']); ?>"></div>
                    <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.fee-row').remove()" style="padding:10px 12px;">✕</button></div>
                </div>
                <?php $fi++; endwhile; endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addFeeRow()" style="margin-bottom:16px;">+ Add Fee Entry</button>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <h4 style="margin-bottom:16px;color:var(--primary);">Important Dates</h4>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Application Start</label><input type="date" name="application_start" class="form-input" value="<?php echo $dates_val['application_start'] ?? ''; ?>"></div>
                <div class="form-group"><label class="form-label">Application End</label><input type="date" name="application_end" class="form-input" value="<?php echo $dates_val['application_end'] ?? ''; ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Exam Date (Prelims)</label><input type="date" name="exam_date_prelims" class="form-input" value="<?php echo $dates_val['exam_date_prelims'] ?? ''; ?>"></div>
                <div class="form-group"><label class="form-label">Exam Date (Mains)</label><input type="date" name="exam_date_mains" class="form-input" value="<?php echo $dates_val['exam_date_mains'] ?? ''; ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Fee Payment Last Date</label><input type="date" name="fee_payment_last" class="form-input" value="<?php echo $dates_val['fee_payment_last'] ?? ''; ?>"></div>
                <div class="form-group"><label class="form-label">Result Date</label><input type="date" name="result_date" class="form-input" value="<?php echo $dates_val['result_date'] ?? ''; ?>"></div>
                <div class="form-group"><label class="form-label">Interview Date</label><input type="date" name="interview" class="form-input" value="<?php echo $dates_val['interview'] ?? ''; ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Application Deadline (for urgency badge)</label><input type="date" name="application_deadline" class="form-input" value="<?php echo $edit_item['application_deadline'] ?? ''; ?>"></div>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <h4 style="margin-bottom:16px;color:var(--primary);">Important Links</h4>
            <div class="form-group"><label class="form-label">Advertisement PDF URL</label><input type="text" name="advertisement_url" class="form-input" placeholder="https://..." value="<?php echo htmlspecialchars($edit_item['advertisement_url'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Official Website</label><input type="text" name="official_website" class="form-input" placeholder="https://..." value="<?php echo htmlspecialchars($edit_item['official_website'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Application Link</label><input type="text" name="application_link" class="form-input" placeholder="https://..." value="<?php echo htmlspecialchars($edit_item['application_link'] ?? ''); ?>"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Other Link URL</label><input type="text" name="other_link" class="form-input" placeholder="https://..." value="<?php echo htmlspecialchars($edit_item['other_link'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Other Link Text</label><input type="text" name="other_link_text" class="form-input" placeholder="e.g. Help Guide" value="<?php echo htmlspecialchars($edit_item['other_link_text'] ?? ''); ?>"></div>
            </div>

            <hr style="margin:24px 0;border-color:var(--gray-200);">
            <div style="display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap;">
                <?php if ($edit_item): ?>
                <label><input type="checkbox" name="is_featured" value="1" <?php echo $edit_item['is_featured']?'checked':''; ?>> Featured</label>
                <label><input type="checkbox" name="is_active" value="1" <?php echo $edit_item['is_active']?'checked':''; ?>> Active</label>
                <?php endif; ?>
            </div>
            <button type="submit" name="<?php echo $edit_item?'edit':'add'; ?>" class="btn btn-primary btn-lg"><?php echo $edit_item?'Update Job':'Add Job'; ?></button>
        </form>
    </div>
</div>

<script>
function addPostRow() {
    const c = document.getElementById('postsContainer');
    const r = document.createElement('div'); r.className = 'form-row post-row'; r.style.marginBottom = '8px';
    r.innerHTML = `<div class="form-group"><input type="number" name="post_no[]" class="form-input" placeholder="Post No." style="width:80px;"></div>
                   <div class="form-group"><input type="text" name="post_name[]" class="form-input" placeholder="Post Name"></div>
                   <div class="form-group"><input type="number" name="vacancy[]" class="form-input" placeholder="Vacancy" style="width:100px;"></div>
                   <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.post-row').remove()" style="padding:10px 12px;">✕</button></div>`;
    c.appendChild(r);
}
function addQualRow() {
    const c = document.getElementById('qualsContainer');
    const r = document.createElement('div'); r.className = 'form-row qual-row'; r.style.marginBottom = '8px';
    r.innerHTML = `<div class="form-group"><input type="number" name="q_post_no[]" class="form-input" placeholder="Post No." style="width:80px;"></div>
                   <div class="form-group"><input type="text" name="education[]" class="form-input" placeholder="Education qualification required"></div>
                   <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.qual-row').remove()" style="padding:10px 12px;">✕</button></div>`;
    c.appendChild(r);
}
function addFeeRow() {
    const c = document.getElementById('feesContainer');
    const r = document.createElement('div'); r.className = 'form-row fee-row'; r.style.marginBottom = '8px';
    r.innerHTML = `<div class="form-group"><input type="number" name="f_post_no[]" class="form-input" placeholder="Post No." style="width:80px;"></div>
                   <div class="form-group"><input type="text" name="fee_category[]" class="form-input" placeholder="Category (e.g. General, SC/ST)"></div>
                   <div class="form-group"><input type="text" name="fee_amount[]" class="form-input" placeholder="Fee (e.g. ₹100, Exempted)"></div>
                   <div class="form-group" style="flex:0;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.fee-row').remove()" style="padding:10px 12px;">✕</button></div>`;
    c.appendChild(r);
}
</script>

<?php else: ?>
<div class="admin-card">
    <div class="admin-card-header"><h3>All Job Listings</h3><a href="jobs.php?action=add" class="btn btn-primary btn-sm">+ Add New</a></div>
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Organization</th><th>Advt No.</th><th>Vacancy</th><th>Deadline</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody><?php while ($i = $items->fetch_assoc()): ?>
                <tr><td><strong><?php echo htmlspecialchars($i['title']); ?></strong></td><td><?php echo htmlspecialchars($i['company']); ?></td><td><?php echo htmlspecialchars($i['advertise_no'] ?? '-'); ?></td><td><?php echo $i['total_vacancy'] ?? 0; ?></td>
                <td style="font-size:0.85rem;"><?php echo $i['application_deadline'] ? date('d M Y', strtotime($i['application_deadline'])) : '-'; ?></td>
                <td><span class="admin-badge" style="background:<?php echo $i['is_active']?'var(--success-light)':'var(--danger-light)'; ?>;color:<?php echo $i['is_active']?'var(--success)':'var(--danger)'; ?>;"><?php echo $i['is_active']?'Active':'Inactive'; ?></span></td>
                <td class="actions"><a href="jobs.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a><a href="jobs.php?del=<?php echo $i['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete this job and all its data?')">🗑</a></td></tr>
            <?php endwhile; ?></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>

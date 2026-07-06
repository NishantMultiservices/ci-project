<?php
$page_title = 'Manage Exams';
include 'includes/header.php';

$categories = $conn->query("SELECT id, name FROM categories WHERE type = 'exam' ORDER BY name");

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_exam'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $desc = $conn->real_escape_string($_POST['description']);
        $cat = intval($_POST['category_id']);
        $duration = intval($_POST['duration_minutes']);
        $passing = intval($_POST['passing_score']);
        $difficulty = $conn->real_escape_string($_POST['difficulty']);
        $total_q = intval($_POST['total_questions']);
        $is_free = isset($_POST['is_free']) ? 1 : 0;
        $price = $is_free ? 0 : floatval($_POST['price']);
        $conn->query("INSERT INTO exams (title, description, category_id, duration_minutes, total_questions, passing_score, difficulty, is_free, price) VALUES ('$title', '$desc', $cat, $duration, $total_q, $passing, '$difficulty', $is_free, $price)");
        echo '<script>window.location.href="exams.php?msg=added";</script>'; exit;
    }
    if (isset($_POST['edit_exam'])) {
        $id = intval($_POST['exam_id']);
        $title = $conn->real_escape_string($_POST['title']);
        $desc = $conn->real_escape_string($_POST['description']);
        $cat = intval($_POST['category_id']);
        $duration = intval($_POST['duration_minutes']);
        $passing = intval($_POST['passing_score']);
        $difficulty = $conn->real_escape_string($_POST['difficulty']);
        $total_q = intval($_POST['total_questions']);
        $active = isset($_POST['is_active']) ? 1 : 0;
        $is_free = isset($_POST['is_free']) ? 1 : 0;
        $price = $is_free ? 0 : floatval($_POST['price']);
        $conn->query("UPDATE exams SET title='$title', description='$desc', category_id=$cat, duration_minutes=$duration, total_questions=$total_q, passing_score=$passing, difficulty='$difficulty', is_active=$active, is_free=$is_free, price=$price WHERE id=$id");
        echo '<script>window.location.href="exams.php?msg=updated";</script>'; exit;
    }
    if (isset($_POST['add_question'])) {
        $exam_id = intval($_POST['exam_id']);
        $q_text = $conn->real_escape_string($_POST['question_text']);
        $opt_a = $conn->real_escape_string($_POST['option_a']);
        $opt_b = $conn->real_escape_string($_POST['option_b']);
        $opt_c = $conn->real_escape_string($_POST['option_c']);
        $opt_d = $conn->real_escape_string($_POST['option_d']);
        $correct = strtoupper($conn->real_escape_string($_POST['correct_option']));
        $explanation = $conn->real_escape_string($_POST['explanation']);
        $marks = intval($_POST['marks']);
        $conn->query("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES ($exam_id, '$q_text', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$correct', '$explanation', $marks)");
        echo '<script>window.location.href="exams.php?id=' . $exam_id . '&msg=question_added";</script>'; exit;
    }
}

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM exams WHERE id=$id");
    echo '<script>window.location.href="exams.php?msg=deleted";</script>'; exit;
}

if (isset($_GET['del_q'])) {
    $qid = intval($_GET['del_q']);
    $eid = intval($_GET['id']);
    $conn->query("DELETE FROM questions WHERE id=$qid");
    echo '<script>window.location.href="exams.php?id=' . $eid . '&msg=question_deleted";</script>'; exit;
}

$exams = $conn->query("SELECT e.*, c.name as cat_name FROM exams e LEFT JOIN categories c ON e.category_id = c.id ORDER BY e.created_at DESC");
$edit_exam = $edit_id ? $conn->query("SELECT * FROM exams WHERE id=$edit_id")->fetch_assoc() : null;
$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg): ?>
    <div class="alert alert-success" style="margin-bottom:16px;">
        <?php echo match($msg) { 'added' => 'Exam added.', 'updated' => 'Exam updated.', 'deleted' => 'Exam deleted.', 'question_added' => 'Question added.', 'question_deleted' => 'Question deleted.', default => 'Done.' }; ?>
    </div>
<?php endif; ?>

<?php if ($action == 'add' || $edit_exam): ?>
    <a href="exams.php" class="back-link">← Back to Exams</a>
    <div class="admin-card">
        <div class="admin-card-header"><h3><?php echo $edit_exam ? 'Edit Exam' : 'Add New Exam'; ?></h3></div>
        <div class="admin-card-body">
            <form method="POST" class="admin-form">
                <?php if ($edit_exam): ?><input type="hidden" name="exam_id" value="<?php echo $edit_exam['id']; ?>"><?php endif; ?>
                <div class="form-group">
                    <label class="form-label">Exam Title</label>
                    <input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_exam['title'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($edit_exam['category_id'] ?? 0) == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Difficulty</label>
                        <select name="difficulty" class="form-select" required>
                            <?php foreach (['beginner','intermediate','advanced'] as $d): ?>
                                <option value="<?php echo $d; ?>" <?php echo ($edit_exam['difficulty'] ?? '') == $d ? 'selected' : ''; ?>><?php echo ucfirst($d); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($edit_exam['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-input" required value="<?php echo $edit_exam['duration_minutes'] ?? 60; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Questions</label>
                        <input type="number" name="total_questions" class="form-input" required value="<?php echo $edit_exam['total_questions'] ?? 10; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Passing Score (%)</label>
                        <input type="number" name="passing_score" class="form-input" required value="<?php echo $edit_exam['passing_score'] ?? 40; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Pricing</label>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="is_free" value="1" id="examIsFree" <?php echo ($edit_exam['is_free'] ?? 1) ? 'checked' : ''; ?> onchange="document.getElementById('examPriceRow').style.display=this.checked?'none':'flex';"> Free</label>
                        </div>
                    </div>
                    <div class="form-group" id="examPriceRow" style="display:<?php echo (isset($edit_exam) && !$edit_exam['is_free']) ? 'flex' : 'none'; ?>;">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" name="price" class="form-input" step="0.01" min="0" value="<?php echo $edit_exam['price'] ?? 0; ?>">
                    </div>
                </div>
                <?php if ($edit_exam): ?>
                    <div class="form-group">
                        <label class="form-check-label">
                            <input type="checkbox" name="is_active" value="1" <?php echo $edit_exam['is_active'] ? 'checked' : ''; ?>> Active
                        </label>
                    </div>
                <?php endif; ?>
                <button type="submit" name="<?php echo $edit_exam ? 'edit_exam' : 'add_exam'; ?>" class="btn btn-primary"><?php echo $edit_exam ? 'Update Exam' : 'Add Exam'; ?></button>
            </form>
        </div>
    </div>

    <?php if ($edit_exam):
        $questions = $conn->query("SELECT * FROM questions WHERE exam_id = {$edit_exam['id']} ORDER BY id");
    ?>
    <div class="admin-card" style="margin-top:24px;">
        <div class="admin-card-header">
            <h3>Questions (<?php echo $questions->num_rows; ?> / <?php echo $edit_exam['total_questions']; ?>)</h3>
        </div>
        <?php if ($questions->num_rows > 0): ?>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th style="width:40px;">#</th><th>Question</th><th style="width:80px;">A</th><th style="width:80px;">B</th><th style="width:80px;">C</th><th style="width:80px;">D</th><th style="width:60px;">✓</th><th style="width:70px;">Marks</th><th style="width:80px;">Actions</th></tr></thead>
                <tbody>
                    <?php $qn = 1; while ($q = $questions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $qn++; ?></td>
                        <td><?php echo htmlspecialchars(substr($q['question_text'], 0, 60)); ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars(substr($q['option_a'], 0, 15)); ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars(substr($q['option_b'], 0, 15)); ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars(substr($q['option_c'], 0, 15)); ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars(substr($q['option_d'], 0, 15)); ?></td>
                        <td><span class="admin-badge" style="background:var(--success-light);color:var(--success);font-weight:700;"><?php echo $q['correct_option']; ?></span></td>
                        <td><?php echo $q['marks']; ?></td>
                        <td><a href="exams.php?del_q=<?php echo $q['id']; ?>&id=<?php echo $edit_exam['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete this question?')">🗑</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <div class="admin-card-body" style="border-top:2px solid var(--primary);background:#f8faff;">
            <h4 style="margin-bottom:16px;color:var(--primary);">➕ Add New Question</h4>
            <form method="POST" class="admin-form">
                <input type="hidden" name="exam_id" value="<?php echo $edit_exam['id']; ?>">
                <div class="form-group">
                    <label class="form-label">Question Text</label>
                    <input type="text" name="question_text" class="form-input" required placeholder="Enter the question...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Option A</label>
                        <input type="text" name="option_a" class="form-input" required placeholder="Option A">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option B</label>
                        <input type="text" name="option_b" class="form-input" required placeholder="Option B">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Option C</label>
                        <input type="text" name="option_c" class="form-input" required placeholder="Option C">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option D</label>
                        <input type="text" name="option_d" class="form-input" required placeholder="Option D">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Correct Option</label>
                        <select name="correct_option" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="form-input" value="1" min="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Explanation (optional)</label>
                    <textarea name="explanation" class="form-textarea" placeholder="Explain why this answer is correct..."></textarea>
                </div>
                <button type="submit" name="add_question" class="btn btn-primary">➕ Add Question</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>All Mock Exams</h3>
            <a href="exams.php?action=add" class="btn btn-primary btn-sm">+ Add New Exam</a>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>Title</th><th>Category</th><th>Difficulty</th><th>Duration</th><th>Questions</th><th>Price</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($e = $exams->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($e['cat_name'] ?? '-'); ?></td>
                        <td><span class="admin-badge" style="background:<?php echo $e['difficulty']=='beginner'?'var(--success-light)':($e['difficulty']=='intermediate'?'var(--warning-light)':'var(--danger-light)'); ?>;color:<?php echo $e['difficulty']=='beginner'?'var(--success)':($e['difficulty']=='intermediate'?'var(--warning)':'var(--danger)'); ?>;"><?php echo ucfirst($e['difficulty']); ?></span></td>
                        <td><?php echo $e['duration_minutes']; ?> min</td>
                        <td><?php echo $e['total_questions']; ?></td>
                        <td><?php echo $e['is_free'] ? '<span class="admin-badge" style="background:var(--success-light);color:var(--success);">Free</span>' : '₹'.number_format($e['price'],0); ?></td>
                        <td><span class="admin-badge" style="background:<?php echo $e['is_active']?'var(--success-light)':'var(--danger-light)'; ?>;color:<?php echo $e['is_active']?'var(--success)':'var(--danger)'; ?>;"><?php echo $e['is_active']?'Active':'Inactive'; ?></span></td>
                        <td class="actions">
                            <a href="exams.php?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-primary btn-xs">✏</a>
                            <a href="exams.php?del=<?php echo $e['id']; ?>" class="btn btn-sm btn-danger btn-xs" onclick="return confirm('Delete this exam and all its questions?')">🗑</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to take exams.';
    redirect('/auth/login.php');
}

$exam_id = intval($_GET['id'] ?? 0);
$exam = $conn->query("SELECT e.*, c.name as category_name FROM exams e LEFT JOIN categories c ON e.category_id = c.id WHERE e.id = $exam_id AND e.is_active = 1")->fetch_assoc();

if (!$exam) {
    $_SESSION['error'] = 'Exam not found.';
    redirect('/exams/index.php');
}

$is_paid = !$exam['is_free'];
if ($is_paid) {
    // Check if user has purchased this exam
    $purchased = $conn->query("SELECT id FROM purchases WHERE user_id = {$_SESSION['user_id']} AND item_type = 'exam' AND item_id = $exam_id AND status = 'completed'")->num_rows > 0;
    if (!$purchased) {
        $page_title = $exam['title'];
        include '../includes/header.php';
?>
<section class="section" style="padding-top:80px;text-align:center;">
    <div class="container" style="max-width:500px;">
        <div style="font-size:3rem;margin-bottom:16px;">🔒</div>
        <h2>Premium Exam</h2>
        <p style="margin:12px 0 20px;color:#64748b;">This exam requires a purchase to access. Price: <strong>₹<?php echo number_format($exam['price'], 0); ?></strong></p>
        <p style="margin-bottom:24px;color:#94a3b8;">Payment integration coming soon. Please contact support for access.</p>
        <a href="index.php" class="btn btn-primary">← Back to Exams</a>
    </div>
</section>
<?php include '../includes/footer.php'; exit;
    }
}

$questions = $conn->query("SELECT * FROM questions WHERE exam_id = $exam_id ORDER BY id");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    $total_marks = 0;
    $correct = 0;
    $incorrect = 0;
    $unanswered = 0;
    $time_taken = intval($_POST['time_taken'] ?? 0);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO exam_results (user_id, exam_id, total_marks) VALUES (?, ?, ?)");
        $total_q = $questions->num_rows;
        $stmt->bind_param("iii", $_SESSION['user_id'], $exam_id, $total_q);
        $stmt->execute();
        $result_id = $stmt->insert_id;

        $questions->data_seek(0);
        while ($q = $questions->fetch_assoc()) {
            $selected = $_POST['question_' . $q['id']] ?? '';
            $is_correct = ($selected === $q['correct_option']) ? 1 : 0;

            if ($is_correct) $correct++;
            elseif (empty($selected)) $unanswered++;
            else $incorrect++;

            $ans_stmt = $conn->prepare("INSERT INTO user_answers (result_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
            $ans_stmt->bind_param("iisi", $result_id, $q['id'], $selected, $is_correct);
            $ans_stmt->execute();

            if ($is_correct) $total_marks += $q['marks'];
        }

        $percentage = $total_q > 0 ? round(($correct / $total_q) * 100, 2) : 0;
        $status = $percentage >= $exam['passing_score'] ? 'passed' : 'failed';

        $update = $conn->prepare("UPDATE exam_results SET score = ?, percentage = ?, correct_count = ?, incorrect_count = ?, unanswered_count = ?, time_taken = ?, status = ? WHERE id = ?");
        $update->bind_param("idiiiisi", $total_marks, $percentage, $correct, $incorrect, $unanswered, $time_taken, $status, $result_id);
        $update->execute();

        $conn->commit();
        redirect("/exams/results.php?id=$result_id");
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Failed to submit exam. Please try again.';
    }
}

$page_title = $exam['title'];
include '../includes/header.php';
?>

<section class="exam-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($exam['title']); ?></h1>
        <p><?php echo htmlspecialchars($exam['description']); ?> • <?php echo $questions->num_rows; ?> Questions • <?php echo $exam['duration_minutes']; ?> Minutes</p>
    </div>
</section>

<div class="container">
    <div class="exam-container">
        <div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="examForm">
                <input type="hidden" name="submit_exam" value="1">
                <input type="hidden" name="time_taken" id="timeTaken" value="0">

                <?php $qnum = 1; $questions->data_seek(0); while ($q = $questions->fetch_assoc()): ?>
                    <div class="exam-question" id="q-<?php echo $q['id']; ?>">
                        <span class="question-number">Question <?php echo $qnum; ?> of <?php echo $questions->num_rows; ?></span>
                        <div class="question-text"><?php echo htmlspecialchars($q['question_text']); ?></div>
                        <div class="options">
                            <?php foreach (['A', 'B', 'C', 'D'] as $opt):
                                $option_text = $q['option_' . strtolower($opt)];
                            ?>
                                <label class="option" onclick="document.querySelectorAll('#q-<?php echo $q['id']; ?> .option').forEach(o=>o.classList.remove('selected'));this.classList.add('selected');">
                                    <input type="radio" name="question_<?php echo $q['id']; ?>" value="<?php echo $opt; ?>" onchange="updatePalette(<?php echo $q['id']; ?>)">
                                    <span class="option-letter"><?php echo $opt; ?></span>
                                    <span><?php echo htmlspecialchars($option_text); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php $qnum++; endwhile; ?>

                <div class="nav-buttons" style="margin-bottom:30px;">
                    <button type="submit" class="btn btn-success btn-lg" style="flex:1;" onclick="return confirm('Are you sure you want to submit the exam?')">
                        Submit Exam
                    </button>
                </div>
            </form>
        </div>

        <div class="exam-sidebar">
            <div class="timer">
                <div class="timer-display" id="timerDisplay"><?php echo sprintf('%02d:%02d', $exam['duration_minutes'], 0); ?></div>
                <div class="timer-label">Time Remaining</div>
            </div>

            <div style="margin-bottom:12px;">
                <strong>Question Palette</strong>
                <div style="font-size:0.85rem;color:var(--gray-500);margin-top:4px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;margin-right:12px;">
                        <span style="width:12px;height:12px;background:var(--success-light);border:2px solid var(--success);border-radius:3px;display:inline-block;"></span> Answered
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        <span style="width:12px;height:12px;background:var(--white);border:2px solid var(--gray-300);border-radius:3px;display:inline-block;"></span> Unanswered
                    </span>
                </div>
            </div>

            <div class="question-palette" id="palette">
                <?php $qnum = 1; $questions->data_seek(0); while ($q = $questions->fetch_assoc()): ?>
                    <div class="q-palette-item" id="pal-<?php echo $q['id']; ?>" onclick="document.getElementById('q-<?php echo $q['id']; ?>').scrollIntoView({behavior:'smooth'})">
                        <?php echo $qnum++; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <div style="font-size:0.85rem;color:var(--gray-500);margin-top:16px;padding-top:16px;border-top:1px solid var(--gray-200);">
                <strong>Instructions:</strong>
                <ul style="padding-left:16px;margin-top:6px;line-height:1.8;">
                    <li>Select one option per question</li>
                    <li>You can change your answer anytime</li>
                    <li>Exam auto-submits when time runs out</li>
                    <li>No negative marking</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
let totalSeconds = <?php echo $exam['duration_minutes'] * 60; ?>;
let answeredQuestions = new Set();

function updatePalette(questionId) {
    const pal = document.getElementById('pal-' + questionId);
    if (pal) {
        pal.classList.add('answered');
        pal.classList.remove('current');
        answeredQuestions.add(questionId);
    }
}

document.querySelectorAll('.option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const qId = this.name.replace('question_', '');
        updatePalette(parseInt(qId));
    });
});

document.getElementById('examForm').addEventListener('submit', function() {
    const totalExamSeconds = <?php echo $exam['duration_minutes'] * 60; ?>;
    const elapsed = totalExamSeconds - totalSeconds;
    document.getElementById('timeTaken').value = elapsed;
});

startExamTimer(totalSeconds, 'timerDisplay', 'examForm');
</script>

<?php include '../includes/footer.php'; ?>

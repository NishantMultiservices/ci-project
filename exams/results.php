<?php
require_once '../config/database.php';

if (!isLoggedIn()) redirect('/auth/login.php');

$result_id = intval($_GET['id'] ?? 0);
$result = $conn->query("
    SELECT r.*, e.title as exam_title, e.description, e.passing_score, e.total_questions,
           c.name as category_name
    FROM exam_results r
    JOIN exams e ON r.exam_id = e.id
    LEFT JOIN categories c ON e.category_id = c.id
    WHERE r.id = $result_id AND r.user_id = {$_SESSION['user_id']}
")->fetch_assoc();

if (!$result) {
    $_SESSION['error'] = 'Result not found.';
    redirect('/exams/index.php');
}

$answers = $conn->query("
    SELECT ua.*, q.question_text, q.correct_option, q.option_a, q.option_b, q.option_c, q.option_d, q.explanation
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    WHERE ua.result_id = $result_id
");

$page_title = 'Exam Results';
include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="results-container">
            <h1 style="font-size:1.6rem;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:1px;">Exam Complete</h1>
            <h2 style="font-size:2rem;margin:10px 0;"><?php echo htmlspecialchars($result['exam_title']); ?></h2>

            <div class="result-circle <?php echo $result['status']; ?>">
                <div class="result-number"><?php echo round($result['percentage']); ?>%</div>
                <div class="result-label"><?php echo ucfirst($result['status']); ?></div>
            </div>

            <?php if ($result['status'] == 'passed'): ?>
                <div class="alert alert-success" style="text-align:left;">Congratulations! You passed the exam with <?php echo $result['percentage']; ?>%.</div>
            <?php else: ?>
                <div class="alert alert-danger" style="text-align:left;">You scored <?php echo $result['percentage']; ?>%. The passing score is <?php echo $result['passing_score']; ?>%. Keep practicing!</div>
            <?php endif; ?>

            <div class="result-stats">
                <div class="result-stat correct">
                    <div class="num"><?php echo $result['correct_count']; ?></div>
                    <div class="lbl">Correct</div>
                </div>
                <div class="result-stat wrong">
                    <div class="num"><?php echo $result['incorrect_count']; ?></div>
                    <div class="lbl">Incorrect</div>
                </div>
                <div class="result-stat unanswered">
                    <div class="num"><?php echo $result['unanswered_count']; ?></div>
                    <div class="lbl">Unanswered</div>
                </div>
            </div>

            <div style="background:var(--white);border-radius:var(--radius-lg);padding:30px;box-shadow:var(--shadow);text-align:left;margin-top:30px;">
                <h3 style="margin-bottom:20px;">Detailed Review</h3>
                <?php $qnum = 1; while ($ans = $answers->fetch_assoc()): ?>
                    <div style="padding:20px;border:1px solid var(--gray-200);border-radius:var(--radius);margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span class="question-number">Question <?php echo $qnum; ?></span>
                            <span class="card-badge <?php echo $ans['is_correct'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $ans['is_correct'] ? '✓ Correct' : '✗ Incorrect'; ?>
                            </span>
                        </div>
                        <p style="font-weight:600;margin-bottom:12px;"><?php echo htmlspecialchars($ans['question_text']); ?></p>
                        <div style="font-size:0.9rem;">
                            <?php foreach (['A','B','C','D'] as $opt):
                                $opt_field = 'option_' . strtolower($opt);
                                $is_correct_opt = ($opt === $ans['correct_option']);
                                $is_selected = ($opt === $ans['selected_option']);
                                $class = $is_correct_opt ? 'correct' : ($is_selected ? 'wrong' : '');
                            ?>
                                <div class="option <?php echo $class; ?>" style="cursor:default;margin-bottom:6px;">
                                    <span class="option-letter"><?php echo $opt; ?></span>
                                    <span><?php echo htmlspecialchars($ans[$opt_field]); ?></span>
                                    <?php if ($is_correct_opt): ?><span style="margin-left:auto;font-weight:600;">✓ Correct Answer</span><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($ans['explanation']): ?>
                            <div style="margin-top:12px;padding:12px;background:var(--info-light);border-radius:var(--radius);font-size:0.9rem;">
                                <strong>Explanation:</strong> <?php echo htmlspecialchars($ans['explanation']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php $qnum++; endwhile; ?>
            </div>

            <div style="display:flex;gap:12px;justify-content:center;margin-top:30px;">
                <a href="index.php" class="btn btn-primary">Back to Exams</a>
                <a href="take_exam.php?id=<?php echo $result_id; ?>" class="btn btn-outline">Retry Exam</a>
                <a href="../dashboard/index.php" class="btn btn-secondary">Dashboard</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

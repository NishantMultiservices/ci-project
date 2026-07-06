<?php
require_once '../config/database.php';

if (!isLoggedIn()) redirect('/auth/login.php');
if (isAdmin()) redirect('/admin/index.php');

$user = getUser($conn, $_SESSION['user_id']);

$exam_history = $conn->query("
    SELECT r.*, e.title as exam_title, e.difficulty
    FROM exam_results r
    JOIN exams e ON r.exam_id = e.id
    WHERE r.user_id = {$_SESSION['user_id']}
    ORDER BY r.completed_at DESC LIMIT 10
");

$saved_items = $conn->query("
    SELECT s.item_type, s.item_id, s.created_at,
           COALESCE(e.title, n.title, j.title, ht.title, ak.title, en.title) as title,
           COALESCE(e.description, n.description, j.description, ht.description, ak.description, en.description) as description
    FROM saved_items s
    LEFT JOIN exams e ON s.item_type = 'exam' AND s.item_id = e.id
    LEFT JOIN study_notes n ON s.item_type = 'note' AND s.item_id = n.id
    LEFT JOIN job_listings j ON s.item_type = 'job' AND s.item_id = j.id
    LEFT JOIN hall_tickets ht ON s.item_type = 'hall_ticket' AND s.item_id = ht.id
    LEFT JOIN answer_keys ak ON s.item_type = 'answer_key' AND s.item_id = ak.id
    LEFT JOIN exam_notifications en ON s.item_type = 'exam_notification' AND s.item_id = en.id
    WHERE s.user_id = {$_SESSION['user_id']}
    ORDER BY s.created_at DESC
");

$stats = $conn->query("
    SELECT
        COUNT(DISTINCT exam_id) as total_exams_taken,
        COUNT(*) as total_attempts,
        ROUND(AVG(percentage), 1) as avg_score,
        SUM(CASE WHEN status = 'passed' THEN 1 ELSE 0 END) as passed_count
    FROM exam_results WHERE user_id = {$_SESSION['user_id']}
")->fetch_assoc();

$page_title = 'Dashboard';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.2rem;">
                        <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 2)); ?>
                    </div>
                    <div>
                        <h1 style="font-size:1.8rem;">Welcome, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?> 👋</h1>
                        <p style="color:var(--gray-500);">Here's an overview of your learning journey.</p>
                    </div>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/profile/index.php" class="btn btn-outline btn-sm">✏ Edit Profile</a>
        </div>
        <div style="height:30px;"></div>

        <?php if ($stats['total_attempts'] > 0): ?>
            <div class="stats-grid" style="margin-bottom:40px;">
                <div class="stat-item" style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);">
                    <div class="stat-number" style="color:var(--primary);"><?php echo $stats['total_exams_taken']; ?></div>
                    <div class="stat-label">Exams Taken</div>
                </div>
                <div class="stat-item" style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);">
                    <div class="stat-number" style="color:var(--success);"><?php echo $stats['passed_count']; ?></div>
                    <div class="stat-label">Exams Passed</div>
                </div>
                <div class="stat-item" style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);">
                    <div class="stat-number" style="color:var(--warning);"><?php echo $stats['avg_score']; ?>%</div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-item" style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);">
                    <div class="stat-number" style="color:var(--secondary);"><?php echo $stats['total_attempts']; ?></div>
                    <div class="stat-label">Total Attempts</div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Start taking mock exams to see your statistics here!</div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:30px;">
            <div>
                <h3 style="margin-bottom:16px;">Recent Exam History</h3>
                <?php if ($exam_history->num_rows === 0): ?>
                    <div class="empty-state" style="padding:30px;background:var(--white);border-radius:var(--radius-lg);">
                        <div class="icon">📝</div>
                        <h3>No exams taken yet</h3>
                        <p>Take your first mock exam to see your history.</p>
                        <a href="../exams/index.php" class="btn btn-primary" style="margin-top:12px;">Browse Exams</a>
                    </div>
                <?php else: ?>
                    <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:var(--gray-100);">
                                    <th style="padding:14px 20px;text-align:left;font-size:0.85rem;font-weight:600;color:var(--gray-500);">Exam</th>
                                    <th style="padding:14px 20px;text-align:center;font-size:0.85rem;font-weight:600;color:var(--gray-500);">Score</th>
                                    <th style="padding:14px 20px;text-align:center;font-size:0.85rem;font-weight:600;color:var(--gray-500);">Status</th>
                                    <th style="padding:14px 20px;text-align:right;font-size:0.85rem;font-weight:600;color:var(--gray-500);">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($h = $exam_history->fetch_assoc()): ?>
                                    <tr style="border-top:1px solid var(--gray-200);">
                                        <td style="padding:14px 20px;font-weight:600;"><?php echo htmlspecialchars($h['exam_title']); ?></td>
                                        <td style="padding:14px 20px;text-align:center;"><?php echo $h['percentage']; ?>%</td>
                                        <td style="padding:14px 20px;text-align:center;">
                                            <span class="card-badge <?php echo $h['status'] === 'passed' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($h['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding:14px 20px;text-align:right;color:var(--gray-500);font-size:0.9rem;">
                                            <?php echo timeAgo($h['completed_at']); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 style="margin-bottom:16px;">Saved Items</h3>
                <?php if ($saved_items->num_rows === 0): ?>
                    <div class="empty-state" style="padding:30px;background:var(--white);border-radius:var(--radius-lg);">
                        <div class="icon">⭐</div>
                        <h3>Nothing saved yet</h3>
                        <p>Bookmark exams, notes, and jobs to access them quickly.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <?php while ($s = $saved_items->fetch_assoc()): ?>
                            <div style="background:var(--white);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);">
                            <div style="font-size:0.85rem;color:var(--gray-500);margin-bottom:4px;">
                                <?php echo match($s['item_type']) {
                                    'exam' => '📝 Exam',
                                    'note' => '📖 Note',
                                    'job' => '💼 Job',
                                    'hall_ticket' => '🎫 Hall Ticket',
                                    'answer_key' => '🔑 Answer Key',
                                    'exam_notification' => '📢 Result/Notice',
                                    default => '📌 Item'
                                }; ?>
                            </div>
                                <div style="font-weight:600;font-size:0.95rem;"><?php echo htmlspecialchars($s['title'] ?? 'Untitled'); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

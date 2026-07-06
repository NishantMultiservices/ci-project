<?php
require_once '../config/database.php';

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

$sql = "SELECT e.*, c.name as category_name, c.slug as category_slug,
        (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) as question_count
        FROM exams e
        LEFT JOIN categories c ON e.category_id = c.id
        WHERE e.is_active = 1";

if ($category_id > 0) $sql .= " AND e.category_id = $category_id";
if ($difficulty) $sql .= " AND e.difficulty = '" . $conn->real_escape_string($difficulty) . "'";
$sql .= " ORDER BY e.created_at DESC";

$exams = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories WHERE type = 'exam' ORDER BY name");

$page_title = 'Mock Exams';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Practice Makes Perfect</span>
            <h2>Mock Exams</h2>
            <p>Choose a subject and test your knowledge with timed practice exams.</p>
        </div>

        <div class="filter-tabs" id="examFilters">
            <button class="filter-tab active" data-filter="all">All Subjects</button>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <button class="filter-tab" data-filter="<?php echo $cat['slug']; ?>"><?php echo htmlspecialchars($cat['name']); ?></button>
            <?php endwhile; ?>
        </div>

        <?php if ($exams->num_rows === 0): ?>
            <div class="empty-state">
                <div class="icon">📝</div>
                <h3>No exams available yet</h3>
                <p>Check back soon for new mock exams.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php while ($exam = $exams->fetch_assoc()):
                    $difficultyColor = match($exam['difficulty']) {
                        'beginner' => 'badge-success',
                        'intermediate' => 'badge-warning',
                        'advanced' => 'badge-danger',
                        default => 'badge-info'
                    };
                ?>
                    <div class="card" style="cursor:pointer;" onclick="window.location='details.php?id=<?php echo $exam['id']; ?>'" data-category="<?php echo $exam['category_slug']; ?>">
                        <div class="card-image" style="background:linear-gradient(135deg,var(--primary-light),var(--info-light));color:var(--primary);">
                            <?php
                                $icons = ['calculator', 'book-open', 'flask', 'monitor', 'brain'];
                                echo $icons[array_rand($icons)];
                            ?>
                        </div>
                        <div class="card-body">
                            <div class="card-badge exam"><?php echo htmlspecialchars($exam['category_name'] ?? 'General'); ?></div>
                            <span class="card-badge <?php echo $difficultyColor; ?>" style="margin-left:4px;"><?php echo ucfirst($exam['difficulty']); ?></span>
                            <span class="card-badge" style="background:<?php echo $exam['is_free'] ? 'var(--success-light)' : 'var(--warning-light)'; ?>;color:<?php echo $exam['is_free'] ? 'var(--success)' : 'var(--warning)'; ?>;"><?php echo $exam['is_free'] ? 'Free' : '₹'.number_format($exam['price'],0); ?></span>
                            <h3><?php echo htmlspecialchars($exam['title']); ?></h3>
                            <p><?php echo htmlspecialchars($exam['description']); ?></p>
                            <div class="card-meta">
                                <span>⏱ <?php echo $exam['duration_minutes']; ?> min</span>
                                <span>📋 <?php echo $exam['question_count'] ?: $exam['total_questions']; ?> questions</span>
                                <span>🎯 <?php echo $exam['passing_score']; ?>% to pass</span>
                            </div>
                        </div>
                        <div class="card-footer" onclick="event.stopPropagation();">
                            <a href="take_exam.php?id=<?php echo $exam['id']; ?>" class="btn <?php echo $exam['is_free'] ? 'btn-primary' : 'btn-outline'; ?> btn-sm">Start Exam</a>
                            <a href="details.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm" style="background:var(--gray-100);">Details</a>
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-sm" style="background:var(--gray-100);" onclick="toggleSave(this,'exam',<?php echo $exam['id']; ?>)">
                                    <span class="save-icon">☆</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initTabs('examFilters', '.cards-grid .card');
});
</script>

<?php include '../includes/footer.php'; ?>

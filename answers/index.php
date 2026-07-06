<?php
require_once '../config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM answer_keys WHERE is_active = 1";

if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (title LIKE '%$s%' OR exam_name LIKE '%$s%' OR subject LIKE '%$s%' OR organization LIKE '%$s%')";
}
$sql .= " ORDER BY is_featured DESC, created_at DESC";

$answer_keys = $conn->query($sql);

$page_title = 'Answer Keys';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Check Your Answers</span>
            <h2>Answer Keys</h2>
            <p>Download official answer keys for recent examinations. Compare your answers and estimate your score.</p>
        </div>

        <div class="search-bar">
            <form method="GET" action="" style="display:flex;gap:12px;width:100%;">
                <input type="text" name="search" class="form-input" placeholder="Search answer keys by exam, subject, or organization..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($answer_keys->num_rows === 0): ?>
            <div class="empty-state">
                <div class="icon">🔑</div>
                <h3>No answer keys available</h3>
                <p>Check back after exams are conducted for official answer key releases.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php while ($ak = $answer_keys->fetch_assoc()): ?>
                    <div class="card" style="cursor:pointer;" onclick="window.location='details.php?id=<?php echo $ak['id']; ?>'">
                        <div class="card-image" style="background:linear-gradient(135deg,var(--success-light),#A7F3D0);color:var(--success);">
                            🔑
                        </div>
                        <div class="card-body">
                            <?php if ($ak['is_featured']): ?>
                                <span class="card-badge badge-info">Featured</span>
                            <?php endif; ?>
                            <span class="card-badge note">Answer Key</span>
                            <h3><?php echo htmlspecialchars($ak['title']); ?></h3>
                            <p><?php echo htmlspecialchars($ak['description']); ?></p>
                            <div class="card-meta">
                                <span>📋 <?php echo htmlspecialchars($ak['exam_name']); ?></span>
                                <?php if ($ak['subject']): ?>
                                    <span>📚 <?php echo htmlspecialchars($ak['subject']); ?></span>
                                <?php endif; ?>
                                <span>🏛 <?php echo htmlspecialchars($ak['organization']); ?></span>
                                <span>📄 <?php echo strtoupper($ak['file_type']); ?></span>
                            </div>
                        </div>
                        <div class="card-footer" onclick="event.stopPropagation();" style="background:transparent;border:none;padding:0;min-height:0;">
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

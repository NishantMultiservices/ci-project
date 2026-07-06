<?php
require_once '../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM exam_notifications WHERE is_active = 1";
$countSql = "SELECT COUNT(*) as c FROM exam_notifications WHERE is_active = 1";

if ($type) {
    $t = $conn->real_escape_string($type);
    $sql .= " AND notification_type = '$t'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (title LIKE '%$s%' OR exam_name LIKE '%$s%' OR organization LIKE '%$s%')";
}

$totalNotices = $conn->query($countSql)->fetch_assoc()['c'];
$sql .= " ORDER BY is_featured DESC, created_at DESC";
$notifications = $conn->query($sql);

$page_title = 'Exam Results & Notifications';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Stay Updated</span>
            <h2>Exam Results & Notifications</h2>
            <p><?php echo $totalNotices; ?> notifications available. Track exam results, admit cards, syllabus updates and more.</p>
        </div>

        <div class="search-bar">
            <form method="GET" action="" style="display:flex;gap:12px;width:100%;">
                <input type="text" name="search" class="form-input" placeholder="Search notifications by exam or organization..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="filter-tabs" id="resultFilters">
            <a href="index.php" class="filter-tab <?php echo !$type ? 'active' : ''; ?>">All Notifications</a>
            <a href="?type=result" class="filter-tab <?php echo $type === 'result' ? 'active' : ''; ?>">📊 Results</a>
            <a href="?type=admit_card" class="filter-tab <?php echo $type === 'admit_card' ? 'active' : ''; ?>">🎫 Admit Cards</a>
            <a href="?type=exam_date" class="filter-tab <?php echo $type === 'exam_date' ? 'active' : ''; ?>">📅 Exam Dates</a>
            <a href="?type=syllabus" class="filter-tab <?php echo $type === 'syllabus' ? 'active' : ''; ?>">📚 Syllabus</a>
            <a href="?type=other" class="filter-tab <?php echo $type === 'other' ? 'active' : ''; ?>">Other</a>
        </div>

        <?php if ($notifications->num_rows === 0): ?>
            <div class="empty-state">
                <div class="icon">📢</div>
                <h3>No notifications found</h3>
                <p>Try a different filter or check back later for new updates.</p>
            </div>
        <?php else: ?>
            <?php while ($n = $notifications->fetch_assoc()):
                $typeColors = [
                    'result' => ['bg' => 'var(--success-light)', 'text' => 'var(--success)', 'icon' => '📊'],
                    'admit_card' => ['bg' => 'var(--warning-light)', 'text' => 'var(--warning)', 'icon' => '🎫'],
                    'exam_date' => ['bg' => 'var(--info-light)', 'text' => 'var(--info)', 'icon' => '📅'],
                    'syllabus' => ['bg' => 'var(--primary-light)', 'text' => 'var(--primary)', 'icon' => '📚'],
                    'other' => ['bg' => 'var(--gray-100)', 'text' => 'var(--gray-700)', 'icon' => '📢']
                ];
                $tc = $typeColors[$n['notification_type']] ?? $typeColors['other'];
            ?>
                <div class="job-card" style="cursor:pointer;<?php echo $n['is_featured'] ? 'border-left:4px solid var(--primary);' : ''; ?>" onclick="window.location='details.php?id=<?php echo $n['id']; ?>'">
                    <div style="display:flex;align-items:flex-start;gap:16px;">
                        <div style="width:50px;height:50px;border-radius:12px;background:<?php echo $tc['bg']; ?>;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
                            <?php echo $tc['icon']; ?>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
                                <span class="card-badge" style="background:<?php echo $tc['bg']; ?>;color:<?php echo $tc['text']; ?>;">
                                    <?php echo ucfirst(str_replace('_', ' ', $n['notification_type'])); ?>
                                </span>
                                <?php if ($n['is_featured']): ?>
                                    <span class="card-badge badge-info">Featured</span>
                                <?php endif; ?>
                                <?php if ($n['exam_name']): ?>
                                    <span style="font-size:0.85rem;color:var(--gray-500);">📋 <?php echo htmlspecialchars($n['exam_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($n['title']); ?></h3>
                            <p style="margin-top:4px;"><?php echo htmlspecialchars($n['description']); ?></p>
                            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:12px;font-size:0.85rem;color:var(--gray-500);">
                                <?php if ($n['organization']): ?>
                                    <span>🏛 <?php echo htmlspecialchars($n['organization']); ?></span>
                                <?php endif; ?>
                                <?php if ($n['result_date']): ?>
                                    <span>📅 Result: <?php echo date('d M Y', strtotime($n['result_date'])); ?></span>
                                <?php endif; ?>
                                <span>🕐 Posted <?php echo timeAgo($n['created_at']); ?></span>
                            </div>
                            <div style="margin-top:14px;display:flex;gap:10px;" onclick="event.stopPropagation();">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM hall_tickets WHERE is_active = 1";

if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (title LIKE '%$s%' OR exam_name LIKE '%$s%' OR organization LIKE '%$s%')";
}
$sql .= " ORDER BY is_featured DESC, exam_date ASC";

$hall_tickets = $conn->query($sql);

$page_title = 'Hall Tickets';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Exam Access</span>
            <h2>Hall Tickets / Admit Cards</h2>
            <p>Download hall tickets and admit cards for upcoming examinations. Keep a printed copy ready for exam day.</p>
        </div>

        <div class="search-bar">
            <form method="GET" action="" style="display:flex;gap:12px;width:100%;">
                <input type="text" name="search" class="form-input" placeholder="Search by exam name, organization, or title..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($hall_tickets->num_rows === 0): ?>
            <div class="empty-state">
                <div class="icon">🎫</div>
                <h3>No hall tickets available</h3>
                <p>Check back later for new hall ticket releases.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php while ($ht = $hall_tickets->fetch_assoc()):
                    $examDate = strtotime($ht['exam_date']);
                    $daysLeft = ceil(($examDate - time()) / 86400);
                    $isUrgent = $daysLeft <= 7 && $daysLeft > 0;
                    $isExpired = $daysLeft <= 0;
                ?>
                    <div class="card" style="cursor:pointer;" onclick="window.location='details.php?id=<?php echo $ht['id']; ?>'">
                        <div class="card-image" style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);color:var(--warning);">
                            🎫
                        </div>
                        <div class="card-body">
                            <?php if ($ht['is_featured']): ?>
                                <span class="card-badge badge-info">Featured</span>
                            <?php endif; ?>
                            <span class="card-badge badge-warning">Admit Card</span>
                            <h3><?php echo htmlspecialchars($ht['title']); ?></h3>
                            <p><?php echo htmlspecialchars($ht['description']); ?></p>
                            <div class="card-meta">
                                <span>📋 <?php echo htmlspecialchars($ht['exam_name']); ?></span>
                                <span>🏛 <?php echo htmlspecialchars($ht['organization']); ?></span>
                                <span>📅 <?php echo date('d M Y', $examDate); ?></span>
                            </div>
                            <?php if ($ht['instructions']): ?>
                                <div style="margin-top:10px;padding:10px;background:var(--info-light);border-radius:8px;font-size:0.85rem;color:var(--gray-700);">
                                    <strong>⚠ Instructions:</strong> <?php echo htmlspecialchars($ht['instructions']); ?>
                                </div>
                            <?php endif; ?>
                            <div style="margin-top:12px;">
                                <span class="deadline <?php echo $isExpired ? 'badge-danger' : ($isUrgent ? 'urgent' : 'normal'); ?>" style="display:inline-block;padding:4px 12px;border-radius:var(--radius-full);font-size:0.85rem;background:<?php echo $isExpired ? 'var(--danger-light)' : ($isUrgent ? 'var(--warning-light)' : 'var(--success-light)'); ?>;color:<?php echo $isExpired ? 'var(--danger)' : ($isUrgent ? 'var(--warning)' : 'var(--success)'); ?>;">
                                    <?php if ($isExpired): ?>
                                        ⏰ Exam Passed
                                    <?php elseif ($isUrgent): ?>
                                        🔥 Exam in <?php echo $daysLeft; ?> day<?php echo $daysLeft > 1 ? 's' : ''; ?>
                                    <?php else: ?>
                                        ✅ Exam on <?php echo date('d M', $examDate); ?>
                                    <?php endif; ?>
                                </span>
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

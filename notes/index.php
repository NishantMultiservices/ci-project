<?php
require_once '../config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_slug = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT n.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name
        FROM study_notes n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN users u ON n.user_id = u.id
        WHERE n.is_public = 1";

if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (n.title LIKE '%$s%' OR n.description LIKE '%$s%')";
}
if ($category_slug) {
    $s = $conn->real_escape_string($category_slug);
    $sql .= " AND c.slug = '$s'";
}
$sql .= " ORDER BY n.is_featured DESC, n.created_at DESC";

$notes = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories WHERE type = 'note' ORDER BY name");

$page_title = 'Study Notes';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Study Smarter</span>
            <h2>Study Notes</h2>
            <p>Browse and download organized study notes by subject.</p>
        </div>

        <div class="search-bar">
            <form method="GET" action="" style="display:flex;gap:12px;width:100%;">
                <input type="text" name="search" class="form-input" placeholder="Search notes by title or description..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="filter-tabs" id="noteFilters">
            <a href="index.php" class="filter-tab <?php echo !$category_slug ? 'active' : ''; ?>">All Notes</a>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="?category=<?php echo $cat['slug']; ?>" class="filter-tab <?php echo $category_slug === $cat['slug'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <?php if ($notes->num_rows === 0): ?>
            <div class="empty-state">
                <div class="icon">📖</div>
                <h3>No notes found</h3>
                <p>Try a different search or browse all categories.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php while ($note = $notes->fetch_assoc()): ?>
                    <div class="card" style="cursor:pointer;" onclick="window.location='details.php?id=<?php echo $note['id']; ?>'">
                        <div class="card-image" style="background:linear-gradient(135deg,var(--success-light),#D1FAE5);color:var(--success);">
                            <?php
                                $icons = ['📘', '📗', '📕', '📙', '📔'];
                                echo $icons[array_rand($icons)];
                            ?>
                        </div>
                        <div class="card-body">
                            <span class="card-badge note"><?php echo htmlspecialchars($note['category_name'] ?? 'General'); ?></span>
                            <?php if ($note['is_featured']): ?>
                                <span class="card-badge badge-warning">Featured</span>
                            <?php endif; ?>
                            <span class="card-badge" style="background:<?php echo $note['is_free'] ? 'var(--success-light)' : 'var(--warning-light)'; ?>;color:<?php echo $note['is_free'] ? 'var(--success)' : 'var(--warning)'; ?>;"><?php echo $note['is_free'] ? 'Free' : '₹'.number_format($note['price'],0); ?></span>
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <p><?php echo htmlspecialchars($note['description']); ?></p>
                            <div class="card-meta">
                                <span>📄 <?php echo strtoupper($note['file_type']); ?></span>
                                <span>📥 <?php echo $note['download_count']; ?> downloads</span>
                                <span>📅 <?php echo date('d M Y', strtotime($note['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="card-footer" onclick="event.stopPropagation();">
                            <a href="details.php?id=<?php echo $note['id']; ?>" class="btn <?php echo $note['is_free'] ? 'btn-success' : 'btn-outline'; ?> btn-sm">View Details</a>
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-sm" style="background:var(--gray-100);" onclick="toggleSave(this,'note',<?php echo $note['id']; ?>)">
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

<?php include '../includes/footer.php'; ?>

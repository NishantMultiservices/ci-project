<?php
require_once '../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM job_listings WHERE is_active = 1";
$countSql = "SELECT COUNT(*) as c FROM job_listings WHERE is_active = 1";

if ($type) {
    $t = $conn->real_escape_string($type);
    $sql .= " AND application_type = '$t'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (title LIKE '%$s%' OR company LIKE '%$s%' OR advertise_no LIKE '%$s%')";
}

$totalJobs = $conn->query($countSql)->fetch_assoc()['c'];
$sql .= " ORDER BY is_featured DESC, created_at DESC";
$jobs = $conn->query($sql);

$page_title = 'Job Notifications';
include '../includes/header.php';
?>

<style>
.job-list-item { display:flex; align-items:center; gap:16px; padding:16px 20px; background:var(--white); border-radius:var(--radius); border:1px solid var(--gray-200); margin-bottom:10px; transition:var(--transition); cursor:pointer; }
.job-list-item:hover { border-color:var(--primary); box-shadow:var(--shadow-sm); }
.job-list-item .jl-badges { display:flex; gap:6px; flex-wrap:wrap; min-width:140px; }
.job-list-item .jl-title { flex:1; }
.job-list-item .jl-title h3 { font-size:1rem; font-weight:600; color:var(--dark); margin:0; }
.job-list-item .jl-title h3:hover { color:var(--primary); }
.job-list-item .jl-title .jl-org { font-size:0.85rem; color:var(--gray-500); margin-top:2px; }
.job-list-item .jl-meta { text-align:right; min-width:140px; }
.job-list-item .jl-meta .vacancy { font-size:0.9rem; font-weight:700; color:var(--primary); }
.job-list-item .jl-meta .deadline { font-size:0.8rem; color:var(--gray-500); }
.job-list-item .jl-meta .deadline.urgent { color:var(--danger); font-weight:600; }
.job-list-item .jl-arrow { font-size:1.2rem; color:var(--gray-300); }
.job-list-item:hover .jl-arrow { color:var(--primary); }
</style>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="section-header" style="text-align:left;">
            <span class="subtitle">Career Opportunities</span>
            <h2>Job Notifications</h2>
            <p><?php echo $totalJobs; ?> active job listings. Don't miss your dream opportunity.</p>
        </div>

        <div class="search-bar">
            <form method="GET" action="" style="display:flex;gap:12px;width:100%;">
                <input type="text" name="search" class="form-input" placeholder="Search by title, organization, or advertisement no..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?><a href="index.php" class="btn btn-secondary">Clear</a><?php endif; ?>
            </form>
        </div>

        <div class="filter-tabs" id="jobFilters">
            <a href="index.php" class="filter-tab <?php echo !$type ? 'active' : ''; ?>">All</a>
            <a href="?type=online" class="filter-tab <?php echo $type === 'online' ? 'active' : ''; ?>">Online</a>
            <a href="?type=offline" class="filter-tab <?php echo $type === 'offline' ? 'active' : ''; ?>">Offline</a>
            <a href="?type=both" class="filter-tab <?php echo $type === 'both' ? 'active' : ''; ?>">Both</a>
        </div>

        <?php if ($jobs->num_rows === 0): ?>
            <div class="empty-state"><div class="icon">💼</div><h3>No job listings found</h3><p>Try a different search or check back later.</p></div>
        <?php else: while ($job = $jobs->fetch_assoc()):
            $daysLeft = $job['application_deadline'] ? ceil((strtotime($job['application_deadline']) - time()) / 86400) : 999;
            $isUrgent = $daysLeft <= 7 && $daysLeft > 0;
            $isExpired = $daysLeft <= 0;

            $totalVac = $conn->query("SELECT SUM(vacancy) as tv FROM job_posts WHERE job_id = {$job['id']}")->fetch_assoc()['tv'];
            $displayVac = $totalVac ?: $job['total_vacancy'];
        ?>
            <a href="details.php?id=<?php echo $job['id']; ?>" class="job-list-item" style="<?php echo $job['is_featured'] ? 'border-left:4px solid var(--primary);' : ''; ?>">
                <div class="jl-badges">
                    <?php if ($job['is_featured']): ?><span class="card-badge badge-info">Featured</span><?php endif; ?>
                    <?php if ($isExpired): ?><span class="card-badge badge-danger">Expired</span>
                    <?php elseif ($isUrgent): ?><span class="card-badge badge-warning">🔥 <?php echo $daysLeft; ?>d left</span><?php endif; ?>
                </div>
                <div class="jl-title">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <div class="jl-org"><?php echo htmlspecialchars($job['company']); ?></div>
                </div>
                <div class="jl-meta">
                    <div class="vacancy"><?php echo number_format($displayVac); ?> Vacancies</div>
                    <?php if ($job['application_deadline']): ?>
                        <div class="deadline <?php echo $isUrgent && !$isExpired ? 'urgent' : ''; ?>">
                            <?php echo $isExpired ? 'Expired' : 'Deadline: ' . date('d M Y', strtotime($job['application_deadline'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="jl-arrow">›</div>
            </a>
        <?php endwhile; endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

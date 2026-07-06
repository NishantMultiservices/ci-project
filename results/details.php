<?php
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$n = $conn->query("SELECT * FROM exam_notifications WHERE id = $id AND is_active = 1")->fetch_assoc();

if (!$n) {
    $page_title = 'Not Found';
    include '../includes/header.php';
    echo '<section class="section" style="padding-top:60px;text-align:center;"><div class="container"><h2>Notification Not Found</h2><p style="margin:16px 0;">This notification does not exist or has been removed.</p><a href="index.php" class="btn btn-primary">← Back to Results & Notifications</a></div></section>';
    include '../includes/footer.php'; exit;
}

$page_title = htmlspecialchars($n['title']) . ' - Notification';
include '../includes/header.php';

$posts = $conn->query("SELECT * FROM notification_posts WHERE notification_id = {$n['id']} ORDER BY id");
$typeColors = [
    'result' => ['bg'=>'#ecfdf5','text'=>'#10b981','icon'=>'📊'],
    'admit_card' => ['bg'=>'#fef3c7','text'=>'#d97706','icon'=>'🎫'],
    'exam_date' => ['bg'=>'#dbeafe','text'=>'#3b82f6','icon'=>'📅'],
    'syllabus' => ['bg'=>'#eef2ff','text'=>'#4f46e5','icon'=>'📚'],
    'other' => ['bg'=>'#f1f5f9','text'=>'#64748b','icon'=>'📢']
];
$tc = $typeColors[$n['notification_type']] ?? $typeColors['other'];
?>

<style>
.dt-bar{position:sticky;top:0;z-index:99;background:rgba(255,255,255,.96);backdrop-filter:blur(14px);border-bottom:1px solid rgba(0,0,0,.06);padding:10px 0;margin-bottom:28px}
.dt-bar-inner{display:flex;align-items:center;justify-content:space-between}
.dt-bar-back{display:flex;align-items:center;gap:6px;font-size:.84rem;color:#889096;text-decoration:none}
.dt-bar-back:hover{color:var(--primary)}
.dt-card{background:white;border:1px solid #eef2f6;border-radius:12px;margin-bottom:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)}
.dt-card-hd{display:flex;align-items:center;gap:10px;padding:15px 22px;border-bottom:1px solid #f1f4f9}
.dt-card-hd .hd-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0}
.dt-card-hd h3{font-size:.88rem;font-weight:700;color:#0f172a;margin:0;flex:1}
.dt-card-bd{padding:18px 22px}
.dt-card-bd p{color:#475569;line-height:1.7;margin:0}
.dt-tbl{width:100%;border-collapse:collapse}
.dt-tbl thead th{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;padding:10px 18px;background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left}
.dt-tbl tbody td{padding:10px 18px;font-size:.86rem;border-bottom:1px solid #f1f5f9;color:#334155}
.dt-tbl tbody tr:last-child td{border:none}
.dt-tbl tbody tr:hover td{background:#f8faff}

.dt-hero{background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:18px;padding:32px;margin-bottom:28px;position:relative;overflow:hidden}
.dt-hero::before{content:'';position:absolute;top:-50%;right:-8%;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,.1) 0%,transparent 60%)}
.dt-hero-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 14px;border-radius:6px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:12px;position:relative;z-index:1}
.dt-hero h1{font-size:clamp(1.3rem,3vw,1.7rem);font-weight:800;color:white;margin:0 0 5px;position:relative;z-index:1}
.dt-hero-org{font-size:.95rem;color:rgba(255,255,255,.5);position:relative;z-index:1}
.dt-hero-meta{display:flex;gap:24px;flex-wrap:wrap;margin-top:16px;position:relative;z-index:1}
.dt-hero-meta div{font-size:.85rem;color:rgba(255,255,255,.65)}

@media(max-width:640px){
.dt-hero{padding:22px 18px}
.dt-card-hd{padding:13px 16px}
.dt-card-bd{padding:14px 16px}
.dt-tbl thead th,.dt-tbl tbody td{padding:8px 12px}
}
</style>

<div class="dt-bar">
    <div class="container">
        <div class="dt-bar-inner">
            <a href="index.php" class="dt-bar-back">← Back to Results & Notifications</a>
            <div class="dt-bar-actions" style="display:flex;gap:6px;">
                <?php if (isLoggedIn()): ?>
                <button onclick="toggleSave(this,'exam_notification',<?php echo $n['id']; ?>)" style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #eef0f2;background:white;cursor:pointer;font-size:1rem;color:#889096;">☆</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:920px;">

        <div class="dt-hero">
            <div class="dt-hero-tag" style="background:<?php echo $tc['bg']; ?>;color:<?php echo $tc['text']; ?>;"><?php echo $tc['icon']; ?> <?php echo ucfirst(str_replace('_',' ',$n['notification_type'])); ?></div>
            <h1><?php echo htmlspecialchars($n['title']); ?></h1>
            <?php if ($n['organization']): ?><div class="dt-hero-org">🏛 <?php echo htmlspecialchars($n['organization']); ?></div><?php endif; ?>
            <div class="dt-hero-meta">
                <?php if ($n['exam_name']): ?><div>📋 <?php echo htmlspecialchars($n['exam_name']); ?></div><?php endif; ?>
                <?php if ($n['result_date']): ?><div>📅 Result: <?php echo date('d M Y', strtotime($n['result_date'])); ?></div><?php endif; ?>
                <div>🕐 Posted <?php echo timeAgo($n['created_at']); ?></div>
            </div>
        </div>

        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#eef2ff;color:#4f46e5;">📄</div><h3>Details</h3></div>
            <div class="dt-card-bd"><p><?php echo nl2br(htmlspecialchars($n['description'])); ?></p></div>
        </div>

        <?php if ($posts && $posts->num_rows > 0): ?>
        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#f3e8ff;color:#9333ea;">📋</div><h3>Posts / Categories</h3></div>
            <div style="padding:0;">
                <table class="dt-tbl">
                    <thead><tr><th>Post / Category</th><th>Result Date</th><th>Download</th></tr></thead>
                    <tbody><?php while($p=$posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($p['post_name']); ?></strong></td>
                            <td><?php echo $p['result_date'] ? date('d M Y', strtotime($p['result_date'])) : '-'; ?></td>
                            <td><?php if ($p['download_url']): ?><a href="<?php echo htmlspecialchars($p['download_url']); ?>" target="_blank" class="btn btn-sm btn-primary" style="font-size:.78rem;padding:4px 12px;">Download</a><?php else: ?><span style="color:#94a3b8;">Soon</span><?php endif; ?></td>
                        </tr>
                    <?php endwhile; ?></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#ccfbf1;color:#0d9488;">🔗</div><h3>Download / View</h3></div>
            <div class="dt-card-bd">
                <?php if ($n['download_url']): ?>
                    <a href="<?php echo htmlspecialchars($n['download_url']); ?>" target="_blank" class="btn btn-primary">📥 Download / View</a>
                <?php else: ?>
                    <p style="color:#94a3b8;">No download available yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>

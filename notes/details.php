<?php
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$note = $conn->query("SELECT n.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name
                       FROM study_notes n
                       LEFT JOIN categories c ON n.category_id = c.id
                       LEFT JOIN users u ON n.user_id = u.id
                       WHERE n.id = $id AND n.is_public = 1")->fetch_assoc();

if (!$note) {
    $page_title = 'Note Not Found';
    include '../includes/header.php';
    echo '<section class="section" style="padding-top:60px;text-align:center;"><div class="container"><h2>Note Not Found</h2><p style="margin:16px 0;">This study note does not exist or has been removed.</p><a href="index.php" class="btn btn-primary">← Back to Notes</a></div></section>';
    include '../includes/footer.php'; exit;
}

$page_title = htmlspecialchars($note['title']) . ' - Study Note';
include '../includes/header.php';
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

.dt-hero{background:linear-gradient(135deg,#064e3b,#065f46,#047857);border-radius:18px;padding:32px;margin-bottom:28px;position:relative;overflow:hidden}
.dt-hero::before{content:'';position:absolute;top:-50%;right:-8%;width:400px;height:400px;background:radial-gradient(circle,rgba(16,185,129,.1) 0%,transparent 60%)}
.dt-hero h1{font-size:clamp(1.3rem,3vw,1.7rem);font-weight:800;color:white;margin:0 0 5px;position:relative;z-index:1}
.dt-hero-org{font-size:.95rem;color:rgba(255,255,255,.5);position:relative;z-index:1}
.dt-hero-meta{display:flex;gap:24px;flex-wrap:wrap;margin-top:16px;position:relative;z-index:1}
.dt-hero-meta div{font-size:.85rem;color:rgba(255,255,255,.65)}

.dt-hl{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px}
.dt-hli{background:white;border:1px solid #eef2f6;border-radius:10px;padding:16px 14px;text-align:center;transition:all .25s}
.dt-hli:hover{border-color:#cbd5e1;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.04)}
.dt-hli .hli-icon{font-size:1.2rem;margin-bottom:5px}
.dt-hli .hli-lbl{font-size:.64rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px}
.dt-hli .hli-val{font-size:.88rem;font-weight:700;color:#0f172a;margin-top:2px}

.dt-actions{display:flex;flex-wrap:wrap;gap:10px}
.dt-actions a,.dt-actions button{display:inline-flex;align-items:center;gap:6px;padding:11px 24px;border-radius:8px;font-size:.88rem;font-weight:600;text-decoration:none;transition:all .25s;cursor:pointer;border:none}
.dt-actions .dl{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white}
.dt-actions .dl:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(74,108,247,.35)}
.dt-actions .sv{border:1.5px solid var(--primary);color:var(--primary);background:white}
.dt-actions .sv:hover{background:var(--primary);color:white;transform:translateY(-1px)}

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
            <a href="index.php" class="dt-bar-back">← Back to Notes</a>
            <div class="dt-bar-actions" style="display:flex;gap:6px;">
                <?php if (isLoggedIn()): ?>
                <button onclick="toggleSave(this,'note',<?php echo $note['id']; ?>)" style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #eef0f2;background:white;cursor:pointer;font-size:1rem;color:#889096;">☆</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:920px;">

        <div class="dt-hero">
            <h1><?php echo htmlspecialchars($note['title']); ?></h1>
            <div class="dt-hero-org">📂 <?php echo htmlspecialchars($note['category_name'] ?? 'General'); ?></div>
            <div class="dt-hero-meta">
                <div>📄 <?php echo strtoupper($note['file_type']); ?></div>
                <div>📥 <?php echo $note['download_count']; ?> downloads</div>
                <div>📅 <?php echo date('d M Y', strtotime($note['created_at'])); ?></div>
                <?php if ($note['author_name']): ?><div>✍️ <?php echo htmlspecialchars($note['author_name']); ?></div><?php endif; ?>
            </div>
        </div>

        <?php if ($note['description']): ?>
        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#eef2ff;color:#4f46e5;">📄</div><h3>Description</h3></div>
            <div class="dt-card-bd"><p><?php echo nl2br(htmlspecialchars($note['description'])); ?></p></div>
        </div>
        <?php endif; ?>

        <div style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;">📊</div>
                <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin:0;">Details</h3>
            </div>
            <div class="dt-hl">
                <div class="dt-hli">
                    <div class="hli-icon">📂</div>
                    <div class="hli-lbl">Category</div>
                    <div class="hli-val"><?php echo htmlspecialchars($note['category_name'] ?? 'General'); ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">📄</div>
                    <div class="hli-lbl">File Type</div>
                    <div class="hli-val"><?php echo strtoupper($note['file_type']); ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">📥</div>
                    <div class="hli-lbl">Downloads</div>
                    <div class="hli-val"><?php echo $note['download_count']; ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">💰</div>
                    <div class="hli-lbl">Price</div>
                    <div class="hli-val"><?php echo $note['is_free'] ? 'Free' : '₹'.number_format($note['price'], 0); ?></div>
                </div>
            </div>
        </div>

        <?php
        $already_purchased = isLoggedIn() && $conn->query("SELECT id FROM purchases WHERE user_id = {$_SESSION['user_id']} AND item_type = 'note' AND item_id = {$note['id']} AND status = 'completed'")->num_rows > 0;
        $can_access = $note['is_free'] || $already_purchased;
        ?>
        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#ccfbf1;color:#0d9488;">🔗</div><h3>Get Access</h3></div>
            <div class="dt-card-bd">
                <div class="dt-actions">
                    <?php if ($can_access && $note['file_path']): ?>
                        <a href="<?php echo htmlspecialchars($note['file_path']); ?>" target="_blank" class="dl">📥 Download <?php echo strtoupper($note['file_type']); ?></a>
                    <?php elseif (!$can_access): ?>
                        <a href="../payment/checkout.php?type=note&id=<?php echo $note['id']; ?>" class="dl">🛒 Buy Now — ₹<?php echo number_format($note['price'], 0); ?></a>
                    <?php else: ?>
                        <p style="color:#94a3b8;">File not available for download yet.</p>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <button onclick="toggleSave(this,'note',<?php echo $note['id']; ?>)" class="sv">☆ Save</button>
                    <?php endif; ?>
                    <?php if (!isLoggedIn()): ?>
                        <a href="../auth/login.php" class="sv" style="border-color:#94a3b8;color:#64748b;">Login to Buy</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dt-ft" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:12px 22px;background:#fafbfc;border-top:1px solid #eef2f6;font-size:.82rem;color:#94a3b8;">
                <span>Uploaded <?php echo timeAgo($note['created_at']); ?></span>
                <?php if ($note['updated_at'] != $note['created_at']): ?>
                    <span>Updated <?php echo timeAgo($note['updated_at']); ?></span>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>

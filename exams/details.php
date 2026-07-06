<?php
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$exam = $conn->query("SELECT e.*, c.name as category_name, c.slug as category_slug,
                       (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) as question_count
                       FROM exams e
                       LEFT JOIN categories c ON e.category_id = c.id
                       WHERE e.id = $id AND e.is_active = 1")->fetch_assoc();

if (!$exam) {
    $page_title = 'Exam Not Found';
    include '../includes/header.php';
    echo '<section class="section" style="padding-top:60px;text-align:center;"><div class="container"><h2>Exam Not Found</h2><p style="margin:16px 0;">This exam does not exist or has been removed.</p><a href="index.php" class="btn btn-primary">← Back to Exams</a></div></section>';
    include '../includes/footer.php'; exit;
}

$page_title = htmlspecialchars($exam['title']) . ' - Mock Exam';
include '../includes/header.php';

$difficultyColor = match($exam['difficulty']) {
    'beginner' => '#10b981',
    'intermediate' => '#f59e0b',
    'advanced' => '#ef4444',
    default => '#6366f1'
};
$difficultyBg = match($exam['difficulty']) {
    'beginner' => '#d1fae5',
    'intermediate' => '#fef3c7',
    'advanced' => '#fee2e2',
    default => '#eef2ff'
};
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

.dt-hero{background:linear-gradient(135deg,#1e1b4b,#312e81,#3730a3);border-radius:18px;padding:32px;margin-bottom:28px;position:relative;overflow:hidden}
.dt-hero::before{content:'';position:absolute;top:-50%;right:-8%;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,.12) 0%,transparent 60%)}
.dt-hero::after{content:'';position:absolute;bottom:-30%;left:-5%;width:280px;height:280px;background:radial-gradient(circle,rgba(139,92,246,.08) 0%,transparent 60%)}
.dt-hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:48px 48px}
.dt-hero-content{position:relative;z-index:1}
.dt-hero-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.dt-hero-tag{padding:4px 12px;border-radius:6px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.8)}
.dt-hero-tag.feat{background:linear-gradient(135deg,#f59e0b,#d97706);color:#0f172a}
.dt-hero-tag.diff{background:<?php echo $difficultyBg; ?>;color:<?php echo $difficultyColor; ?>}
.dt-hero h1{font-size:clamp(1.35rem,3.2vw,1.75rem);font-weight:800;color:white;margin:0 0 5px;line-height:1.25}
.dt-hero-org{font-size:.95rem;color:rgba(255,255,255,.5);margin-bottom:14px}
.dt-hero-stats{display:flex;gap:20px;flex-wrap:wrap}
.dt-hero-stat{display:flex;flex-direction:column;align-items:center;padding:10px 18px;background:rgba(255,255,255,.06);border-radius:10px;border:1px solid rgba(255,255,255,.08);min-width:80px}
.dt-hero-stat .hs-num{font-size:1.3rem;font-weight:800;color:white;line-height:1}
.dt-hero-stat .hs-label{font-size:.65rem;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.4px;margin-top:4px}

.dt-hl{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px}
.dt-hli{background:white;border:1px solid #eef2f6;border-radius:10px;padding:16px 14px;text-align:center;transition:all .25s}
.dt-hli:hover{border-color:#cbd5e1;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.04)}
.dt-hli .hli-icon{font-size:1.2rem;margin-bottom:5px}
.dt-hli .hli-lbl{font-size:.64rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px}
.dt-hli .hli-val{font-size:.88rem;font-weight:700;color:#0f172a;margin-top:2px}

.dt-actions{display:flex;flex-wrap:wrap;gap:10px}
.dt-actions a,.dt-actions button{display:inline-flex;align-items:center;gap:6px;padding:11px 24px;border-radius:8px;font-size:.88rem;font-weight:600;text-decoration:none;transition:all .25s;cursor:pointer;border:none}
.dt-actions .start{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white}
.dt-actions .start:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(74,108,247,.35)}
.dt-actions .sv{border:1.5px solid var(--primary);color:var(--primary);background:white}
.dt-actions .sv:hover{background:var(--primary);color:white;transform:translateY(-1px)}

@media(max-width:640px){
.dt-hero{padding:22px 18px}
.dt-card-hd{padding:13px 16px}
.dt-card-bd{padding:14px 16px}
}
</style>

<div class="dt-bar">
    <div class="container">
        <div class="dt-bar-inner">
            <a href="index.php" class="dt-bar-back">← Back to Exams</a>
            <div class="dt-bar-actions" style="display:flex;gap:6px;">
                <?php if (isLoggedIn()): ?>
                <button onclick="toggleSave(this,'exam',<?php echo $exam['id']; ?>)" style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #eef0f2;background:white;cursor:pointer;font-size:1rem;color:#889096;">☆</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:920px;">

        <div class="dt-hero">
            <div class="dt-hero-grid"></div>
            <div class="dt-hero-content">
                <div class="dt-hero-tags">
                    <?php if ($exam['is_featured'] ?? false): ?><span class="dt-hero-tag feat">★ Featured</span><?php endif; ?>
                    <span class="dt-hero-tag diff"><?php echo ucfirst($exam['difficulty']); ?></span>
                    <span class="dt-hero-tag" style="background:<?php echo $exam['is_free'] ? '#d1fae5' : '#fef3c7'; ?>;color:<?php echo $exam['is_free'] ? '#10b981' : '#d97706'; ?>;"><?php echo $exam['is_free'] ? 'Free' : '₹'.number_format($exam['price'], 0); ?></span>
                </div>
                <h1><?php echo htmlspecialchars($exam['title']); ?></h1>
                <div class="dt-hero-org">📂 <?php echo htmlspecialchars($exam['category_name'] ?? 'General'); ?></div>
                <div class="dt-hero-stats">
                    <div class="dt-hero-stat">
                        <div class="hs-num">⏱ <?php echo $exam['duration_minutes']; ?></div>
                        <div class="hs-label">Minutes</div>
                    </div>
                    <div class="dt-hero-stat">
                        <div class="hs-num">📋 <?php echo $exam['question_count'] ?: $exam['total_questions']; ?></div>
                        <div class="hs-label">Questions</div>
                    </div>
                    <div class="dt-hero-stat">
                        <div class="hs-num">🎯 <?php echo $exam['passing_score']; ?>%</div>
                        <div class="hs-label">Passing</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($exam['description']): ?>
        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#eef2ff;color:#4f46e5;">📄</div><h3>Description</h3></div>
            <div class="dt-card-bd"><p><?php echo nl2br(htmlspecialchars($exam['description'])); ?></p></div>
        </div>
        <?php endif; ?>

        <div style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;">📊</div>
                <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin:0;">Exam Details</h3>
            </div>
            <div class="dt-hl">
                <div class="dt-hli">
                    <div class="hli-icon">📂</div>
                    <div class="hli-lbl">Category</div>
                    <div class="hli-val"><?php echo htmlspecialchars($exam['category_name'] ?? 'General'); ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">📊</div>
                    <div class="hli-lbl">Difficulty</div>
                    <div class="hli-val" style="color:<?php echo $difficultyColor; ?>;"><?php echo ucfirst($exam['difficulty']); ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">⏱</div>
                    <div class="hli-lbl">Duration</div>
                    <div class="hli-val"><?php echo $exam['duration_minutes']; ?> min</div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">📋</div>
                    <div class="hli-lbl">Questions</div>
                    <div class="hli-val"><?php echo $exam['question_count'] ?: $exam['total_questions']; ?></div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">🎯</div>
                    <div class="hli-lbl">Pass Score</div>
                    <div class="hli-val"><?php echo $exam['passing_score']; ?>%</div>
                </div>
                <div class="dt-hli">
                    <div class="hli-icon">💰</div>
                    <div class="hli-lbl">Price</div>
                    <div class="hli-val"><?php echo $exam['is_free'] ? 'Free' : '₹'.number_format($exam['price'], 0); ?></div>
                </div>
            </div>
        </div>

        <?php
        $already_purchased = isLoggedIn() && $conn->query("SELECT id FROM purchases WHERE user_id = {$_SESSION['user_id']} AND item_type = 'exam' AND item_id = {$exam['id']} AND status = 'completed'")->num_rows > 0;
        $can_access = $exam['is_free'] || $already_purchased;
        ?>
        <div class="dt-card">
            <div class="dt-card-hd"><div class="hd-icon" style="background:#ccfbf1;color:#0d9488;">🚀</div><h3>Get Access</h3></div>
            <div class="dt-card-bd">
                <div class="dt-actions">
                    <?php if ($can_access): ?>
                        <a href="take_exam.php?id=<?php echo $exam['id']; ?>" class="start">📝 <?php echo $exam['is_free'] ? 'Start Free Exam' : 'Access Exam'; ?></a>
                    <?php else: ?>
                        <a href="../payment/checkout.php?type=exam&id=<?php echo $exam['id']; ?>" class="start">🛒 Buy Now — ₹<?php echo number_format($exam['price'], 0); ?></a>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <button onclick="toggleSave(this,'exam',<?php echo $exam['id']; ?>)" class="sv">☆ Save Exam</button>
                    <?php endif; ?>
                    <?php if (!isLoggedIn()): ?>
                        <a href="../auth/login.php" class="sv" style="border-color:#94a3b8;color:#64748b;">Login to Buy</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>

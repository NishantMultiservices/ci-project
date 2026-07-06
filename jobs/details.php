<?php
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$job = $conn->query("SELECT * FROM job_listings WHERE id = $id AND is_active = 1")->fetch_assoc();

if (!$job) {
    $page_title = 'Job Not Found';
    include '../includes/header.php';
    echo '<section class="section" style="padding-top:60px;text-align:center;"><div class="container"><h2>Job Not Found</h2><p style="margin:16px 0;">This job listing does not exist or has been removed.</p><a href="index.php" class="btn btn-primary">← Back to Jobs</a></div></section>';
    include '../includes/footer.php';
    exit;
}

$page_title = htmlspecialchars($job['title']) . ' - Job Details';
include '../includes/header.php';

$daysLeft = $job['application_deadline'] ? ceil((strtotime($job['application_deadline']) - time()) / 86400) : 999;
$isUrgent = $daysLeft <= 7 && $daysLeft > 0;
$isExpired = $daysLeft <= 0;

$posts = $conn->query("SELECT * FROM job_posts WHERE job_id = {$job['id']} ORDER BY post_no");
$quals = $conn->query("SELECT * FROM job_qualifications WHERE job_id = {$job['id']} ORDER BY post_no");
$fees = $conn->query("SELECT * FROM job_fees WHERE job_id = {$job['id']} ORDER BY id");
$dates = json_decode($job['important_dates'], true);
$date_labels = [
    'application_start' => 'Application Start',
    'application_end' => 'Last Date',
    'exam_date_prelims' => 'Exam Date (Prelims)',
    'exam_date_mains' => 'Exam Date (Mains)',
    'fee_payment_last' => 'Fee Payment Last Date',
    'result_date' => 'Result Date',
    'interview' => 'Interview Date'
];

$totalVacFromPosts = $posts ? $conn->query("SELECT SUM(vacancy) as tv FROM job_posts WHERE job_id = {$job['id']}")->fetch_assoc()['tv'] : 0;
$displayVac = $totalVacFromPosts ?: $job['total_vacancy'];
$hasDeadline = $job['application_deadline'] && !$isExpired;
?>
<?php $deadlineTs = $job['application_deadline'] ? strtotime($job['application_deadline']) : 0; ?>

<style>
*{box-sizing:border-box}

@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.su{animation:slideUp .45s ease both}
.s1{animation-delay:.06s}
.s2{animation-delay:.12s}
.s3{animation-delay:.18s}
.s4{animation-delay:.24s}
.s5{animation-delay:.30s}

/* ── Top Bar ── */
.jd-bar{position:sticky;top:0;z-index:99;background:rgba(255,255,255,.96);backdrop-filter:blur(14px);border-bottom:1px solid rgba(0,0,0,.06);padding:10px 0;margin-bottom:28px}
.jd-bar-inner{display:flex;align-items:center;justify-content:space-between}
.jd-bar-back{display:flex;align-items:center;gap:6px;font-size:.84rem;color:#889096;text-decoration:none}
.jd-bar-back:hover{color:var(--primary)}
.jd-bar-actions{display:flex;gap:6px}
.jd-bar-actions button,.jd-bar-actions a{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #eef0f2;background:white;cursor:pointer;font-size:1rem;color:#889096;text-decoration:none;transition:all .2s}
.jd-bar-actions button:hover,.jd-bar-actions a:hover{border-color:var(--primary);color:var(--primary);background:#f0f3ff}

/* ── Hero ── */
.jd-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#1a1a3e 100%);border-radius:18px;padding:32px;margin-bottom:28px;position:relative;overflow:hidden}
.jd-hero::before{content:'';position:absolute;top:-50%;right:-8%;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,.12) 0%,transparent 60%)}
.jd-hero::after{content:'';position:absolute;bottom:-30%;left:-5%;width:280px;height:280px;background:radial-gradient(circle,rgba(168,85,247,.08) 0%,transparent 60%)}
.jd-hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:48px 48px}
.jd-hero-content{position:relative;z-index:1}
.jd-hero-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.jd-hero-tag{padding:4px 12px;border-radius:6px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.8)}
.jd-hero-tag.feat{background:linear-gradient(135deg,#f59e0b,#d97706);color:#0f172a}
.jd-hero-tag.danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:white}
.jd-hero-tag.urgent{background:linear-gradient(135deg,#f59e0b,#d97706);color:#0f172a}
.jd-hero-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.jd-hero-text{flex:1;min-width:200px}
.jd-hero h1{font-size:clamp(1.35rem,3.2vw,1.75rem);font-weight:800;color:white;margin:0 0 5px;line-height:1.25}
.jd-hero-org{font-size:.95rem;color:rgba(255,255,255,.5);margin-bottom:0}
.jd-hero-badge{display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(99,102,241,.25),rgba(168,85,247,.15));border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px 28px;min-width:130px;text-align:center;flex-shrink:0}
.jd-hero-badge .hb-num{font-size:1.8rem;font-weight:900;color:white;line-height:1;letter-spacing:-.5px}
.jd-hero-badge .hb-label{font-size:.65rem;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-top:4px}

/* ── Countdown ── */
.jd-cd{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,#fef9c3,#fde68a,#fef3c7);border:1px solid #fcd34d;border-radius:12px;padding:14px 22px;margin-bottom:28px;flex-wrap:wrap}
.jd-cd .cicon{font-size:1.5rem}
.jd-cd .cbody{flex:1;min-width:160px}
.jd-cd .clabel{font-size:.78rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.3px}
.jd-cd .ctimer{font-size:1.3rem;font-weight:800;color:#78350f;font-variant-numeric:tabular-nums;letter-spacing:1px;margin-top:1px}
.jd-cd .ctimer .csep{animation:pulse 1s infinite}
.jd-cd .cbtn{flex-shrink:0}

/* ── Cards ── */
.jd-card{background:white;border-radius:12px;margin-bottom:18px;overflow:hidden;border:1px solid #eef2f6;box-shadow:0 1px 3px rgba(0,0,0,.03);transition:box-shadow .25s,transform .25s}
.jd-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.06);transform:translateY(-1px)}
.jd-card-hd{display:flex;align-items:center;gap:10px;padding:15px 22px;border-bottom:1px solid #f1f4f9}
.jd-card-hd .hd-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0}
.jd-card-hd .hd-desc{background:#eef2ff;color:#4f46e5}
.jd-card-hd .hd-post{background:#f3e8ff;color:#9333ea}
.jd-card-hd .hd-edu{background:#ecfdf5;color:#10b981}
.jd-card-hd .hd-fee{background:#fef3c7;color:#d97706}
.jd-card-hd .hd-date{background:#fef2f2;color:#dc2626}
.jd-card-hd .hd-link{background:#ccfbf1;color:#0d9488}
.jd-card-hd h3{font-size:.88rem;font-weight:700;color:#0f172a;margin:0;flex:1}
.jd-card-bd{padding:18px 22px}
.jd-card-bd p{color:#475569;line-height:1.7;margin:0}

/* ── Meta tags ── */
.jd-mtags{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.jd-mtag{display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;border-radius:6px;padding:5px 12px;font-size:.78rem;font-weight:600;color:#475569}
.jd-mtag span{color:var(--primary)}
.jd-mtag .micon{font-size:.85rem}

/* ── Tables ── */
.jd-tbl{width:100%;border-collapse:collapse}
.jd-tbl thead th{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;padding:10px 18px;background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left}
.jd-tbl tbody td{padding:10px 18px;font-size:.86rem;border-bottom:1px solid #f1f5f9;color:#334155}
.jd-tbl tbody tr:last-child td{border:none}
.jd-tbl tbody tr{transition:background .15s}
.jd-tbl tbody tr:hover td{background:#f8faff}

/* ── Highlights ── */
.jd-hl{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px}
.jd-hli{background:white;border:1px solid #eef2f6;border-radius:10px;padding:16px 14px;text-align:center;transition:all .25s}
.jd-hli:hover{border-color:#cbd5e1;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.04)}
.jd-hli .hli-icon{font-size:1.2rem;margin-bottom:5px}
.jd-hli .hli-lbl{font-size:.64rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px}
.jd-hli .hli-val{font-size:.88rem;font-weight:700;color:#0f172a;margin-top:2px}
.jd-hli.danger{border-color:#fecaca;background:#fef2f2}
.jd-hli.danger .hli-lbl{color:#dc2626}
.jd-hli.danger .hli-val{color:#dc2626}
.jd-hli.muted{opacity:.5}

/* ── Dates ── */
.jd-dates{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:10px}
.jd-dti{background:#f8fafc;border-radius:8px;padding:12px 14px;border-left:3px solid var(--primary);transition:all .2s}
.jd-dti:hover{background:#eef2ff}
.jd-dti .ddlbl{font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.2px}
.jd-dti .ddval{font-size:.88rem;font-weight:700;color:#0f172a;margin-top:2px}
.jd-dti.danger{border-left-color:#dc2626;background:#fef2f2}
.jd-dti.danger:hover{background:#fee2e2}
.jd-dti.danger .ddlbl{color:#dc2626}
.jd-dti.danger .ddval{color:#dc2626}

/* ── Links ── */
.jd-links{display:flex;flex-wrap:wrap;gap:8px}
.jd-links a{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:8px;font-size:.83rem;font-weight:600;text-decoration:none;transition:all .25s}
.jd-links .lp{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white}
.jd-links .lp:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(74,108,247,.35)}
.jd-links .lo{border:1.5px solid var(--primary);color:var(--primary);background:white}
.jd-links .lo:hover{background:var(--primary);color:white;transform:translateY(-1px)}
.jd-links .lg{background:#f1f5f9;color:#475569}
.jd-links .lg:hover{background:#e2e8f0;transform:translateY(-1px)}

/* ── Footer ── */
.jd-ft{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:12px 22px;background:#fafbfc;border-top:1px solid #eef2f6;font-size:.82rem;color:#94a3b8}
.jd-ft .ft-urgent{color:#dc2626;font-weight:600}

/* ── Brand ── */
.jd-brand{text-align:center;padding:24px 0 8px;font-size:.84rem;color:#c0c5cc}
.jd-brand .rainbow-text{font-weight:800;font-size:.95rem}

/* ── Float ── */
.jd-float{position:fixed;bottom:24px;right:24px;z-index:100;display:none}
.jd-float a{display:flex;align-items:center;gap:8px;padding:13px 22px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-weight:700;font-size:.92rem;text-decoration:none;box-shadow:0 6px 20px rgba(74,108,247,.35);transition:all .25s}
.jd-float a:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(74,108,247,.4)}
@media(max-width:768px){.jd-float{display:block}}

/* ── Responsive ── */
@media(max-width:640px){
.jd-hero{padding:22px 18px}
.jd-hero-row{flex-direction:column}
.jd-hero-badge{width:100%;flex-direction:row;gap:12px;padding:10px 18px}
.jd-hero-badge .hb-num{font-size:1.4rem}
.jd-card-hd{padding:13px 16px}
.jd-card-bd{padding:14px 16px}
.jd-tbl thead th,.jd-tbl tbody td{padding:8px 12px}
.jd-ft{padding:10px 16px}
.jd-hl{grid-template-columns:1fr 1fr}
.jd-dates{grid-template-columns:1fr}
.jd-cd .cbtn{width:100%}
.jd-cd .cbtn a{width:100%;text-align:center}
}
</style>

<!-- Topbar -->
<div class="jd-bar">
    <div class="container">
        <div class="jd-bar-inner">
            <a href="index.php" class="jd-bar-back">← Back to all jobs</a>
            <div class="jd-bar-actions">
                <?php if (isLoggedIn()): ?>
                <button onclick="toggleSave(this,'job',<?php echo $job['id']; ?>)" title="Save">☆</button>
                <?php endif; ?>
                <a href="javascript:window.print()" title="Print">⎙</a>
            </div>
        </div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:920px;">

        <!-- Hero -->
        <div class="jd-hero su">
            <div class="jd-hero-grid"></div>
            <div class="jd-hero-content">
                <div class="jd-hero-tags">
                    <?php if ($job['is_featured']): ?><span class="jd-hero-tag feat">★ Featured</span><?php endif; ?>
                    <span class="jd-hero-tag"><?php echo htmlspecialchars($job['application_type']); ?></span>
                    <?php if ($isExpired): ?><span class="jd-hero-tag danger">Expired</span>
                    <?php elseif ($isUrgent): ?><span class="jd-hero-tag urgent"><?php echo $daysLeft; ?> day<?php echo $daysLeft>1?'s':''; ?> left</span><?php endif; ?>
                </div>
                <div class="jd-hero-row">
                    <div class="jd-hero-text">
                        <h1><?php echo htmlspecialchars($job['title']); ?></h1>
                        <div class="jd-hero-org">🏛 <?php echo htmlspecialchars($job['company']); ?></div>
                    </div>
                    <?php if ($displayVac): ?>
                    <div class="jd-hero-badge">
                        <div class="hb-num"><?php echo number_format($displayVac); ?></div>
                        <div class="hb-label">Total Vacancy</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Countdown -->
        <?php if ($hasDeadline): ?>
        <div class="jd-cd su s1">
            <div class="cicon">⏰</div>
            <div class="cbody">
                <div class="clabel"><?php echo $isUrgent ? '⚠️ Hurry! Last date approaching' : 'Last Date to Apply'; ?></div>
                <div class="ctimer">
                    <span id="cdDays"><?php echo str_pad($daysLeft,2,'0',STR_PAD_LEFT); ?></span><span class="csep">:</span>
                    <span id="cdHours">00</span><span class="csep">:</span>
                    <span id="cdMins">00</span><span class="csep">:</span>
                    <span id="cdSecs">00</span>
                </div>
            </div>
            <?php if ($job['application_link']): ?>
            <div class="cbtn"><a href="<?php echo htmlspecialchars($job['application_link']); ?>" target="_blank" class="btn btn-primary">Apply Now</a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Description -->
        <div class="jd-card su s1">
            <div class="jd-card-bd" style="padding-bottom:0;">
                <div class="jd-mtags">
                    <?php if ($job['advertise_no']): ?>
                    <div class="jd-mtag"><span class="micon">📋</span> Advt No. <span><?php echo htmlspecialchars($job['advertise_no']); ?></span></div>
                    <?php endif; ?>
                    <?php if ($job['exam_name']): ?>
                    <div class="jd-mtag"><span class="micon">📝</span> Exam <span><?php echo htmlspecialchars($job['exam_name']); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="jd-card-hd"><div class="hd-icon hd-desc">📄</div><h3>Description</h3></div>
            <div class="jd-card-bd"><p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p></div>
        </div>

        <!-- Post & Vacancy -->
        <?php if ($posts && $posts->num_rows > 0): ?>
        <div class="jd-card su s2">
            <div class="jd-card-hd"><div class="hd-icon hd-post">📋</div><h3>Post &amp; Vacancy Details</h3></div>
            <div style="padding:0;">
                <table class="jd-tbl">
                    <thead><tr><th>Post Name</th><th>Vacancy</th></tr></thead>
                    <tbody><?php while($p=$posts->fetch_assoc()): ?>
                        <tr><td><strong><?php echo htmlspecialchars($p['post_name']); ?></strong></td><td><strong><?php echo number_format($p['vacancy']); ?></strong></td></tr>
                    <?php endwhile; ?></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Education -->
        <?php if ($quals && $quals->num_rows > 0): ?>
        <div class="jd-card su s3">
            <div class="jd-card-hd"><div class="hd-icon hd-edu">🎓</div><h3>Education &amp; Qualification</h3></div>
            <div style="padding:0;">
                <table class="jd-tbl">
                    <thead><tr><th>Education Required</th></tr></thead>
                    <tbody><?php while($q=$quals->fetch_assoc()): ?>
                        <tr><td><?php echo htmlspecialchars($q['education']); ?></td></tr>
                    <?php endwhile; ?></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fee -->
        <?php if ($fees && $fees->num_rows > 0): ?>
        <div class="jd-card su s4">
            <div class="jd-card-hd"><div class="hd-icon hd-fee">💰</div><h3>Application Fee</h3></div>
            <div style="padding:0;">
                <table class="jd-tbl">
                    <thead><tr><th>Category</th><th>Fee</th></tr></thead>
                    <tbody><?php while($f=$fees->fetch_assoc()): ?>
                        <tr><td><?php echo htmlspecialchars($f['category']); ?></td><td><strong><?php echo htmlspecialchars($f['fee']); ?></strong></td></tr>
                    <?php endwhile; ?></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Key Highlights -->
        <div class="su s4" style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;">📊</div>
                <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin:0;">Key Highlights</h3>
            </div>
            <div class="jd-hl">
                <div class="jd-hli">
                    <div class="hli-icon">📌</div>
                    <div class="hli-lbl">Type</div>
                    <div class="hli-val" style="text-transform:capitalize;"><?php echo htmlspecialchars($job['application_type']); ?></div>
                </div>
                <?php if ($job['job_location']): ?>
                <div class="jd-hli">
                    <div class="hli-icon">📍</div>
                    <div class="hli-lbl">Location</div>
                    <div class="hli-val"><?php echo htmlspecialchars($job['job_location']); ?></div>
                </div>
                <?php endif; ?>
                <?php if ($job['salary_range']): ?>
                <div class="jd-hli">
                    <div class="hli-icon">💰</div>
                    <div class="hli-lbl">Salary</div>
                    <div class="hli-val"><?php echo htmlspecialchars($job['salary_range']); ?></div>
                </div>
                <?php endif; ?>
                <div class="jd-hli">
                    <div class="hli-icon">📅</div>
                    <div class="hli-lbl">Posted</div>
                    <div class="hli-val"><?php echo timeAgo($job['created_at']); ?></div>
                </div>
                <?php if ($job['application_deadline']): ?>
                <div class="jd-hli <?php echo $isExpired?'muted':'danger'; ?>">
                    <div class="hli-icon">⏳</div>
                    <div class="hli-lbl">Last Date</div>
                    <div class="hli-val"><?php echo $isExpired ? 'Expired' : date('d M Y', strtotime($job['application_deadline'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Important Dates -->
        <?php if ($dates && is_array($dates) && count($dates) > 0): ?>
        <div class="jd-card su s5">
            <div class="jd-card-hd"><div class="hd-icon hd-date">📅</div><h3>Important Dates</h3></div>
            <div class="jd-card-bd">
                <div class="jd-dates">
                    <?php foreach ($dates as $dk => $dv): if (!empty($dv) && isset($date_labels[$dk])): $isLast = ($dk === 'application_end'); ?>
                        <div class="jd-dti <?php echo $isLast ? 'danger' : ''; ?>">
                            <div class="ddlbl"><?php echo $date_labels[$dk]; ?></div>
                            <div class="ddval"><?php echo date('d M Y', strtotime($dv)); ?></div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Links -->
        <div class="jd-card su s5">
            <div class="jd-card-hd"><div class="hd-icon hd-link">🔗</div><h3>Important Links</h3></div>
            <div class="jd-card-bd">
                <div class="jd-links">
                    <?php if ($job['advertisement_url']): ?><a href="<?php echo htmlspecialchars($job['advertisement_url']); ?>" target="_blank" class="lo">📄 Advertisement</a><?php endif; ?>
                    <?php if ($job['official_website']): ?><a href="<?php echo htmlspecialchars($job['official_website']); ?>" target="_blank" class="lo">🌐 Website</a><?php endif; ?>
                    <?php if ($job['application_link']): ?><a href="<?php echo htmlspecialchars($job['application_link']); ?>" target="_blank" class="lp">📝 Apply Now</a><?php endif; ?>
                    <?php if ($job['other_link'] && $job['other_link_text']): ?><a href="<?php echo htmlspecialchars($job['other_link']); ?>" target="_blank" class="lg"><?php echo htmlspecialchars($job['other_link_text']); ?></a><?php endif; ?>
                </div>
            </div>
            <div class="jd-ft">
                <span>Posted <?php echo timeAgo($job['created_at']); ?></span>
                <?php if ($hasDeadline): ?>
                    <span class="<?php echo $isUrgent?'ft-urgent':''; ?>">⏰ Last Date: <?php echo date('d M Y', strtotime($job['application_deadline'])); ?></span>
                <?php elseif ($isExpired): ?>
                    <span class="ft-urgent">⛔ Expired</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="jd-brand">
            Powered by <span class="rainbow-text">www.nishantmultiservices.com</span>
        </div>

    </div>
</section>

<?php if ($job['application_link'] && $hasDeadline): ?>
<div class="jd-float">
    <a href="<?php echo htmlspecialchars($job['application_link']); ?>" target="_blank">📝 Apply Now</a>
</div>
<?php endif; ?>

<script>
(function(){
    var ts=parseInt('<?php echo $deadlineTs; ?>');if(!ts)return;
    function t(){var n=Math.floor(Date.now()/1000),d=Math.max(0,ts-n),dd=Math.floor(d/86400),hh=Math.floor((d%86400)/3600),mm=Math.floor((d%3600)/60),ss=d%60;
    document.getElementById('cdDays').textContent=String(dd).padStart(2,'0');
    document.getElementById('cdHours').textContent=String(hh).padStart(2,'0');
    document.getElementById('cdMins').textContent=String(mm).padStart(2,'0');
    document.getElementById('cdSecs').textContent=String(ss).padStart(2,'0');}
    t();setInterval(t,1000);
})();
</script>

<?php include '../includes/footer.php'; ?>

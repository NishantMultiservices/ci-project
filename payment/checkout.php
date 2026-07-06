<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to continue with payment.';
    redirect('/auth/login.php');
}

$type = $_GET['type'] ?? '';
$id   = intval($_GET['id'] ?? 0);

if (!in_array($type, ['exam', 'note']) || !$id) {
    $_SESSION['error'] = 'Invalid request.';
    redirect('/index.php');
}

if ($type === 'exam') {
    $item = $conn->query("SELECT e.*, c.name as category_name FROM exams e LEFT JOIN categories c ON e.category_id = c.id WHERE e.id = $id AND e.is_active = 1")->fetch_assoc();
    $backLink = "../exams/details.php?id=$id";
    $itemIcon = '📝';
    $itemType = 'Mock Exam';
} else {
    $item = $conn->query("SELECT n.*, c.name as category_name FROM study_notes n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id = $id AND n.is_public = 1")->fetch_assoc();
    $backLink = "../notes/details.php?id=$id";
    $itemIcon = '📖';
    $itemType = 'Study Note';
}

if (!$item) {
    $_SESSION['error'] = 'Item not found.';
    redirect('/index.php');
}

$already = $conn->query("SELECT id FROM purchases WHERE user_id = {$_SESSION['user_id']} AND item_type = '$type' AND item_id = $id AND status = 'completed'")->num_rows;
if ($item['is_free'] || $already) {
    $_SESSION['success'] = 'You already have access to this item.';
    $content = $type === 'exam' ? "../exams/take_exam.php?id=$id" : "../notes/details.php?id=$id";
    redirect($content);
}

$page_title = 'Checkout - ' . htmlspecialchars($item['title']);
include '../includes/header.php';
?>

<style>
.chk-wrap{max-width:520px;margin:0 auto}
.chk-card{background:white;border-radius:14px;border:1px solid #eef2f6;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04);margin-bottom:20px}
.chk-card-hd{padding:18px 24px;border-bottom:1px solid #f1f4f9;display:flex;align-items:center;gap:10px}
.chk-card-hd h3{font-size:.95rem;font-weight:700;color:#0f172a;margin:0}
.chk-card-bd{padding:20px 24px}

.chk-summary{display:flex;gap:16px;align-items:center}
.chk-summary .chk-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.chk-summary .chk-info{flex:1}
.chk-summary .chk-info h2{font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 2px}
.chk-summary .chk-info .chk-meta{font-size:.8rem;color:#94a3b8}
.chk-summary .chk-price{font-size:1.3rem;font-weight:800;color:#0f172a;white-space:nowrap}

.chk-amt-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0}
.chk-amt-row+.chk-amt-row{border-top:1px solid #f1f5f9}
.chk-amt-row .lbl{font-size:.88rem;color:#64748b}
.chk-amt-row .val{font-size:.95rem;font-weight:600;color:#0f172a}
.chk-amt-row.total .lbl{font-weight:700;color:#0f172a}
.chk-amt-row.total .val{font-size:1.2rem;color:var(--primary)}

.chk-pp-brand{display:flex;align-items:center;gap:14px}
.chk-pp-brand .pp-logo{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#5F259F,#7C3AED);display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;color:white;flex-shrink:0}
.chk-pp-brand .pp-info{flex:1}
.chk-pp-brand .pp-info strong{display:block;font-size:.95rem;color:#0f172a}
.chk-pp-brand .pp-info small{font-size:.78rem;color:#94a3b8}

.chk-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.chk-badges span{padding:5px 10px;border-radius:6px;font-size:.72rem;font-weight:600}

.chk-pay-btn{display:block;width:100%;padding:16px;border:none;border-radius:10px;font-size:1.05rem;font-weight:700;color:white;background:linear-gradient(135deg,#5F259F,#7C3AED);cursor:pointer;transition:all .25s}
.chk-pay-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(95,37,159,.35)}
.chk-pay-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}

.chk-secure{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;font-size:.78rem;color:#94a3b8}

@media(max-width:640px){
.chk-card-hd,.chk-card-bd{padding:14px 16px}
}
</style>

<section class="section" style="padding-top:40px;">
    <div class="container chk-wrap">

        <div style="margin-bottom:24px;">
            <a href="<?php echo $backLink; ?>" style="font-size:.84rem;color:#889096;display:flex;align-items:center;gap:6px;">← Back to details</a>
        </div>

        <!-- Order Summary -->
        <div class="chk-card">
            <div class="chk-card-hd"><h3>📋 Order Summary</h3></div>
            <div class="chk-card-bd">
                <div class="chk-summary">
                    <div class="chk-icon" style="background:linear-gradient(135deg,<?php echo $type==='exam' ? '#eef2ff,#c7d2fe' : '#d1fae5,#a7f3d0'; ?>);color:<?php echo $type==='exam' ? '#4f46e5' : '#10b981'; ?>;">
                        <?php echo $itemIcon; ?>
                    </div>
                    <div class="chk-info">
                        <h2><?php echo htmlspecialchars($item['title']); ?></h2>
                        <div class="chk-meta"><?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?> · <?php echo $itemType; ?></div>
                    </div>
                    <div class="chk-price">₹<?php echo number_format($item['price'], 0); ?></div>
                </div>
            </div>
        </div>

        <!-- Price Detail -->
        <div class="chk-card">
            <div class="chk-card-hd"><h3>💰 Price Details</h3></div>
            <div class="chk-card-bd">
                <div class="chk-amt-row">
                    <span class="lbl">Item Price</span>
                    <span class="val">₹<?php echo number_format($item['price'], 0); ?></span>
                </div>
                <div class="chk-amt-row">
                    <span class="lbl">Tax</span>
                    <span class="val" style="color:#10b981;">₹0</span>
                </div>
                <div class="chk-amt-row">
                    <span class="lbl">Discount</span>
                    <span class="val" style="color:#10b981;">₹0</span>
                </div>
                <div class="chk-amt-row total">
                    <span class="lbl">Total</span>
                    <span class="val">₹<?php echo number_format($item['price'], 0); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="chk-card">
            <div class="chk-card-hd"><h3>💳 Payment Method</h3></div>
            <div class="chk-card-bd">
                <div class="chk-pp-brand">
                    <div class="pp-logo">PP</div>
                    <div class="pp-info">
                        <strong>PhonePe</strong>
                        <small>Pay via UPI, Card, Net Banking or Wallet</small>
                    </div>
                </div>
                <div class="chk-badges">
                    <span style="background:#f3e8ff;color:#7C3AED;">📱 UPI</span>
                    <span style="background:#eef2ff;color:#4f46e5;">💳 Cards</span>
                    <span style="background:#fef3c7;color:#d97706;">🏦 Net Banking</span>
                    <span style="background:#d1fae5;color:#10b981;">🔒 Secured</span>
                </div>
            </div>
        </div>

        <!-- Pay Button -->
        <form method="POST" action="pay.php">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="chk-card">
                <div class="chk-card-bd" style="text-align:center;">
                    <button type="submit" class="chk-pay-btn" id="payBtn">
                        🔒 Pay ₹<?php echo number_format($item['price'], 0); ?> via PhonePe
                    </button>
                    <div class="chk-secure">
                        <span>🔒</span> Secure payment · You will be redirected to PhonePe
                    </div>
                </div>
            </div>
        </form>

    </div>
</section>

<script>
document.getElementById('payBtn')?.addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '⏳ Redirecting to PhonePe...';
    this.form.submit();
});
</script>

<?php include '../includes/footer.php'; ?>

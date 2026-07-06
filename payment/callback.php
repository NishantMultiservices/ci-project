<?php
/**
 * PhonePe Payment Callback
 * 
 * PhonePe redirects here after payment (POST).
 * We DO NOT trust the redirect response alone.
 * We always call status.php to verify via PhonePe status API.
 */

require_once __DIR__ . '/helpers.php';

$responseB64 = $_POST['response'] ?? '';
$xVerify     = $_SERVER['HTTP_X_VERIFY'] ?? '';

// Get pending transaction info from session
$info = $_SESSION['pending_txn'] ?? null;

if (!$info || !$info['txn_id']) {
    $_SESSION['error'] = 'Session expired. Please try again.';
    redirect('/index.php');
}

$txnId = $info['txn_id'];

// Verify checksum of the redirect response (first-level check)
if ($responseB64 && $xVerify) {
    if (!phonepeVerifyCallback($responseB64, $xVerify)) {
        $_SESSION['error'] = 'Security check failed: invalid response signature.';
        redirect('/index.php');
    }
}

// ── Always confirm via PhonePe Status API ──
$statusUrl = SITE_URL . '/payment/status.php?transactionId=' . urlencode($txnId);
$statusResult = @file_get_contents($statusUrl);
$status = $statusResult ? json_decode($statusResult, true) : null;

if (!$status || !isset($status['success'])) {
    // Fallback: try cURL directly
    $statusResult = phonepeCurlStatus('/pg/v1/status/' . PHONEPE_MERCHANT_ID . '/' . $txnId);
    $statusData = json_decode($statusResult['response'], true);
    $status = [
        'success' => ($statusData['code'] ?? '') === 'PAYMENT_SUCCESS' && ($statusData['data']['state'] ?? '') === 'COMPLETED',
        'code'    => $statusData['code'] ?? '',
        'state'   => $statusData['data']['state'] ?? '',
    ];
}

if ($status['success']) {
    // Mark purchase as completed
    $stmt = $conn->prepare("UPDATE purchases SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $info['purchase_id'], $_SESSION['user_id']);
    $stmt->execute();

    $returnUrl = ($info['type'] === 'exam')
        ? SITE_URL . '/exams/take_exam.php?id=' . $info['item_id']
        : SITE_URL . '/notes/details.php?id=' . $info['item_id'];

    unset($_SESSION['pending_txn']);
    $_SESSION['success'] = 'Payment successful via PhonePe! You now have access.';
    redirect($returnUrl);
}

// Payment failed
$msg = $status['message'] ?: ($status['code'] ?: 'Payment unsuccessful');
$_SESSION['error'] = 'Payment ' . htmlspecialchars($msg) . '. Please try again.';
$checkoutUrl = SITE_URL . '/payment/checkout.php?type=' . $info['type'] . '&id=' . $info['item_id'];
unset($_SESSION['pending_txn']);
redirect($checkoutUrl);

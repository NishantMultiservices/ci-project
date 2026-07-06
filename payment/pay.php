<?php
require_once __DIR__ . '/helpers.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to pay.';
    redirect('/auth/login.php');
}

$type = $_POST['type'] ?? '';
$id   = intval($_POST['id'] ?? 0);

if (!in_array($type, ['exam', 'note']) || !$id) {
    $_SESSION['error'] = 'Invalid request.';
    redirect('/index.php');
}

$data = phonepeGetItem($conn, $type, $id);
$item = $data['item'];
$returnUrl = $data['return_url'];

if (!$item) {
    $_SESSION['error'] = 'Item not found.';
    redirect('/index.php');
}

$already = $conn->query("SELECT id FROM purchases WHERE user_id = {$_SESSION['user_id']} AND item_type = '$type' AND item_id = $id AND status = 'completed'")->num_rows;
if ($already) {
    $_SESSION['success'] = 'You already have access.';
    redirect($returnUrl);
}

// Create transaction
$txnId = 'TXN' . strtoupper(bin2hex(random_bytes(8))) . time();
$amountPaise = intval($item['price'] * 100);
$callbackUrl = SITE_URL . '/payment/callback.php';

// Save pending purchase
$stmt = $conn->prepare("INSERT INTO purchases (user_id, item_type, item_id, amount, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isid", $_SESSION['user_id'], $type, $id, $item['price']);
$stmt->execute();
$purchaseId = $stmt->insert_id;

// Save txn_id reference on the purchase
$conn->query("UPDATE purchases SET txn_id = '" . $conn->real_escape_string($txnId) . "' WHERE id = $purchaseId");

// Store in session for callback matching
$_SESSION['pending_txn'] = [
    'purchase_id' => $purchaseId,
    'txn_id'      => $txnId,
    'type'        => $type,
    'item_id'     => $id,
];

// Build PhonePe payload
$payload = phonepeBuildPayload($txnId, $_SESSION['user_id'], $amountPaise, $callbackUrl);

// Encode & checksum
$payloadB64 = base64_encode(json_encode($payload));
$endpoint = '/pg/v1/pay';

// Call PhonePe API
$result = phonepeCurl($endpoint, $payloadB64);

if ($result['error']) {
    $_SESSION['error'] = 'Connection failed: ' . $result['error'];
    redirect("/payment/checkout.php?type=$type&id=$id");
}

$response = json_decode($result['response'], true);
$redirectUrl = $response['data']['instrumentResponse']['redirectInfo']['url'] ?? null;

if ($redirectUrl) {
    header("Location: $redirectUrl");
    exit;
}

// Handle failure
$msg = $response['message'] ?? ($response['code'] ?? 'Payment initiation failed');
$_SESSION['error'] = 'PhonePe: ' . htmlspecialchars($msg);
redirect("/payment/checkout.php?type=$type&id=$id");

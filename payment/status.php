<?php
/**
 * PhonePe Payment Status Verification
 * 
 * Call via: status.php?transactionId=TXNxxx
 * Returns JSON: {success: bool, code: string, state: string, message: string}
 */

require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

$txnId = $_GET['transactionId'] ?? '';

if (!$txnId) {
    echo json_encode(['success' => false, 'code' => 'INVALID', 'state' => '', 'message' => 'Missing transaction ID']);
    exit;
}

$endpoint = '/pg/v1/status/' . PHONEPE_MERCHANT_ID . '/' . $txnId;
$result = phonepeCurlStatus($endpoint);

if ($result['error']) {
    echo json_encode(['success' => false, 'code' => 'CURL_ERROR', 'state' => '', 'message' => $result['error']]);
    exit;
}

$response = json_decode($result['response'], true);
$code  = $response['code'] ?? '';
$state = $response['data']['state'] ?? '';
$xVerify = $result['response']; // Not needed for status verify in this simple form

$isSuccess = ($code === 'PAYMENT_SUCCESS' && $state === 'COMPLETED');

echo json_encode([
    'success' => $isSuccess,
    'code'    => $code,
    'state'   => $state,
    'message' => $response['message'] ?? '',
    'data'    => $response['data'] ?? null,
]);

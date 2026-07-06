<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/phonepe.php';

function phonepeChecksum($payloadB64, $endpoint) {
    return hash('sha256', $payloadB64 . $endpoint . PHONEPE_SALT_KEY) . '###' . PHONEPE_SALT_INDEX;
}

function phonepeHeaders($payloadB64, $endpoint) {
    return [
        'Content-Type: application/json',
        'X-VERIFY: ' . phonepeChecksum($payloadB64, $endpoint),
        'X-MERCHANT-ID: ' . PHONEPE_MERCHANT_ID,
    ];
}

function phonepeVerifyCallback($payloadB64, $xVerify) {
    $expected = hash('sha256', $payloadB64 . '/pg/v1/pay' . PHONEPE_SALT_KEY) . '###' . PHONEPE_SALT_INDEX;
    return hash_equals($expected, $xVerify);
}

function phonepeVerifyStatusResponse($payloadB64, $xVerify) {
    $expected = hash('sha256', $payloadB64 . '/pg/v1/status' . PHONEPE_SALT_KEY) . '###' . PHONEPE_SALT_INDEX;
    return hash_equals($expected, $xVerify);
}

function phonepeCurl($endpoint, $payloadB64) {
    $url = PHONEPE_BASE_URL . $endpoint;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['request' => $payloadB64]),
        CURLOPT_HTTPHEADER => phonepeHeaders($payloadB64, $endpoint),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return ['response' => $response, 'http_code' => $httpCode, 'error' => $error];
}

function phonepeCurlStatus($endpoint) {
    $url = PHONEPE_BASE_URL . $endpoint;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-VERIFY: ' . phonepeChecksum('', $endpoint),
            'X-MERCHANT-ID: ' . PHONEPE_MERCHANT_ID,
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return ['response' => $response, 'http_code' => $httpCode, 'error' => $error];
}

function phonepeBuildPayload($merchantTxnId, $userId, $amountPaise, $callbackUrl) {
    return [
        'merchantId' => PHONEPE_MERCHANT_ID,
        'merchantTransactionId' => $merchantTxnId,
        'merchantUserId' => 'USR' . $userId,
        'amount' => $amountPaise,
        'redirectUrl' => $callbackUrl,
        'redirectMode' => 'POST',
        'callbackUrl' => $callbackUrl,
        'mobileNumber' => '',
        'paymentInstrument' => ['type' => 'PAY_PAGE'],
    ];
}

function phonepeGetItem($conn, $type, $id) {
    if ($type === 'exam') {
        $item = $conn->query("SELECT id, title, price FROM exams WHERE id = " . intval($id) . " AND is_active = 1")->fetch_assoc();
        $returnUrl = SITE_URL . '/exams/take_exam.php?id=' . intval($id);
    } else {
        $item = $conn->query("SELECT id, title, price FROM study_notes WHERE id = " . intval($id) . " AND is_public = 1")->fetch_assoc();
        $returnUrl = SITE_URL . '/notes/details.php?id=' . intval($id);
    }
    return ['item' => $item, 'return_url' => $returnUrl];
}

<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

$item_type = $_POST['item_type'] ?? '';
$item_id = intval($_POST['item_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$valid_types = ['exam', 'note', 'job', 'hall_ticket', 'answer_key', 'exam_notification'];
if (!in_array($item_type, $valid_types) || $item_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

$check = $conn->prepare("SELECT id FROM saved_items WHERE user_id = ? AND item_type = ? AND item_id = ?");
$check->bind_param("isi", $user_id, $item_type, $item_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $delete = $conn->prepare("DELETE FROM saved_items WHERE id = ?");
    $delete->bind_param("i", $existing['id']);
    $delete->execute();
    echo json_encode(['status' => 'unsaved']);
} else {
    $insert = $conn->prepare("INSERT INTO saved_items (user_id, item_type, item_id) VALUES (?, ?, ?)");
    $insert->bind_param("isi", $user_id, $item_type, $item_id);
    $insert->execute();
    echo json_encode(['status' => 'saved']);
}

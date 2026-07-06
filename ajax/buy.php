<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to purchase.';
    redirect('/auth/login.php');
}

$item_type = $_GET['type'] ?? '';
$item_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$valid_types = ['exam', 'note'];
if (!in_array($item_type, $valid_types) || $item_id <= 0) {
    $_SESSION['error'] = 'Invalid request.';
    redirect('/index.php');
}

$table = $item_type === 'exam' ? 'exams' : 'study_notes';
$item = $conn->query("SELECT id, is_free, price FROM $table WHERE id = $item_id")->fetch_assoc();

if (!$item) {
    $_SESSION['error'] = 'Item not found.';
    redirect("/$table/index.php");
}

$already = $conn->query("SELECT id FROM purchases WHERE user_id = $user_id AND item_type = '$item_type' AND item_id = $item_id AND status = 'completed'")->num_rows > 0;

if (!$already && !$item['is_free']) {
    $stmt = $conn->prepare("INSERT INTO purchases (user_id, item_type, item_id, amount, status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->bind_param("isid", $user_id, $item_type, $item_id, $item['price']);
    $stmt->execute();
    $_SESSION['success'] = 'Purchase successful! You now have access.';
}

$redirect = $item_type === 'exam' ? "/exams/take_exam.php?id=$item_id" : "/notes/details.php?id=$item_id";
redirect($redirect);

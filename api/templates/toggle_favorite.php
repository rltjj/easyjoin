<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
  echo json_encode(['success' => false, 'message' => '권한 없음']);
  exit;
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$ids  = $data['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
  echo json_encode(['success' => false, 'message' => 'ids가 없습니다.']);
  exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "
  UPDATE templates
  SET is_favorite = IF(is_favorite = 1, 0, 1)
  WHERE id IN ($placeholders)
";

$stmt = $pdo->prepare($sql);
$stmt->execute($ids);

echo json_encode(['success' => true]);

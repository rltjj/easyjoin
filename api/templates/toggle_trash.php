<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

if (empty($ids)) {
  echo json_encode(['success' => false]);
  exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "
  UPDATE templates
  SET is_deleted = 1
  WHERE id IN ($placeholders)
";

$stmt = $pdo->prepare($sql);
$result = $stmt->execute($ids);

echo json_encode(['success' => $result]);

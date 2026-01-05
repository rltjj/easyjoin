<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    exit;
}

$templateId = intval($_GET['id'] ?? 0);
$data = json_decode(file_get_contents('php://input'), true);

$pdo->prepare("DELETE FROM template_fields WHERE template_id = :id")
    ->execute([':id'=>$templateId]);

$stmt = $pdo->prepare("
  INSERT INTO template_fields
  (template_id, field_type, role, label, pos_x, pos_y, width, height)
  VALUES
  (:tid, 'TEXT','STAFF',:label,:x,:y,:w,:h)
");

foreach ($data as $f) {
    $stmt->execute([
        ':tid'=>$templateId,
        ':label'=>$f['label'],
        ':x'=>$f['pos_x'],
        ':y'=>$f['pos_y'],
        ':w'=>$f['width'],
        ':h'=>$f['height']
    ]);
}

echo json_encode(['success'=>true]);

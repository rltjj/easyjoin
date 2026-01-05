<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode([
        'success' => false,
        'message' => '권한이 없습니다.'
    ]);
    exit;
}

$siteId = $_SESSION['current_site_id'] ?? null;
if (!$siteId) {
    echo json_encode([
        'success' => false,
        'message' => '현장이 선택되지 않았습니다.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
if ($name === '') {
    echo json_encode([
        'success' => false,
        'message' => '카테고리명을 입력하세요.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO templates_categories (site_id, name)
        VALUES (:site_id, :name)
    ");
    $stmt->execute([
        ':site_id' => $siteId,
        ':name'    => $name
    ]);

    echo json_encode([
        'success' => true,
        'id'      => $pdo->lastInsertId(),
        'name'    => $name
    ]);
} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        echo json_encode([
            'success' => false,
            'message' => '이미 존재하는 카테고리입니다.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'DB 오류 발생'
    ]);
}

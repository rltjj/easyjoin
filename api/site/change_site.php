<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(401);
    exit('로그인이 필요합니다.');
}

if ($_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    exit('권한이 없습니다.');
}

$siteId = intval($_POST['site_id'] ?? 0);
if ($siteId <= 0) exit('잘못된 요청입니다.');

$stmt = $pdo->prepare("
    SELECT id, site_name
    FROM sites
    WHERE id = ? AND service_enabled = 1
    LIMIT 1
");
$stmt->execute([$siteId]);
$site = $stmt->fetch();

if (!$site) exit('존재하지 않거나 비활성화된 현장입니다.');

$_SESSION['current_site_id']   = $site['id'];
$_SESSION['current_site_name'] = $site['site_name'];

$redirect = $_SERVER['HTTP_REFERER'] ?? '/';
if (strpos($redirect, '/templates/') === false) {
    $redirect = '/easyjoin/public/pages/home.php';
}
header('Location: ' . $redirect);
exit;

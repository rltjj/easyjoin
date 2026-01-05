<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/site_helper.php';
require_once __DIR__ . '/../../includes/home_queries.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: public/pages/login.html');
    exit;
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

$sites = getUserSites($pdo, $userId, $role);
$currentSiteId = $_SESSION['current_site_id'] ?? ($sites[0]['id'] ?? null);
$currentSite = null;

foreach ($sites as $site) {
    if ($site['id'] == $currentSiteId) {
        $currentSite = $site;
        break;
    }
}

$contracts = $currentSite ? getContractStats($pdo, $currentSite['id']) : [];
$inquiries = $currentSite ? getInquiries($pdo, $currentSite['id']) : [];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>홈 | EasyJoin</title>
  <link rel="stylesheet" href="../../public/css/layout.css">
  <link rel="stylesheet" href="../../public/css/header.css">
  <link rel="stylesheet" href="../../public/css/sidebar.css">
</head>
<body>

<div class="layout">
<?php include __DIR__.'/../../includes/sidebar.php'; ?>

<div class="main">
<?php include __DIR__.'/../../includes/header.php'; ?>

<div class="content">

<h2>계약 현황</h2>
<table class="table">
  <thead>
    <tr>
      <th>템플릿명</th>
      <th>카테고리</th>
      <th>진행</th>
      <th>완료</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($contracts as $row): ?>
    <tr>
      <td><?= htmlspecialchars($row['title']) ?></td>
      <td><?= htmlspecialchars($row['category']) ?></td>
      <td><?= $row['in_progress'] ?></td>
      <td><?= $row['completed'] ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2>문의 내역</h2>
<table class="table">
  <thead>
    <tr>
      <?php if ($role === 'ADMIN'): ?>
      <th><input type="checkbox"></th>
      <?php endif; ?>
      <th>상태</th>
      <th>요청자</th>
      <th>내용</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($inquiries as $inq): ?>
    <tr>
      <td><input type="checkbox" data-id="<?= $inq['id'] ?>"></td>
      <td><?= $inq['status'] === 'DONE' ? '처리됨' : '미처리' ?></td>
      <td><?= htmlspecialchars($inq['name'] ?? '-') ?></td>
      <td><?= htmlspecialchars($inq['content']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($role === 'ADMIN'): ?>
<button>처리</button>
<?php elseif ($role === 'OPERATOR'): ?>
<button>문의하기</button>
<?php endif; ?>

</div>
</div>
</div>

</body>
</html>

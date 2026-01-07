<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    exit('접근 권한이 없습니다.');
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/site_helper.php';

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

$sites = getUserSites($pdo, $userId, $role);
$currentSiteId = $_SESSION['current_site_id'] ?? ($sites[0]['id'] ?? 0);

// --- GET 파라미터 ---
$category = $_GET['category'] ?? 'ALL';
$keyword  = $_GET['keyword'] ?? '';
$limit    = intval($_GET['limit'] ?? 10);
$page     = max(1, intval($_GET['page'] ?? 1));
$offset   = ($page - 1) * $limit;

// --- 카테고리 목록 ---
$catStmt = $pdo->prepare("
    SELECT id, name
    FROM templates_categories
    WHERE site_id = :site_id
      AND is_deleted = 0
    ORDER BY name ASC
");
$catStmt->execute([':site_id' => $currentSiteId]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// --- 템플릿 목록 ---
$sql = "
SELECT 
  t.id,
  t.title,
  c.name AS category,
  t.created_at,
  t.is_favorite
FROM templates t
LEFT JOIN templates_categories c ON t.category_id = c.id
WHERE t.site_id = :site_id
  AND t.is_deleted = 0
  AND (:category = 'ALL' OR c.name = :category)
  AND t.title LIKE :keyword
ORDER BY t.sort_order ASC, t.id DESC
LIMIT :offset, :limit
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':site_id', $currentSiteId, PDO::PARAM_INT);
$stmt->bindValue(':category', $category);
$stmt->bindValue(':keyword', "%$keyword%");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ko" data-limit="<?= $limit ?>">
<head>
  <meta charset="UTF-8">
  <title>템플릿 관리</title>
  <link rel="stylesheet" href="../../../public/css/template.css">
  <link rel="stylesheet" href="../../../public/css/layout.css">
  <link rel="stylesheet" href="../../../public/css/header.css">
  <link rel="stylesheet" href="../../../public/css/sidebar.css">
</head>
<body>

  <div class="layout">
  <?php include __DIR__.'/../../../includes/sidebar.php'; ?>

  <div class="main">
  <?php include __DIR__.'/../../../includes/header.php'; ?>

  <main class="content">

    <!-- 상단 툴바 -->
    <div class="toolbar">
      <select onchange="search()">
        <option value="ALL">전체</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $category === $cat['name'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input type="text" id="keyword" placeholder="제목 검색" value="<?= htmlspecialchars($keyword) ?>">
      <button onclick="search()">검색</button>

      <div class="right">
        <button onclick="setLimit(10)">10개씩</button>
        <button onclick="setLimit(20)">20개씩</button>
      </div>
    </div>

    <!-- 템플릿 등록 버튼 -->
    <div class="top-action">
      <button onclick="openModal()">템플릿 등록</button>
    </div>

    <?php include 'create_modal.php'; ?>

    <!-- 선택 상태 -->
    <div class="bulk-bar">
      선택 <span id="selectedCount">0</span>개
      <div class="right">
        <button id="toggleFavoriteBtn">즐겨찾기</button>
        <button id="trashTemplateBtn">휴지통</button>
      </div>
    </div>

    <!-- 템플릿 리스트 -->
    <table class="template-table">
      <thead>
        <tr>
          <th><input type="checkbox" id="checkAll"></th>
          <th>템플릿 제목</th>
          <th>카테고리</th>
          <th>등록일</th>
        </tr>
      </thead>

      <tbody id="templateList">
        <?php foreach ($templates as $t): ?>
        <tr data-id="<?= $t['id'] ?>" data-favorite="<?= $t['is_favorite'] ?>">
          <td><input type="checkbox" class="row-check"></td>
          <td class="title"><?= htmlspecialchars($t['title']) ?></td>
          <td><?= htmlspecialchars($t['category']) ?></td>
          <td><?= date('Y-m-d', strtotime($t['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>

      <tbody>
        <tr id="templateActionBar" style="display:none;">
          <td colspan="4" class="action-bar">
            <button id="editTemplateBtn">내용 확인 및 수정</button>
            <button id="favoriteActionBtn">즐겨찾기</button>
            <button id="trashActionBtn">휴지통</button>
          </td>
        </tr>
      </tbody>
    </table>

  </main>

  <script src="../../js/template/list.js"></script>

</body>
</html>

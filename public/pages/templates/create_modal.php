<?php
$siteId = $_SESSION['current_site_id'] ?? null;

$categories = [];

if ($siteId) {
    $stmt = $pdo->prepare("
        SELECT id, name
        FROM templates_categories
        WHERE site_id = :site_id
          AND is_deleted = 0
        ORDER BY name ASC
    ");
    $stmt->execute([':site_id' => $siteId]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="modal" id="templateModal">
  <div class="modal-content">

    <h2>템플릿 등록</h2>

    <form id="templateForm" method="post" enctype="multipart/form-data"
          action="create_action.php">

      <div class="form-group">
        <label>문서명</label>
        <input type="text" name="title" required>
      </div>

      <div class="form-group">
        <label>카테고리</label>
        <select name="category_id" required>
          <option value="">선택</option>

          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

          <button type="button" class="add-category-btn"
            onclick="showCategoryInput()">+ 새 카테고리 추가</button>

          <div id="newCategoryBox" style="display:none;">
            <input type="text" id="newCategoryName" placeholder="카테고리명">
            <button type="button" onclick="addCategory()">추가</button>
          </div>
      </div>

      <div class="form-group">
        <label>PDF 파일</label>
        <input type="file" name="pdf" accept="application/pdf" required>
      </div>

      <div class="modal-actions">
        <button type="submit">등록</button>
        <button type="button" onclick="closeModal()">취소</button>
      </div>

    </form>

  </div>
</div>

<script>
function showCategoryInput() {
  document.getElementById('newCategoryBox').style.display = 'block';
}

function addCategory() {
  const name = document.getElementById('newCategoryName').value.trim();
  if (!name) return;

  fetch('/easyjoin/api/templates/add_category.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ name })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      const select = document.querySelector('select[name="category_id"]');
      const opt = document.createElement('option');
      opt.value = res.id;
      opt.textContent = res.name;
      opt.selected = true;
      select.appendChild(opt);
      document.getElementById('newCategoryBox').style.display = 'none';
    } else {
      alert(res.message);
    }
  });
}
</script>

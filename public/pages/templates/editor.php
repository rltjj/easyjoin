<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';

if ($_SESSION['role'] !== 'ADMIN') {
    die('접근 불가');
}

$templateId = intval($_GET['id'] ?? 0);
if (!$templateId) die('템플릿 없음');

/* 템플릿 조회 */
$stmt = $pdo->prepare("SELECT * FROM templates WHERE id = :id");
$stmt->execute([':id' => $templateId]);
$template = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$template) die('존재하지 않음');

/* 기존 필드 */
$stmt = $pdo->prepare("
  SELECT *
  FROM template_fields
  WHERE template_id = :id
");
$stmt->execute([':id' => $templateId]);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>템플릿 에디터</title>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.8.162/pdf.min.js"></script>

<style>
body { margin:0; display:flex; height:100vh; font-family:sans-serif; }
.sidebar {
  width:260px; background:#f5f5f5; padding:10px; overflow:auto;
}
.sidebar h3 { cursor:pointer; margin:10px 0; }
.sidebar .group { display:none; padding-left:10px; }
.sidebar button { width:100%; margin:4px 0; }

.editor {
  flex:1; background:#ddd; display:flex; justify-content:center;
}
#pdfWrap {
  position:relative;
}
.field {
  position:absolute;
  border:1px dashed #333;
  padding:4px;
  background:#fff;
  cursor:move;
}
.field.selected {
  border:2px solid red;
}
.save-btn {
  position:fixed;
  bottom:20px;
  right:20px;
  padding:10px 20px;
}
</style>
</head>

<body>

<!-- 좌측 필드 -->
<div class="sidebar">

  <h3 onclick="toggleGroup(this)">직원</h3>
  <div class="group">
    <button onclick="addField('TEXT','STAFF')">텍스트</button>
    <button onclick="addField('CHECKBOX','STAFF')">체크박스</button>
    <button onclick="addField('SIGN','STAFF')">서명/날인</button>
  </div>

  <h3 onclick="toggleGroup(this)">계약자</h3>
  <div class="group">
    <button onclick="addField('TEXT','CONTRACTOR')">텍스트</button>
    <button onclick="addField('CHECKBOX','CONTRACTOR')">체크박스</button>
    <button onclick="addField('SIGN','CONTRACTOR')">서명/날인</button>
    <button onclick="addField('TEXT','CONTRACTOR','주민등록번호')">주민등록번호</button>
    <button onclick="addField('TEXT','CONTRACTOR','주소')">주소</button>
    <button onclick="addField('TEXT','CONTRACTOR','동')">동</button>
    <button onclick="addField('TEXT','CONTRACTOR','호')">호</button>
  </div>

  <h3 onclick="toggleGroup(this)">날짜</h3>
  <div class="group">
    <button onclick="addField('DATE','CONTRACTOR','yyyy')">yyyy</button>
    <button onclick="addField('DATE','CONTRACTOR','mm')">mm</button>
    <button onclick="addField('DATE','CONTRACTOR','dd')">dd</button>
  </div>

</div>

<!-- PDF 영역 -->
<div class="editor">
  <div id="pdfWrap"></div>
</div>

<button class="save-btn" onclick="saveFields()">저장하기</button>

<script>
const pdfUrl = "<?= $template['pdf_path'] ?>";
const wrap = document.getElementById('pdfWrap');
let selected = null;
let fields = <?= json_encode($fields, JSON_UNESCAPED_UNICODE) ?>;

/* PDF 로드 */
pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
  pdf.getPage(1).then(page => {
    const viewport = page.getViewport({scale:1.3});
    const canvas = document.createElement('canvas');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    wrap.appendChild(canvas);

    page.render({
      canvasContext: canvas.getContext('2d'),
      viewport
    });

    renderFields();
  });
});

/* 필드 렌더 */
function renderFields() {
  fields.forEach(f => {
    createFieldEl(f);
  });
}

/* 필드 생성 */
function createFieldEl(f) {
  const el = document.createElement('div');
  el.className = 'field';
  el.style.left = f.pos_x + 'px';
  el.style.top = f.pos_y + 'px';
  el.style.width = (f.width || 100) + 'px';
  el.style.height = (f.height || 30) + 'px';
  el.textContent = f.label || f.field_type;
  el.dataset.id = f.id || '';

  makeDraggable(el);
  el.onclick = e => {
    e.stopPropagation();
    select(el);
  };
  wrap.appendChild(el);
}

/* 새 필드 */
function addField(type, role, label='') {
  const f = {
    field_type: type,
    role,
    label,
    pos_x: 50,
    pos_y: 50,
    width: type === 'CHECKBOX' ? 20 : 120,
    height: type === 'CHECKBOX' ? 20 : 30
  };
  fields.push(f);
  createFieldEl(f);
}

/* 드래그 */
function makeDraggable(el) {
  let offsetX, offsetY;
  el.onmousedown = e => {
    select(el);
    offsetX = e.offsetX;
    offsetY = e.offsetY;
    document.onmousemove = ev => {
      el.style.left = (ev.pageX - wrap.offsetLeft - offsetX) + 'px';
      el.style.top  = (ev.pageY - wrap.offsetTop - offsetY) + 'px';
    };
    document.onmouseup = () => {
      document.onmousemove = null;
    };
  };
}

/* 선택 */
function select(el) {
  document.querySelectorAll('.field').forEach(f => f.classList.remove('selected'));
  el.classList.add('selected');
  selected = el;
}

/* 삭제 */
document.addEventListener('keydown', e => {
  if (e.key === 'Backspace' && selected) {
    selected.remove();
    selected = null;
  }
});

/* 저장 */
function saveFields() {
  const data = [];
  document.querySelectorAll('.field').forEach(el => {
    data.push({
      label: el.textContent,
      pos_x: parseInt(el.style.left),
      pos_y: parseInt(el.style.top),
      width: parseInt(el.style.width),
      height: parseInt(el.style.height)
    });
  });

  fetch('save_fields.php?id=<?= $templateId ?>', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data)
  })
  .then(r=>r.json())
  .then(res=>{
    alert('저장 완료');
  });
}

function toggleGroup(h3) {
  const g = h3.nextElementSibling;
  g.style.display = g.style.display === 'block' ? 'none' : 'block';
}
</script>

</body>
</html>

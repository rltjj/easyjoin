document.addEventListener('DOMContentLoaded', () => {
  const checks = document.querySelectorAll('.row-check');
  const count = document.getElementById('selectedCount');
  const checkAll = document.getElementById('checkAll');

  // limit 값은 HTML data-limit에서 읽기
  const limit = parseInt(document.documentElement.dataset.limit, 10) || 10;

  // 체크박스 개수 업데이트
  function updateCount() {
    count.textContent = document.querySelectorAll('.row-check:checked').length;
    checkAll.checked = document.querySelectorAll('.row-check:checked').length === checks.length;
  }

  checks.forEach(c => c.addEventListener('change', updateCount));
  checkAll.addEventListener('change', e => {
    checks.forEach(c => c.checked = e.target.checked);
    updateCount();
  });

  // 검색
  window.search = function() {
    const k = document.getElementById('keyword').value;
    const category = document.querySelector('.toolbar select').value;
    location.href = `?category=${encodeURIComponent(category)}&keyword=${encodeURIComponent(k)}&limit=${limit}`;
  };

  // 페이지당 항목 수 변경
  window.setLimit = function(n) {
    const category = document.querySelector('.toolbar select').value;
    const keyword = document.getElementById('keyword').value;
    location.href = `?category=${encodeURIComponent(category)}&keyword=${encodeURIComponent(keyword)}&limit=${n}`;
  };

  // 휴지통 이동
  window.moveToTrash = function() {
    const ids = [...document.querySelectorAll('.row-check:checked')]
      .map(c => c.closest('tr').dataset.id);

    if (!ids.length) return alert('선택된 템플릿이 없습니다.');
    if (!confirm('휴지통으로 이동하시겠습니까?')) return;

    fetch('../../../api/templates/toggle_trash.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ ids })
    }).then(() => location.reload());
  };

  // 모달 열기/닫기
  window.openModal = () => document.getElementById('templateModal').classList.add('show');
  window.closeModal = () => document.getElementById('templateModal').classList.remove('show');

  const templateRows = document.querySelectorAll('#templateList tr');
  const actionBar = document.getElementById('templateActionBar');
  const editBtn = document.getElementById('editTemplateBtn');
  const favoriteBtn = document.getElementById('toggleFavoriteBtn');
  const trashBtn = document.getElementById('trashTemplateBtn');

  let selectedTemplateId = null;
  let isFavorite = false; // 초기값, 필요 시 DB에서 가져오기

  templateRows.forEach(row => {
    row.addEventListener('click', (e) => {
      // 체크박스 클릭은 액션바 토글 제외
      if (e.target.classList.contains('row-check')) return;

      const clickedId = row.dataset.id;

      // 같은 템플릿 클릭 시 접기
      if (selectedTemplateId === clickedId) {
        selectedTemplateId = null;
        actionBar.style.display = 'none';
        return;
      }

      // 다른 템플릿 클릭
      selectedTemplateId = clickedId;
      actionBar.style.display = 'block';

      // 즐겨찾기 버튼 상태 갱신
      // TODO: 실제 DB 값에 따라 업데이트 필요
      favoriteBtn.textContent = isFavorite ? '즐겨찾기 해제' : '즐겨찾기';
    });
  });

  // --- 내용 확인 / 수정 ---
  editBtn.addEventListener('click', () => {
    if (!selectedTemplateId) return;
      window.location.href = `editor.php?id=${selectedTemplateId}`;
  });


  // --- 즐겨찾기 토글 ---
  favoriteBtn.addEventListener('click', () => {
    if (!selectedTemplateId) return;

    // 실제 DB 토글 API 필요
    isFavorite = !isFavorite;
    favoriteBtn.textContent = isFavorite ? '즐겨찾기 해제' : '즐겨찾기';

    fetch(`../../../api/templates/toggle_favorite.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ template_id: selectedTemplateId })
    });
  });

  // --- 휴지통 이동 ---
  trashBtn.addEventListener('click', () => {
    if (!selectedTemplateId) return;
    if (!confirm('해당 템플릿을 휴지통으로 보내시겠습니까?')) return;

    fetch(`../../../api/templates/toggle_trash.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: [selectedTemplateId] })
    }).then(() => location.reload());
  });

});

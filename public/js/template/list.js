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
});

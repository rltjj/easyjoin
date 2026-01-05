<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/site_helper.php';

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

$roleTextMap = [
  'ADMIN'    => '관리자',
  'OPERATOR' => '운영자',
  'STAFF'    => '직원',
  'USER'     => '회원'
];
$roleText = $roleTextMap[$role] ?? '';

$sites = getUserSites($pdo, $userId, $role);

if (!isset($_SESSION['current_site_id']) && !empty($sites)) {
    $_SESSION['current_site_id'] = $sites[0]['id'];
}

$currentSiteId = $_SESSION['current_site_id'] ?? null;
$currentSiteName = '';

foreach ($sites as $s) {
    if ($s['id'] == $currentSiteId) {
        $currentSiteName = $s['site_name'];
        break;
    }
}
?>

<header class="header">
  <div class="page-title">홈</div>

  <div class="profile" id="profileBtn">
    <div class="circle"><?= mb_substr($roleText, 0, 1) ?></div>

    <div class="profile-popup" id="profilePopup">
      <p><strong><?= htmlspecialchars($_SESSION['name']) ?></strong></p>
      <p><?= $roleText ?></p>
      <?php if (!empty($_SESSION['email'])): ?>
        <p><?= htmlspecialchars($_SESSION['email']) ?></p>
      <?php endif; ?>
      <a href="../../api/auth/logout.php">로그아웃</a>
    </div>
  </div>
</header>

<?php if ($role === 'ADMIN'): ?>
  <div class="site-select">
    <form method="post" action="/api/site/change_site.php">
      <select name="site_id" onchange="this.form.submit()">
        <?php foreach ($sites as $site): ?>
          <option value="<?= $site['id'] ?>"
            <?= $site['id'] == $currentSiteId ? 'selected' : '' ?>>
            <?= htmlspecialchars($site['site_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
<?php else: ?>
  <div class="site-name">
    <?= htmlspecialchars($currentSiteName) ?>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('profileBtn');
  const popup = document.getElementById('profilePopup');

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    popup.classList.toggle('active');
  });

  document.addEventListener('click', () => {
    popup.classList.remove('active');
  });
});
</script>

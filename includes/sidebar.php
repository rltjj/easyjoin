<?php
$role = $_SESSION['role'];
?>

<aside class="sidebar">
  <h1 class="logo">
    <a href="home.php">EasyJoin</a>
  </h1>

  <ul class="menu">
    <li><a href="home.php">홈</a></li>
    <li><a href="sign.php">전자서명</a></li>

    <?php if ($role !== 'STAFF'): ?>
      <li><a href="file.php">문서함</a></li>
      <li><a href="trash.php">휴지통</a></li>
    <?php else: ?>
      <li><a href="file.php">문서함</a></li>
    <?php endif; ?>

    <?php if ($role === 'ADMIN'): ?>
      <li class="admin">
        <a href="template.php">템플릿</a>
      </li>
    <?php endif; ?>

    <li><a href="mypage.php">마이페이지</a></li>
    <li><a href="menu.php">매뉴얼</a></li>

    <?php if ($role === 'ADMIN'): ?>
      <li class="admin">
        <a href="manage.php">이지조인 관리자</a>
      </li>
    <?php endif; ?>
  </ul>
</aside>

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: public/html/login.html');
    exit;
}

// echo "<h1>로그인 성공</h1>";
// echo "<p>이름: {$_SESSION['name']}</p>";
// echo "<p>역할: {$_SESSION['role']}</p>";

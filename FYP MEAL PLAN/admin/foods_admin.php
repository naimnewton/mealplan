<?php
session_start();
// Pastikan user adalah admin
if (!isset($_SESSION['logged_in']) || ($_SESSION['type'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../includes/db_connect.php'; //
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kategori Makanan | MyMealPlan</title>
    <link rel="stylesheet" href="../css/foods_admin.css">
    
</head>
<body>

<div class="container">
    <div class="header-box-gradient">
        <h1>Pengurusan Menu Makanan</h1>
        <p>Pilih kategori untuk dikemaskini</p>
    </div>

    <div class="category-container">
        <button class="btn-main-action" onclick="window.location.href='manage_admin.php?category=sarapan'">
            URUS SARAPAN
        </button>

        <button class="btn-main-action" onclick="window.location.href='manage_admin.php?category=tengahari'">
            URUS MAKAN TENGAHARI
        </button>

        <button class="btn-main-action" onclick="window.location.href='manage_admin.php?category=malam'">
            URUS MAKAN MALAM
        </button>
    </div>

    <div class="nav-actions-center">
    <a href="admin_dashboard.php" class="btn-link-back">⬅ Kembali ke Dashboard</a>
</div>
</div>

</body>
</html>
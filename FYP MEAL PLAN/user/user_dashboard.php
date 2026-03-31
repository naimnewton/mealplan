<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../database/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'user') {
    header("Location: ../authentification/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// REPAIR SQL: JOIN untuk tarik nama dari user_input
$sql = "SELECT ui.name, u.last_login FROM users u 
        LEFT JOIN user_input ui ON u.Id = ui.user_id 
        WHERE u.Id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$display_name = $user_data['name'] ?? "Pengguna";
$last_login = $user_data['last_login'] ?? "Baru Sahaja";
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MyMealPlan</title>
    <link rel="stylesheet" href="../css/user_dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <header class="header-user">
        <img src="https://static.vecteezy.com/system/resources/previews/005/544/718/original/profile-icon-design-free-vector.jpg" class="profile-img" alt="User Avatar">
        <div class="user-info">
            <h1>Selamat Datang, <span><?= htmlspecialchars($display_name) ?></span></h1>
            <p>Log masuk terakhir: <?= htmlspecialchars($last_login) ?></p>
        </div>
    </header>

    <div class="menu-grid">
        <div class="menu-card" onclick="window.location.href='profile.php'">
            <h3>Profil</h3>
            <p>Urus maklumat peribadi</p>
        </div>

        <div class="menu-card" onclick="window.location.href='input_user.php'">
            <h3>Sasaran</h3>
            <p>Tetapkan matlamat</p>
        </div>

        <div class="menu-card" onclick="window.location.href='catalogue.php'">
            <h3>Katalog</h3>
            <p>Rekod harian anda</p>
        </div>

        <div class="menu-card" onclick="window.location.href='../authentification/logout.php'">
            <h3>Log Keluar</h3>
            <p>Tamatkan sesi</p>
        </div>
    </div>
</div>

</body>
</html>
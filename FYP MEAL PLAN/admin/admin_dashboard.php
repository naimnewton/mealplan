<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../database/db_connect.php';

// 1. SEMAK LOGIN
if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../authentification/login.php");
    exit;
}

// ================== 2. DATA PROFIL ADMIN ==================
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$adminName = 'Admin';

// Tarik data admin
$stmt = $conn->prepare("SELECT * FROM users WHERE Id = ? LIMIT 1");
$stmt->bind_param("i", $sessionUserId);
$stmt->execute();
$adminRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($adminRow) {
    $adminName = "Admin #" . ($adminRow['Admin_Id'] ?? $sessionUserId);
}

// ================== 3. LOG PENGGUNA TERBARU (DIKEMASKINI) ==================
// Kita tak pakai LEFT JOIN dah. Kita tarik terus dari column 'nama_paparan'
$sqlLogs = "SELECT user_id, nama_paparan, activity, last_login 
            FROM logs 
            ORDER BY last_login DESC LIMIT 10";

$resLogs = $conn->query($sqlLogs);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | MyMealPlan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h1>Dashboard Admin</h1>
    </div>

    <div class="main-content-row">
        <div class="card full-width">
            <h2>Memantau Log Pengguna</h2>
            <div class="table-container">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Nama / Identiti</th> <th>Aktiviti</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resLogs && $resLogs->num_rows > 0): ?>
                            <?php while($row = $resLogs->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_paparan'] ?? 'User #'.$row['user_id']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($row['activity'] ?? '-') ?></td>
                                <td><small><?= isset($row['last_login']) ? date('d/m/y H:i', strtotime($row['last_login'])) : '-' ?></small></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center; padding: 20px;">Tiada log aktiviti dikesan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn" onclick="window.location.href='logs.php'">Lihat Semua Log</button>
        </div>
    </div>

    <div class="sub-content-row">
        <div class="card">
            <h2>Kemaskini Makanan</h2>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 15px;">Urus senarai menu & kalori</p>
            <button class="btn" onclick="window.location.href='foods_admin.php'">Urus Makanan</button>
        </div>

        <div class="card">
            <h2>Kemaskini Aktiviti</h2>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 15px;">Urus jenis senaman & MET</p>
            <button class="btn" onclick="window.location.href='activities_admin.php'">Urus Aktiviti</button>
        </div>
    </div>

    <div class="logout-container">
        <a href="../authentification/logout.php" class="btn-logout">LOG KELUAR SISTEM</a>
    </div>
</div>

</body>
</html>
<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../database/db_connect.php';

// Pastikan user dah login
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../authentification/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// 1. Tarik nama pengguna dari database
$sql_user = "SELECT ui.name 
             FROM users u 
             LEFT JOIN user_input ui ON u.Id = ui.user_id 
             WHERE u.Id = ? 
             LIMIT 1";

$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Ambil sejarah laporan kalori
$sql_history = "SELECT * FROM calorie_report WHERE user_id = ? ORDER BY tarikh DESC";
$stmt_hist = $conn->prepare($sql_history);
$stmt_hist->bind_param("i", $userId);
$stmt_hist->execute();
$history = $stmt_hist->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil & Sejarah | MyMealPlan</title>
    <link rel="stylesheet" href="../css/profile.css?v=<?= time(); ?>">
</head>
<body>

<div class="profile-container">
    <header class="profile-header">
        <h1><?= htmlspecialchars($user_info['name'] ?? 'Pengguna') ?></h1>
        <p>ID Pengguna: #<?= $userId ?></p>
    </header>

    <div class="history-section">
        <h3>SEJARAH LAPORAN (Klik untuk lihat pelan)</h3>
        
        <div class="history-list">
            <?php if($history && $history->num_rows > 0): ?>
                <?php while($row = $history->fetch_assoc()): ?>
                    <a href="result.php?report_id=<?= $row['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="history-card">
                            <div class="hist-date">
                                <span><?= date('d M', strtotime($row['tarikh'])) ?></span>
                                <small><?= date('Y', strtotime($row['tarikh'])) ?></small>
                            </div>
                            
                            <div class="hist-details">
                                <div class="hist-item">
                                    <small>BMI</small>
                                    <strong class="bmi-val"><?= number_format($row['bmi'], 1) ?></strong>
                                </div>
                                <div class="hist-item">
                                    <small>TARGET</small>
                                    <strong class="kcal-val"><?= number_format($row['kalori_disyorkan']) ?> <small>kcal</small></strong>
                                </div>
                            </div>
                            
                            <div style="color: #10b981; font-weight: bold;">➔</div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">Belum ada sejarah laporan dijana.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="nav-footer">
        <a href="user_dashboard.php" class="btn-back">⬅ KEMBALI KE DASHBOARD</a>
    </div>
</div>

</body>
</html>
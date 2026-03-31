<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../authentification/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// --- 1. LOGIK RESET / MULA BARU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kalori_disyorkan']) && !isset($_POST['next_day'])) {
    $_SESSION['hari_pelan'] = 1;
}

// --- 2. AMBIL DATA ASAS (SINKRONISASI TARGET HARI) ---
if (isset($_GET['report_id'])) {
    $report_id = (int)$_GET['report_id'];
    $stmt = $conn->prepare("SELECT cr.*, ui.target_days FROM calorie_report cr 
                            JOIN user_input ui ON cr.user_id = ui.user_id 
                            WHERE cr.id = ? AND cr.user_id = ?");
    $stmt->bind_param("ii", $report_id, $userId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if ($data) {
        $bmi = $data['bmi'];
        $tdee = $data['tdee'];
        $kalori_disyorkan = $data['kalori_disyorkan'];
        $_SESSION['current_target_days'] = (int)$data['target_days'];
    }
} else {
    $bmi = $_POST['bmi'] ?? 0;
    $tdee = $_POST['tdee'] ?? 0;
    $kalori_raw = $_POST['kalori_disyorkan'] ?? 0;
    $kalori_disyorkan = ($kalori_raw < 1200 && $kalori_raw > 0) ? 1200 : $kalori_raw;

    $stmt_days = $conn->prepare("SELECT target_days FROM user_input WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_days->bind_param("i", $userId);
    $stmt_days->execute();
    $res_days = $stmt_days->get_result()->fetch_assoc();
    $_SESSION['current_target_days'] = (int)($res_days['target_days'] ?? 7);
}

$target_days = $_SESSION['current_target_days'] ?? 7;

// --- 3. LOGIK INCREMENT HARI ---
if(!isset($_SESSION['hari_pelan'])) { $_SESSION['hari_pelan'] = 1; }
if(isset($_POST['next_day'])) {
    if ($_SESSION['hari_pelan'] < $target_days) {
        $_SESSION['hari_pelan']++;
    }
}
$is_finished = ($_SESSION['hari_pelan'] >= $target_days);

// --- 4. FUNCTIONS PEMILIHAN MENU ---

function dapatkanMenu($conn, $limit, $cat) {
    $min_range = $limit * 0.65; 
    $sql = "SELECT name, kcal_per_serving, serving_info FROM foods 
            WHERE category = ? AND kcal_per_serving BETWEEN ? AND ? 
            ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdd", $cat, $min_range, $limit);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    if (!$res) {
        $sql_fb = "SELECT name, kcal_per_serving, serving_info FROM foods 
                   WHERE category = ? AND kcal_per_serving <= ? 
                   ORDER BY kcal_per_serving DESC LIMIT 1";
        $stmt_fb = $conn->prepare($sql_fb);
        $stmt_fb->bind_param("sd", $cat, $limit);
        $stmt_fb->execute();
        $res = $stmt_fb->get_result()->fetch_assoc();
    }
    return $res;
}

function dapatkanSnek($conn, $baki) {
    if ($baki < 100) return null; 
    $sql = "SELECT name, kcal_per_serving, serving_info FROM foods 
            WHERE category = 'snek' AND kcal_per_serving <= ? 
            ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $baki);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function dapatkanAktiviti($conn) {
    $sql = "SELECT name, met_value FROM activities ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : ['name' => 'Berjalan Santai', 'met_value' => '2.5'];
}

// Pecahan Menu & Snek (25% - 45% - 30%)
$limit_s = $kalori_disyorkan * 0.25;
$sarapan = dapatkanMenu($conn, $limit_s, 'sarapan');
$snek_s = dapatkanSnek($conn, $limit_s - ($sarapan['kcal_per_serving'] ?? 0));

$limit_t = $kalori_disyorkan * 0.45;
$tengahari = dapatkanMenu($conn, $limit_t, 'tengahari');
$snek_t = dapatkanSnek($conn, $limit_t - ($tengahari['kcal_per_serving'] ?? 0));

$limit_m = $kalori_disyorkan * 0.30;
$malam = dapatkanMenu($conn, $limit_m, 'malam');
$snek_m = dapatkanSnek($conn, $limit_m - ($malam['kcal_per_serving'] ?? 0));

$activities = dapatkanAktiviti($conn);

// Total Kalori Sebenar (Menu + Snek)
$total_makanan = ($sarapan['kcal_per_serving'] ?? 0) + ($snek_s['kcal_per_serving'] ?? 0) + 
                 ($tengahari['kcal_per_serving'] ?? 0) + ($snek_t['kcal_per_serving'] ?? 0) + 
                 ($malam['kcal_per_serving'] ?? 0) + ($snek_m['kcal_per_serving'] ?? 0);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Keputusan Pelan | MyMealPlan</title>
    <link rel="stylesheet" href="../css/result.css?v=<?= time(); ?>">
</head>
<body>

<div class="container">
    <header class="header">
        <h2>HARI <span><?= $_SESSION['hari_pelan'] ?> / <?= $target_days ?></span></h2>
        <div class="stat-info">
            <span><strong>BMI:</strong> <?= number_format($bmi, 1) ?></span>
            <span><strong>TDEE:</strong> <?= number_format($tdee) ?> kcal</span>
            <span class="target-val"><strong>Target:</strong> <?= number_format($kalori_disyorkan) ?> kcal</span>
        </div>
        <div style="margin-top:10px; font-size: 0.8rem; color: #10b981;">
            Jumlah Kalori Menu Hari Ini: <strong><?= number_format($total_makanan) ?> kcal</strong>
        </div>
    </header>

    <div class="grid">
        <div class="card">
            <span class="meal-tag breakfast">Sarapan</span>
            <h3><?= htmlspecialchars($sarapan['name'] ?? 'Tiada Data') ?></h3>
            <span class="serving-info"><?= htmlspecialchars($sarapan['serving_info'] ?? '-') ?></span>
            <p class="kcal-val"><?= $sarapan['kcal_per_serving'] ?? 0 ?> kcal</p>
            <?php if ($snek_s): ?>
                <div class="snek-box" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.2);">
                    <small style="color: #10b981;">+ TAMBAHAN</small>
                    <h4 style="margin: 5px 0; font-size: 0.9rem;"><?= htmlspecialchars($snek_s['name']) ?></h4>
                    <p style="font-size: 0.8rem; opacity: 0.7;"><?= $snek_s['kcal_per_serving'] ?> kcal</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <span class="meal-tag lunch">Tengahari</span>
            <h3><?= htmlspecialchars($tengahari['name'] ?? 'Tiada Data') ?></h3>
            <span class="serving-info"><?= htmlspecialchars($tengahari['serving_info'] ?? '-') ?></span>
            <p class="kcal-val"><?= $tengahari['kcal_per_serving'] ?? 0 ?> kcal</p>
            <?php if ($snek_t): ?>
                <div class="snek-box" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.2);">
                    <small style="color: #10b981;">+ TAMBAHAN</small>
                    <h4 style="margin: 5px 0; font-size: 0.9rem;"><?= htmlspecialchars($snek_t['name']) ?></h4>
                    <p style="font-size: 0.8rem; opacity: 0.7;"><?= $snek_t['kcal_per_serving'] ?> kcal</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <span class="meal-tag dinner">Malam</span>
            <h3><?= htmlspecialchars($malam['name'] ?? 'Tiada Data') ?></h3>
            <span class="serving-info"><?= htmlspecialchars($malam['serving_info'] ?? '-') ?></span>
            <p class="kcal-val"><?= $malam['kcal_per_serving'] ?? 0 ?> kcal</p>
            <?php if ($snek_m): ?>
                <div class="snek-box" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.2);">
                    <small style="color: #10b981;">+ TAMBAHAN</small>
                    <h4 style="margin: 5px 0; font-size: 0.9rem;"><?= htmlspecialchars($snek_m['name']) ?></h4>
                    <p style="font-size: 0.8rem; opacity: 0.7;"><?= $snek_m['kcal_per_serving'] ?> kcal</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="exercise-section">
        <span class="intensity-badge">CADANGAN AKTIVITI</span>
        <h3><?= htmlspecialchars($activities['name']) ?></h3>
        <p>Lakukan senaman ini untuk mengimbangi sasaran kalori anda.</p>
        <div class="duration-box">30 - 45 MINIT</div>
        <p class="met-val">Estimasi MET: <?= $activities['met_value'] ?></p>
    </div>

    <div class="action-buttons">
        <?php if (!$is_finished): ?>
            <form method="POST">
                <input type="hidden" name="bmi" value="<?= $bmi ?>">
                <input type="hidden" name="tdee" value="<?= $tdee ?>">
                <input type="hidden" name="kalori_disyorkan" value="<?= $kalori_disyorkan ?>">
                <input type="hidden" name="next_day" value="1">
                <button type="submit" class="btn">Menu Hari Ke-<?= $_SESSION['hari_pelan'] + 1 ?> ➔</button>
            </form>
        <?php else: ?>
            <div class="finish-banner">✅ Sasaran <?= $target_days ?> hari selesai!</div>
            <a href="input_user.php" class="btn">Bina Pelan Baru</a>
        <?php endif; ?>
        <a href="user_dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
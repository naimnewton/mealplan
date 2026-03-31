<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../database/db_connect.php'; 

if (!isset($_SESSION['logged_in'])) { 
    header("Location: ../authentification/login.php"); 
    exit; 
}

$status_msg = "";
$is_valid = false;
$userId = $_SESSION['user_id']; 

$query_user = $conn->prepare("SELECT nama, umur, tinggi, berat FROM users WHERE Id = ?");
$query_user->bind_param("i", $userId);
$query_user->execute();
$res_user = $query_user->get_result();
$user_data = $res_user->fetch_assoc();

$nama   = isset($user_data['nama']) ? trim($user_data['nama']) : "";
$umur   = $user_data['umur']   ?? "";
$tinggi = $user_data['tinggi'] ?? "";
$berat  = $user_data['berat']  ?? "";
$berat_sasaran = $tempoh_sasaran = "";

if (isset($_POST['submit_input'])) {
    $nama           = $_POST['nama'];
    $umur           = $_POST['umur'];
    $tinggi         = $_POST['tinggi'];
    $berat          = $_POST['berat'];
    $berat_sasaran  = $_POST['berat_sasaran'];
    $tempoh_sasaran = $_POST['tempoh_sasaran'];
    $tahap_aktif    = $_POST['tahap_aktif'];

    $berat_beza = abs($berat - $berat_sasaran); 
    $had_maksimum = ($tempoh_sasaran / 7) * 2; 

    if (empty($nama)) {
        $status_msg = "Sila masukkan Nama Penuh anda.";
    } else if ($berat_sasaran < 30) {
        $status_msg = "Berat sasaran terlalu rendah (min 30kg).";
    } else if ($tempoh_sasaran < 7) {
        $status_msg = "Tempoh sasaran mestilah sekurang-kurangnya 7 hari.";
    } else if ($berat_beza > $had_maksimum) {
        $status_msg = "Sasaran tidak munasabah! Had selamat adalah 2kg seminggu.";
    } else {
        $tinggi_m = $tinggi / 100;
        $bmi = $berat / ($tinggi_m * $tinggi_m);
        $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) + 5;
        $tdee = $bmr * $tahap_aktif; 
        
        $weight_diff = $berat_sasaran - $berat;
        $kalori_disyorkan = $tdee + (($weight_diff * 7700) / $tempoh_sasaran);

        if ($kalori_disyorkan < 1200) {
            $status_msg = "Sasaran terlalu ekstrem! Sila panjangkan tempoh hari.";
        } else {
            $sql_ui = "INSERT INTO user_input (user_id, name, age, height, weight, weight_target, target_days) 
                       VALUES (?, ?, ?, ?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE name=?, age=?, height=?, weight=?, weight_target=?, target_days=?";
            $stmt_ui = $conn->prepare($sql_ui);
            $stmt_ui->bind_param("isidddisidddi", $userId, $nama, $umur, $tinggi, $berat, $berat_sasaran, $tempoh_sasaran,
                                $nama, $umur, $tinggi, $berat, $berat_sasaran, $tempoh_sasaran);
            $stmt_ui->execute();

            $update_name = $conn->prepare("UPDATE users SET nama = ? WHERE Id = ? AND (nama IS NULL OR nama = '')");
            $update_name->bind_param("si", $nama, $userId);
            $update_name->execute();

            $stmt_cr = $conn->prepare("INSERT INTO calorie_report (user_id, bmi, bmr, tdee, kalori_disyorkan, tarikh) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt_cr->bind_param("idddd", $userId, $bmi, $bmr, $tdee, $kalori_disyorkan);
            
            if($stmt_cr->execute()){
                $is_valid = true;
                $status_msg = "✅ Pelan anda telah dijana!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Sasaran | MyMealPlan</title>
    <link rel="stylesheet" href="../css/input_user.css?v=<?= time(); ?>">
</head>
<body>

<div class="main-wrapper">
    <header class="header-card">
        <h1>SASARAN <span>PELAN</span></h1>
    </header>

    <div class="form-container">
        <?php if ($status_msg !== ""): ?>
            <div class="alert-msg"><?= $status_msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-grid">
                <div class="group">
                    <label>Nama Penuh</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($nama); ?>" 
                    placeholder="Masukkan nama penuh" required 
                    <?= (!empty($nama)) ? 'readonly class="readonly-input"' : ''; ?>>
                </div>
                <div class="group">
                    <label>Umur (Tahun)</label>
                    <input type="number" name="umur" value="<?= htmlspecialchars($umur); ?>" required>
                </div>
                <div class="group">
                    <label>Tinggi (cm)</label>
                    <input type="number" step="0.1" name="tinggi" value="<?= htmlspecialchars($tinggi); ?>" required>
                </div>
                <div class="group">
                    <label>Berat Semasa (kg)</label>
                    <input type="number" step="0.1" name="berat" value="<?= htmlspecialchars($berat); ?>" required>
                </div>
                
                <div class="group">
                    <label>Kekerapan Senaman</label>
                    <select name="tahap_aktif" required>
                        <option value="1.2">Jarang Senaman (Duduk Sahaja)</option>
                        <option value="1.375" selected>Aktif Ringan (1-3 hari/seminggu)</option>
                        <option value="1.55">Aktif Sederhana (3-5 hari/seminggu)</option>
                        <option value="1.725">Sangat Aktif (6-7 hari/seminggu)</option>
                        <option value="1.9">Atlet / Kerja Berat</option>
                    </select>
                </div>

                <div class="group">
                    <label>Berat Sasaran (kg)</label>
                    <input type="number" step="0.1" name="berat_sasaran" value="<?= htmlspecialchars($berat_sasaran); ?>" required>
                </div>
                <div class="group">
                    <label>Tempoh Sasaran (Hari)</label>
                    <input type="number" name="tempoh_sasaran" value="<?= htmlspecialchars($tempoh_sasaran); ?>" placeholder="Min 7 hari" required>
                </div>
            </div>
            <button type="submit" name="submit_input" class="btn-save">JANA PELAN SEKARANG</button>
        </form>

        <?php if (!$is_valid): ?>
        <div class="dashboard-footer">
            <a href="user_dashboard.php" class="back-link">⬅ KEMBALI KE DASHBOARD</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="reference-section">
        <div class="info-card info-bmi">
            <h4>📊 KLASIFIKASI BMI</h4>
            <table class="bmi-table">
                <thead>
                    <tr><th>BMI</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr class="underweight"><td>Bawah 18.5</td><td>Kurang Berat</td></tr>
                    <tr class="normal"><td>18.5 - 24.9</td><td>Normal (Ideal)</td></tr>
                    <tr class="overweight"><td>25.0 - 29.9</td><td>Lebih Berat</td></tr>
                    <tr class="obese"><td>30.0 ke atas</td><td>Obesiti</td></tr>
                </tbody>
            </table>
        </div>

        <div class="info-card info-health">
            <h4>💡 INFO KESIHATAN</h4>
            <p>Kadar perubahan berat badan yang sihat (WHO):</p>
            <ul class="health-list">
                <li>Ideal: 0.5kg - 1.0kg seminggu.</li>
                <li>Had selamat: Maksimum 2kg seminggu.</li>
                <li>Min pengambilan harian: 1200 kcal.</li>
            </ul>
        </div>
    </div>

    <?php if ($is_valid): ?>
    <div class="results-section">
        <div class="results-grid">
            <div class="res-card card-bmi">
                <h4>BMI</h4>
                <div class="val"><?= number_format($bmi, 1) ?></div>
            </div>
            <div class="res-card card-bmr">
                <h4>BMR</h4>
                <div class="val"><?= number_format($bmr, 0) ?></div>
            </div>
            <div class="res-card card-tdee">
                <h4>TDEE</h4>
                <div class="val"><?= number_format($tdee, 0) ?></div>
            </div>
            <div class="res-card highlighted">
                <h4>SASARAN HARIAN</h4>
                <div class="val"><?= number_format($kalori_disyorkan, 0) ?> <span class="unit">kcal</span></div>
            </div>
        </div>
        <div class="action-footer">
            <form action="result.php" method="POST">
                <input type="hidden" name="bmi" value="<?= $bmi ?>">
                <input type="hidden" name="tdee" value="<?= $tdee ?>">
                <input type="hidden" name="kalori_disyorkan" value="<?= $kalori_disyorkan ?>">
                <button type="submit" class="btn-next">LIHAT PELAN MAKANAN ➔</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
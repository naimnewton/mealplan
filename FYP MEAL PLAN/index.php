<?php
session_start();
// 1. Sambungan database (Keluar folder user, masuk folder database)
include '../database/db_connect.php';

// 2. Keselamatan
if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'user') {
    header("Location: ../authentification/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kira Pelan Kesihatan</title>
    <link rel="stylesheet" href="../css/style.css"> </head>
<body>

<header>
    <h1>Sistem Pelan Aktiviti dan Pemakanan</h1>
    <p>Bantu anda capai berat ideal mengikut keperluan rakyat Malaysia 🇲🇾</p>
</header>

<section class="form-section">
    <form method="POST" action="">
        <label>Umur:</label>
        <input type="number" name="age" required><br>

        <label>Jantina:</label>
        <select name="gender" required>
            <option value="Male">Lelaki</option>
            <option value="Female">Perempuan</option>
        </select><br>

        <label>Tinggi (cm):</label>
        <input type="number" step="0.1" name="height" required><br>

        <label>Berat (kg):</label>
        <input type="number" step="0.1" name="weight" required><br>

        <label>Tahap Aktiviti:</label>
        <select name="activity" required>
            <option value="1.2">Tidak aktif</option>
            <option value="1.375">Aktif ringan (1–3 kali/minggu)</option>
            <option value="1.55">Aktif sederhana (3–5 kali/minggu)</option>
            <option value="1.725">Sangat aktif (6–7 kali/minggu)</option>
        </select><br>

        <label>Berat Sasaran (kg):</label>
        <input type="number" name="target_weight" step="0.1" required>

        <label>Tempoh Sasaran (hari):</label>
        <input type="number" name="duration_days" required>

        <button type="submit" name="calculate">Kira Pelan Saya</button>
    </form>
</section>

<?php
if (isset($_POST['calculate'])) {
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $act_value = $_POST['activity'];

    // --- LOGIK KIRAAN (Sama macam asal kau) ---
    $bmi = $weight / pow($height / 100, 2);

    if ($gender == "Male") {
        $bmr = 88.36 + (13.4 * $weight) + (4.8 * $height) - (5.7 * $age);
    } else {
        $bmr = 447.6 + (9.2 * $weight) + (3.1 * $height) - (4.3 * $age);
    }

    $tdee = $bmr * $act_value;

    // Klasifikasi
    if ($bmi < 18.5) {
        $category = "Kurus"; $meal = "Tinggi protein..."; $activity_plan = "Senaman ringan...";
    } elseif ($bmi < 23) {
        $category = "Normal"; $meal = "Diet seimbang..."; $activity_plan = "Senaman sederhana...";
    } elseif ($bmi < 27.5) {
        $category = "Berlebihan berat"; $meal = "Kurang goreng..."; $activity_plan = "Kardio...";
    } else {
        $category = "Obes"; $meal = "Defisit kalori..."; $activity_plan = "Intensiti sederhana...";
    }

    // --- SIMPAN KE DATABASE ---
    // Pastikan table 'calorie_report' atau 'records' kau ada column yang betul
    $stmt = $conn->prepare("INSERT INTO calorie_report (user_id, bmi, bmr, tdee, kalori_disyorkan, tarikh) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("idddd", $userId, $bmi, $bmr, $tdee, $tdee); // Contoh simpan TDEE sebagai kalori disyorkan
    
    if ($stmt->execute()) {
        echo "<script>alert('Berjaya dikira!'); window.location='result.php';</script>";
    }
}
?>

<footer>
    <p>© 2025 Sistem Pelan Aktiviti Dan Pemakanan - Dibangunkan oleh Naim</p>
</footer>

</body>
</html> 
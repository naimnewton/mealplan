<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// 1. KESELAMATAN: Pastikan hanya admin boleh akses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['type'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../database/db_connect.php';

$msg = "";
$msg_type = ""; 

// 2. PROSES TAMBAH AKTIVITI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $intensity = (int)$_POST['intensity'];
    $met_value = (float)$_POST['met_value'];

    if ($name === '' || $intensity <= 0 || $met_value <= 0) {
        $msg = "Sila isi maklumat dengan betul.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO activities (name, intensity, met_value) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $name, $intensity, $met_value);
        if($stmt->execute()){
            $msg = "Aktiviti berjaya ditambah!";
            $msg_type = "success";
        } else {
            $msg = "Ralat: " . $conn->error;
            $msg_type = "error";
        }
        $stmt->close();
    }
}

// 3. PROSES HAPUS AKTIVITI
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        header("Location: activities_admin.php?msg=deleted");
        exit;
    }
    $stmt->close();
}

// 4. AMBIL SENARAI AKTIVITI
$activities = $conn->query("SELECT * FROM activities ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Aktiviti | MyMealPlan Admin</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="stylesheet" href="../css/activities_admin.css">
    <link rel="stylesheet" href="../css/logs.css">
    <style>
        /* Tambahan style sikit bagi nampak solid macam kau nak */
        .met-label {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
        }
        .alert.error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fff; }
        .alert.success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #fff; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    
    <div class="dashboard-header">
        <h1>Pengurusan <span>Aktiviti</span></h1>
        <p>Kawal selia senarai senaman dan nilai intensiti (MET)</p>
    </div>

    <div class="form-container-centered">
        <div class="card form-card">
            <h2>Tambah Aktiviti Baru</h2>

            <?php if ($msg || isset($_GET['msg'])): ?>
                <div class="alert <?= $msg_type ?: 'success' ?>" style="padding:15px; border-radius:10px; margin-bottom:20px;">
                    <?= $msg ?: ($_GET['msg'] == 'deleted' ? "✅ Aktiviti telah dipadam." : "") ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Nama Aktiviti</label>
                    <input type="text" name="name" placeholder="Contoh: Berenang, Berbasikal" required>
                </div>

                <div class="input-row" style="display: flex; gap: 20px; margin-top: 15px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Tahap Intensiti (1-10)</label>
                        <input type="number" name="intensity" placeholder="Contoh: 5" required>
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Nilai MET</label>
                        <input type="number" step="0.1" name="met_value" placeholder="Contoh: 3.5" required>
                    </div>
                </div>

                <button type="submit" name="add_activity" class="btn" style="width: 100%; margin-top: 20px; background: #10b981; color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                    SIMPAN AKTIVITI
                </button>
            </form>
        </div>
    </div>

    <div class="table-container" style="margin-top: 30px;">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Senarai Aktiviti Semasa</h2>
            
            <table class="log-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #334155;">
                        <th width="60" style="padding: 10px;">No.</th>
                        <th style="padding: 10px;">Nama Aktiviti</th>
                        <th style="padding: 10px;">Intensiti</th>
                        <th style="padding: 10px;">Nilai MET</th>
                        <th style="text-align: center; padding: 10px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($activities && $activities->num_rows > 0): 
                        $no = 1; 
                        while ($row = $activities->fetch_assoc()): 
                    ?>
                        <tr style="border-bottom: 1px solid #334155;">
                            <td style="padding: 15px;"><?= $no++ ?>.</td>
                            <td style="padding: 15px; color: #fff; font-weight: 600;">
                                <?= htmlspecialchars($row['name']) ?>
                            </td>
                            <td style="padding: 15px;"><?= $row['intensity'] ?> / 10</td>
                            <td style="padding: 15px;">
                                <span class="met-label"><?= $row['met_value'] ?></span>
                            </td>
                            <td style="text-align: center; padding: 15px;">
                                <a href="activities_admin.php?delete=<?= $row['id'] ?>" 
                                   onclick="return confirm('Padam aktiviti ini?')" 
                                   style="color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 0.85rem;">
                                   [ PADAM ]
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">Tiada aktiviti dalam pangkalan data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <a href="admin_dashboard.php" class="btn-back" style="text-decoration: none; color: #94a3b8; font-weight: bold; border: 1px solid #334155; padding: 10px 20px; border-radius: 50px;">
            ⬅ Kembali ke Dashboard
        </a>
    </div>

</div>

</body>
</html>
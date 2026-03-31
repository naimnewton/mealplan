<?php
session_start();
include '../database/db_connect.php';

// Check login admin (Optional: Ikut logik admin kau)
// if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$status_msg = "";

// 1. LOGIK TAMBAH AKTIVITI
if (isset($_POST['add_activity'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $met = mysqli_real_escape_string($conn, $_POST['met_value']);

    $sql = "INSERT INTO activities (name, met_value) VALUES ('$name', '$met')";
    if ($conn->query($sql)) {
        $status_msg = "✅ Aktiviti berjaya ditambah!";
    } else {
        $status_msg = "❌ Gagal: " . $conn->error;
    }
}

// 2. LOGIK PADAM AKTIVITI
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM activities WHERE id = $id");
    header("Location: manage_activities.php?msg=deleted");
    exit;
}

// 3. TARIK SENARAI AKTIVITI
$activities = $conn->query("SELECT * FROM activities ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Aktiviti | MyMealPlan Admin</title>
    <link rel="stylesheet" href="../css/manage_activities.css?v=<?= time(); ?>">
</head>
<body>

<div class="admin-wrapper">
    <div class="sidebar">
        <h2>ADMIN <span>PANEL</span></h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_foods.php">Urus Makanan</a>
        <a href="manage_activities.php" class="active">Urus Aktiviti</a>
        <a href="../authentification/logout.php" style="color: #ef4444;">Log Keluar</a>
    </div>

    <div class="main-content">
        <header class="main-header">
            <h1>Pengurusan <span>Aktiviti Senaman</span></h1>
            <p>Tambah dan urus nilai MET untuk aktiviti pengguna</p>
        </header>

        <?php if ($status_msg || isset($_GET['msg'])): ?>
            <div class="alert">
                <?= $status_msg ? $status_msg : "✅ Aktiviti telah dipadam." ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Tambah Aktiviti Baru</h3>
            <form method="POST" class="add-form">
                <div class="form-group">
                    <label>Nama Aktiviti</label>
                    <input type="text" name="name" placeholder="cth: Berjoging, Berenang" required>
                </div>
                <div class="form-group">
                    <label>Nilai MET</label>
                    <input type="number" step="0.1" name="met_value" placeholder="cth: 7.0" required>
                </div>
                <button type="submit" name="add_activity" class="btn-add">SIMPAN AKTIVITI</button>
            </form>
        </div>

        <div class="admin-card">
            <h3>Senarai Aktiviti Semasa</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Aktiviti</th>
                        <th>Nilai MET</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activities->num_rows > 0): ?>
                        <?php while($row = $activities->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><span class="met-badge"><?= $row['met_value'] ?></span></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn-del" onclick="return confirm('Padam aktiviti ini?')">Padam</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">Tiada aktiviti dijumpai.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
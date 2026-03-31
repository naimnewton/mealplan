<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../authentification/login.php");
    exit;
}

$sql = "SELECT * FROM logs ORDER BY last_login DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Rekod Jejak Aktiviti</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="stylesheet" href="../css/logs.css?v=<?= time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        
        <div class="back-container">
            <a href="admin_dashboard.php" class="btn-back">⬅ Kembali ke Dashboard</a>
        </div>
        
        <h1 class="logs-title">Log Pengguna</h1>
        
        <div class="card full-width">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nama / Identiti</th>
                        <th>Aktiviti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="time-col">
                                <?= date('d/m/Y H:i', strtotime($row['last_login'])) ?>
                            </td>
                            
                            <td class="name-col">
                                <strong><?= htmlspecialchars($row['nama_paparan'] ?? 'Tiada Nama') ?></strong><br>
                                <span class="id-subtext">DB_ID: <?= $row['user_id'] ?></span>
                            </td>
                            
                            <td class="activity-col">
                                <?= htmlspecialchars($row['activity']) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="no-data">Tiada rekod aktiviti dijumpai.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
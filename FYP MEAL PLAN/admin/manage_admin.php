<?php
session_start();
include '../includes/db_connect.php';

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'sarapan';

$sql = "SELECT id, name, kcal_per_serving FROM foods WHERE category = '$category' ORDER BY name ASC";
$result = $conn->query($sql);

$display_title = strtoupper($category);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Urus Menu <?= $display_title ?></title>
    <link rel="stylesheet" href="../css/manage_admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="dashboard-wrapper">
    <div class="header-box-gradient">
        <h1>PENGURUSAN MENU: <?= $display_title ?></h1>
        <p>Kategori: <?= $category ?></p>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: white; margin: 0;">Senarai Hidangan</h2>
            <button class="btn-add" onclick="window.location.href='form_food.php?category=<?= $category ?>'">
                + Tambah <?= ucfirst($category) ?> Baru
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Makanan</th>
                        <th>Kalori (kcal)</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['kcal_per_serving']) ?> kcal</td>
                            <td class="action-links">
                                <a href="form_food.php?id=<?= $row['id'] ?>" class="edit-link">Edit</a>
                                <a href="delete_food.php?id=<?= $row['id'] ?>" class="delete-link" onclick="return confirm('Padam?')">Padam</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding: 40px;">
                                Tiada data untuk kategori <strong><?= $category ?></strong>. 
                                <br><small>Sila masukkan menu baharu.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="btn-back-container">
        <button class="btn-link-back" onclick="window.location.href='foods_admin.php'">
            ⬅ Kembali ke Kategori
        </button>
    </div>
</div>

</body>
</html>
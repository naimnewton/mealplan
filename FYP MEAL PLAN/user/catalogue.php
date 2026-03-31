<?php
include '../database/db_connect.php';

// 1. Ambil data mengikut kategori
$sarapan   = $conn->query("SELECT * FROM foods WHERE category = 'sarapan' ORDER BY name ASC");
$tengahari = $conn->query("SELECT * FROM foods WHERE category = 'tengahari' ORDER BY name ASC");
$malam     = $conn->query("SELECT * FROM foods WHERE category = 'malam' ORDER BY name ASC");
$snek      = $conn->query("SELECT * FROM foods WHERE category = 'snek' ORDER BY name ASC");

// Ambil data aktiviti
$query_activities = "SELECT * FROM activities ORDER BY name ASC";
$result_activities = $conn->query($query_activities);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Sihat | MyMealPlan</title>
    <link rel="stylesheet" href="../css/catalogue.css?v=<?= time(); ?>">
</head>
<body>

<div class="catalogue-wrapper">
    <header class="cat-header">
        <h1>Katalog <span>Sihat</span></h1>
        <p>Senarai rujukan makanan dan aktiviti anda</p>
    </header>

    <div class="section-divider">🍱 PILIHAN MAKANAN</div>
    
    <div class="cat-grid">
        <div class="cat-card">
            <div class="cat-type">SARAPAN</div>
            <div class="food-list-container">
                <?php while($food = $sarapan->fetch_assoc()): ?>
                <div class="food-item">
                    <span class="f-name"><?= htmlspecialchars($food['name']) ?></span>
                    <span class="f-kcal"><?= $food['kcal_per_serving'] ?> kcal</span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="cat-card">
            <div class="cat-type">MAKAN TENGAHARI</div>
            <div class="food-list-container">
                <?php while($food = $tengahari->fetch_assoc()): ?>
                <div class="food-item">
                    <span class="f-name"><?= htmlspecialchars($food['name']) ?></span>
                    <span class="f-kcal"><?= $food['kcal_per_serving'] ?> kcal</span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="cat-card">
            <div class="cat-type">MAKAN MALAM</div>
            <div class="food-list-container">
                <?php while($food = $malam->fetch_assoc()): ?>
                <div class="food-item">
                    <span class="f-name"><?= htmlspecialchars($food['name']) ?></span>
                    <span class="f-kcal"><?= $food['kcal_per_serving'] ?> kcal</span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="cat-card">
            <div class="cat-type">SNEK / TAMBAHAN</div>
            <div class="food-list-container">
                <?php if($snek->num_rows > 0): ?>
                    <?php while($food = $snek->fetch_assoc()): ?>
                    <div class="food-item">
                        <span class="f-name"><?= htmlspecialchars($food['name']) ?></span>
                        <span class="f-kcal"><?= $food['kcal_per_serving'] ?> kcal</span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#888; font-size:0.8rem; padding:10px;">Tiada data snek.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section-divider" style="margin-top: 50px;">🏃 SENARAI AKTIVITI</div>
    <div class="activity-box">
        <?php while($act = $result_activities->fetch_assoc()): ?>
        <div class="act-item">
            <span class="a-name"><?= htmlspecialchars($act['name']) ?></span>
            <span class="a-met">MET: <?= $act['met_value'] ?></span>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="back-nav">
        <a href="user_dashboard.php" class="btn-back">⬅ Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
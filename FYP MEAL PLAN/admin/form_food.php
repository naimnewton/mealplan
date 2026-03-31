<?php
session_start();
include '../includes/db_connect.php';

// Ambil ID dan Kategori dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'sarapan';

$isEdit = !empty($id);
$name = "";
$kcal = "";
$serving_info = ""; // Variable baru untuk menyimpan info set makanan

// Jika mod EDIT, tarik data sedia ada dari database
if ($isEdit) {
    $sql_fetch = "SELECT * FROM foods WHERE id = '$id'";
    $res = $conn->query($sql_fetch);
    if ($row = $res->fetch_assoc()) {
        $name = $row['name'];
        $kcal = $row['kcal_per_serving'];
        $category = $row['category'];
        $serving_info = $row['serving_info']; // Ambil data serving_info
    }
}

// Proses simpan data (Tambah atau Kemaskini)
if (isset($_POST['save'])) {
    $f_name = mysqli_real_escape_string($conn, $_POST['name']);
    $f_kcal = mysqli_real_escape_string($conn, $_POST['kcal']);
    $f_cat  = mysqli_real_escape_string($conn, $_POST['category']);
    $f_info = mysqli_real_escape_string($conn, $_POST['serving_info']); // Ambil input serving_info dari form
    
    // Gunakan admin_id dari session, default kepada 1 jika tiada
    $admin_id = $_SESSION['admin_id'] ?? 1; 

    if ($isEdit) {
        // Query UPDATE termasuk serving_info
        $query = "UPDATE foods SET 
                    name='$f_name', 
                    kcal_per_serving='$f_kcal', 
                    category='$f_cat', 
                    serving_info='$f_info' 
                  WHERE id='$id'";
        $msg = "Menu dikemaskini!";
    } else {
        // Query INSERT termasuk Admin_Id dan serving_info
        $query = "INSERT INTO foods (name, kcal_per_serving, category, Admin_Id, serving_info) 
                  VALUES ('$f_name', '$f_kcal', '$f_cat', '$admin_id', '$f_info')";
        $msg = "Menu ditambah!";
    }

    if ($conn->query($query)) {
        echo "<script>alert('$msg'); window.location.href='manage_admin.php?category=$f_cat';</script>";
    } else {
        echo "<script>alert('Ralat: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Menu</title>
    <link rel="stylesheet" href="../css/form_food.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="dashboard-wrapper">
    <div class="header-box-gradient">
        <h1><?= $isEdit ? 'KEMASKINI' : 'TAMBAH' ?> MENU</h1>
        <p>Kategori: <strong><?= strtoupper($category) ?></strong></p>
    </div>

    <div class="card">
        <form action="" method="POST">
            <input type="hidden" name="category" value="<?= $category ?>">

            <div class="form-group">
                <label>NAMA MAKANAN / SET</label>
                <input type="text" name="name" class="form-control" value="<?= $name ?>" placeholder="Contoh: Set Ikan Bakar Sihat" required>
            </div>

            <div class="form-group">
                <label>JUMLAH KALORI SET (kcal)</label>
                <input type="number" name="kcal" class="form-control" value="<?= $kcal ?>" placeholder="Contoh: 550" required>
            </div>

            <div class="form-group">
                <label>KANDUNGAN SET (SERVING INFO)</label>
                <textarea name="serving_info" class="form-control" rows="4" placeholder="Contoh: 1 senduk nasi putih (150g) + 1 ekor ikan kembung(45g) + sayur bayam(100g)" required><?= $serving_info ?></textarea>
            </div>

            <button type="submit" name="save" class="btn-save">
                <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Menu' ?>
            </button>
        </form>
    </div>

    <div class="btn-back-container">
        <a href="manage_admin.php?category=<?= $category ?>" class="btn-link-cancel">BATAL / KEMBALI</a>
    </div>
</div>

</body>
</html>
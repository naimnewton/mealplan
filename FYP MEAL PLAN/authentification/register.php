<?php
session_start();
include '../database/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $id       = trim($_POST['id']);
    $password = $_POST['pwd'];
    // Ambil data tambahan dari form
    $nama     = trim($_POST['nama']);
    $umur     = intval($_POST['umur']);
    $tinggi   = intval($_POST['tinggi']);
    $berat    = floatval($_POST['berat']);

    if (!ctype_digit($id)) {
        echo "<script>alert('No matrik mesti nombor sahaja.');</script>";
    } else {
        $check = $conn->prepare("SELECT Id FROM users WHERE Id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('No matrik sudah digunakan.');</script>";
        } else {
            if (strlen($password) < 8) {
                echo "<script>alert('Kata laluan mesti sekurang-kurangnya 8 aksara.');</script>";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
 
                $stmt = $conn->prepare("INSERT INTO users (Id, Admin_Id, password, nama, umur, tinggi, berat) VALUES (?, NULL, ?, ?, ?, ?, ?)");

                $stmt->bind_param("issiid", $id, $hash, $nama, $umur, $tinggi, $berat);
                if($stmt->execute()){
                     echo "<script>alert('Pendaftaran berjaya! Sila log masuk.');window.location='login.php';</script>";
                } else {
                     echo "<script>alert('Ralat pendaftaran: " . $stmt->error . "');</script>";
                }
                $stmt->close();
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Daftar - MyMealPlan</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="container">
    <h2>DAFTAR PENGGUNA</h2>
    <form method="POST" action="register.php">
        <div class="input-group">
            <label>No Matrik:</label>
            <input type="text" name="id" required>
        </div>
        <div class="input-group">
            <label>Nama Penuh:</label>
            <input type="text" name="nama" required>
        </div>
        <div class="input-group">
            <label>Umur:</label>
            <input type="number" name="umur" required>
        </div>
        <div class="input-group">
            <label>Tinggi (cm):</label>
            <input type="number" name="tinggi" required>
        </div>
        <div class="input-group">
            <label>Berat (kg):</label>
            <input type="number" step="0.1" name="berat" required>
        </div>
        <div class="input-group">
            <label>Kata Laluan:</label>
            <input type="password" name="pwd" required minlength="8">
        </div>
        <button type="submit" name="register">DAFTAR</button>
    </form>
    <p>Dah ada akaun? <a href="login.php">Log Masuk</a></p>
</div>
</body>
</html>
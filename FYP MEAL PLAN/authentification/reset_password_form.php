<?php
session_start();
include '../database/db_connect.php'; 

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE Id = ?");
    $stmt->bind_param("si", $hashed_password, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Berjaya dikemaskini!'); window.location='login.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/login.css">
    <title>Password Baru</title>
</head>
<body>
<div class="container">
    <h2>Cipta Kata Laluan Baru</h2>
    <form method="POST" action="">
        <div class="input-group">
            <label>Kata Laluan Baru</label>
            <input type="password" name="new_password" required minlength="8">
        </div>
        <button type="submit">KEMASKINI PASSWORD</button>
    </form>
</div>
</body>
</html>
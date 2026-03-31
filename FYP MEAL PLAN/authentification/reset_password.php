<?php
include '../database/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['login_id']);
    $stmt = $conn->prepare("SELECT Id FROM users WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        echo "<script>alert('ID ditemui!'); window.location='reset_password_form.php?id=$id';</script>";
    } else {
        echo "<script>alert('Akaun tidak dijumpai.');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="container">
    <h2>Lupa Kata Laluan?</h2>
    <form method="POST" action="reset_password.php">
        <div class="input-group">
            <label>Masukkan No Matrik / ID</label>
            <input type="text" name="login_id" required>
        </div>
        <button type="submit">SEMAK ID</button>
    </form>
    <a href="login.php">Kembali ke Login</a>
</div>
</body>
</html>
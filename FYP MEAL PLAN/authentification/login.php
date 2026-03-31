<?php
session_start();
// Aktifkan error reporting supaya kalau ada error, dia tak keluar blank putih
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../database/db_connect.php'; 

if (!empty($_SESSION['logged_in'])) {
    $target = ($_SESSION['type'] === 'admin') ? "../admin/admin_dashboard.php" : "../user/user_dashboard.php";
    header("Location: $target"); 
    exit;
}

$login_error = "";
$password_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $type     = $_POST['type']; 
    $login_id = trim($_POST['login_id']); 
    $password = $_POST['pwd'];

    if (!ctype_digit($login_id) || strlen($login_id) > 8) {
        $login_error = "ID mestilah nombor (maksima 8 angka).";
    } else {
        if ($type === "admin") {
            $stmt = $conn->prepare("SELECT Id, Admin_Id, password FROM users WHERE Admin_Id = ? LIMIT 1");
        } else {
            $stmt = $conn->prepare("SELECT Id, Admin_Id, password FROM users WHERE Id = ? AND Admin_Id IS NULL LIMIT 1");
        }

        $stmt->bind_param("i", $login_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            $login_error = "Akaun tidak dijumpai.";
        } else {
            $stmt->bind_result($db_Id, $db_adminId, $db_password);
            $stmt->fetch();

            if (!password_verify($password, $db_password)) {
                $password_error = "Kata laluan salah.";
            } else {
                // 1. SET SESSION
                $_SESSION['logged_in'] = true;
                $_SESSION['type'] = $type;
                $_SESSION['user_id'] = $db_Id; 

                // Tentukan nama untuk log
                if ($type === "admin") {
                    $_SESSION['admin_user_id'] = $db_Id;
                    $_SESSION['admin_id']      = $db_adminId;
                    $nama_log = "Admin (ID: " . $db_adminId . ")";
                    $redirect_path = "../admin/admin_dashboard.php"; 
                } else {
                    $nama_log = "Pengguna (ID: " . $db_Id . ")";
                    $redirect_path = "../user/user_dashboard.php"; 
                }

                // 2. SIMPAN LOG AKTIVITI
                $activity = "Telah Log Masuk";
                $sqlLog = "INSERT INTO logs (user_id, nama_paparan, activity, last_login) VALUES (?, ?, ?, NOW())";
                $stmtLog = $conn->prepare($sqlLog);
                $stmtLog->bind_param("iss", $db_Id, $nama_log, $activity);
                $stmtLog->execute();
                $stmtLog->close();

                // 3. UPDATE LAST LOGIN
                $up = $conn->prepare("UPDATE users SET last_login = NOW() WHERE Id = ?");
                $up->bind_param("i", $db_Id);
                $up->execute();
                $up->close();

                // 4. REDIRECT
                header("Location: $redirect_path");
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Login - MyMealPlan</title>
    <link rel="stylesheet" href="../css/login.css?v=<?= time(); ?>">
</head>
<body>

<div class="system-title-container">
    <h1 class="main-title">SISTEM PELAN AKTIVITI FIZIKAL DAN PEMAKANAN</h1>
    <h2 class="sub-title">UNTUK SASARAN BERAT IDEAL</h2>
</div>

<div class="container">
    <h2>LOG MASUK</h2>
    <form method="POST">
        <div class="input-group">
            <label>Jenis Akaun</label>
            <select name="type">
                <option value="user">Pengguna</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="input-group">
            <label>No Matrik / ID Admin</label>
            <input type="text" name="login_id" placeholder="Masukkan ID" required>
            <?php if($login_error): ?>
                <p class="error-msg"><?= $login_error ?></p>
            <?php endif; ?>
        </div>
        <div class="input-group">
            <label>Kata Laluan</label>
            <input type="password" name="pwd" placeholder="Masukkan Kata Laluan" required>
            <?php if($password_error): ?>
                <p class="error-msg"><?= $password_error ?></p>
            <?php endif; ?>
        </div>
        <button type="submit" name="login">LOG MASUK</button>
    </form>
    
    <div class="login-footer">
        <p>Tiada Akaun? <a href="register.php" class="link-register">Daftar</a></p>
        <a href="reset_password.php" class="link-forgot">Lupa Kata Laluan?</a>
    </div>
</div>

</body>
</html>
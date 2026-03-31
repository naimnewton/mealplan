<?php
// Tunjuk error dengan jelas (senang debug)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Credentials XAMPP default
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fyp_mealplan";

// Sambung DB
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

// Jika sampai sini, sambungan OK
echo "<h2 style='color:green'>✔️ Sambungan ke pangkalan data berjaya!</h2>";

// Uji jadual foods
$res = $conn->query("SELECT COUNT(*) AS jumlah FROM foods");
$row = $res->fetch_assoc();
echo "<p>Jumlah rekod dalam jadual <b>foods</b>: <b>{$row['jumlah']}</b></p>";

$conn->close();
?>

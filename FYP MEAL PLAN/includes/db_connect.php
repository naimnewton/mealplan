<?php
// Supaya MySQL error keluar jelas (penting masa development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "fyp_mealplan";

try {
    // Sambungan
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Kalau sambungan gagal → paparkan error dan hentikan sistem
    die("❌ Ralat sambungan pangkalan data: " . $e->getMessage());
}
?>

<?php
// Credentials XAMPP default
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fyp_mealplan"; // Berdasarkan nama DB dalam phpMyAdmin kau

// Sambung DB
$conn = new mysqli($host, $user, $pass, $db);

// Set charset supaya tulisan simbol/emoji tak error
$conn->set_charset("utf8mb4");

// Jika gagal, sistem akan beritahu sebabnya
if ($conn->connect_error) {
    die("Sambungan ke pangkalan data gagal: " . $conn->connect_error);
}
?>
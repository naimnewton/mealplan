<?php
// Tunjuk semua error (kalau ada)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Password asal yang kita nak hash
$password_plain = "Admin12345";

// Jana hash menggunakan password_hash
$hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Papar di skrin
echo "<h1>Jana Hash Kata Laluan</h1>";
echo "<p>Password asal: <b>{$password_plain}</b></p>";
echo "<p>Hash yang dijana:</p>";
echo "<pre>{$hash}</pre>";
?>

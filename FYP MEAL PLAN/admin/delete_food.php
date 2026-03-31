<?php
include '../database/db_connect.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Cari kategori dulu sebelum padam untuk redirect nanti
    $res = $conn->query("SELECT category FROM foods WHERE id = '$id'");
    if ($row = $res->fetch_assoc()) {
        $cat = $row['category'];
        
        // Padam
        if ($conn->query("DELETE FROM foods WHERE id = '$id'")) {
            header("Location: manage_admin.php?category=$cat&status=deleted");
            exit;
        }
    }
}
header("Location: foods_admin.php");
exit;
?>
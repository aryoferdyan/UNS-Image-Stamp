<?php
if (isset($_GET['files']) && isset($_GET['processed'])) {
    $uploadDir = 'uploads/';
    $processedDir = 'processed/';

    // Hapus semua file dari folder uploads
    $uploadedFilesInDir = glob($uploadDir . '*'); // Mendapatkan semua file di folder uploads
    foreach ($uploadedFilesInDir as $file) {
        if (file_exists($file)) {
            unlink($file); // Hapus setiap file
        }
    }

    // Hapus semua file dari folder processed (termasuk file ZIP dan lainnya)
    $processedFilesInDir = glob($processedDir . '*'); // Mendapatkan semua file di folder processed
    foreach ($processedFilesInDir as $file) {
        if (file_exists($file)) {
            unlink($file); // Hapus setiap file
        }
    }

// Mendapatkan URL dasar (untuk redirect setelah selesai)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$baseURL = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';

// Redirect ke halaman utama setelah menghapus file
header("Location: $baseURL");
exit();

}
?>

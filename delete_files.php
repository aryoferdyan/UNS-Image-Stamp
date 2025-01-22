<?php
if (isset($_GET['files']) && isset($_GET['processed'])) {
    $uploadDir = 'uploads/';
    $processedDir = 'processed/';

    // Decode parameter files yang berisi path file yang diupload dan diproses
    $uploadedFiles = json_decode(urldecode($_GET['files']));
    $processedFiles = json_decode(urldecode($_GET['processed']));

    // Hapus file dari folder uploads
    foreach ($uploadedFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Hapus file dari folder processed (termasuk file ZIP dan lainnya)
    $processedFilesInDir = glob($processedDir . '*'); // Mendapatkan semua file di folder processed
    foreach ($processedFilesInDir as $file) {
        if (file_exists($file)) {
            unlink($file); // Hapus setiap file
        }
    }

    // Redirect ke halaman utama setelah menghapus file
    header("Location: http://localhost/formatweb/");
    exit();
}
?>

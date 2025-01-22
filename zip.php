<?php
// Pastikan direktori hasil proses ada
$processedDir = 'processed/';
$uploadsDir = 'uploads/';

// Cek apakah ada parameter file ZIP yang akan didownload
if (isset($_GET['zip']) && file_exists($processedDir . $_GET['zip'])) {
    $zipFile = $processedDir . $_GET['zip'];
    
    // Set header untuk mengunduh file ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
    header('Content-Length: ' . filesize($zipFile));

    // Baca file ZIP dan kirimkan ke output
    readfile($zipFile);

    // Tunggu 1 menit sebelum menghapus semua file
    sleep(60);

    // Hapus semua file di folder processed
    $files = glob($processedDir . '*');
    foreach ($files as $file) {
        unlink($file);
    }

    // Hapu

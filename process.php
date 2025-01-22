<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    $processedDir = 'processed/';
    $watermarkFile = 'assets/watermark.png';

    // Pastikan direktori ada
    if (!is_dir($uploadDir)) mkdir($uploadDir);
    if (!is_dir($processedDir)) mkdir($processedDir);

    // Periksa file upload
    $files = $_FILES['images'];
    if (empty($files['name'][0])) {
        echo 'No files uploaded!';
        exit;
    }

    // Ambil nama file dari input user
    $filenameInput = isset($_POST['filename']) ? $_POST['filename'] : 'image'; // Default 'image' jika tidak ada input
    $processedFiles = [];
    $uploadedFiles = [];

    foreach ($files['tmp_name'] as $index => $tmpName) {
        $originalName = $files['name'][$index];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Menentukan nama baru berdasarkan input pengguna
        $newFileName = $filenameInput;
        if (count($files['tmp_name']) > 1) {
            $newFileName .= " " . ($index + 1); // Menambahkan urutan angka jika lebih dari 1 file
        }
        $newFileName .= '.png'; // Simpan sementara dalam format PNG untuk proses watermark

        $uploadedPath = $uploadDir . basename($newFileName);

        // Pindahkan file upload
        if (!move_uploaded_file($tmpName, $uploadedPath)) {
            echo "Failed to upload file: $originalName<br>";
            continue;
        }

        $uploadedFiles[] = $uploadedPath;  // Menyimpan file yang diupload

        // Buat gambar dari file yang diupload (sementara dalam PNG)
        $image = imagecreatefromstring(file_get_contents($uploadedPath));
        if (!$image) {
            echo "Invalid image file: $originalName<br>";
            continue;
        }

        // Tambahkan watermark
        $watermark = imagecreatefrompng($watermarkFile);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);

        // Sesuaikan ukuran watermark agar proporsional (misalnya, 15% dari lebar gambar)
        $wmNewWidth = imagesx($image) * 0.15; // 15% dari lebar gambar
        $wmNewHeight = ($wmNewWidth / $wmWidth) * $wmHeight; // Pertahankan rasio asli watermark

        // Membuat gambar watermark yang baru dengan ukuran proporsional
        $resizedWatermark = imagescale($watermark, $wmNewWidth, $wmNewHeight);
        imagedestroy($watermark); // Hapus watermark asli setelah diubah ukurannya

       // Pindahkan watermark 5% dari panjang gambar untuk $x dan 5% dari lebar gambar untuk $y
        $x = imagesx($image) - $wmNewWidth - (imagesx($image) * 0.05); // 5% dari lebar gambar untuk jarak dari kanan
        $y = imagesy($image) - $wmNewHeight - (imagesy($image) * 0.05); // 5% dari tinggi gambar untuk jarak dari bawah


        // Menempelkan watermark yang sudah diubah ukurannya pada gambar
        imagecopy($image, $resizedWatermark, $x, $y, 0, 0, $wmNewWidth, $wmNewHeight);
        imagedestroy($resizedWatermark); // Hapus watermark yang telah diubah ukurannya

        // Resize gambar setelah watermark
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        $newWidth = 1024;
        $newHeight = intval(($newWidth / $origWidth) * $origHeight);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($image);

        // Konversi gambar yang telah di-resize ke WebP
        $webpPath = $processedDir . pathinfo($newFileName, PATHINFO_FILENAME) . '.webp';
        imagewebp($resized, $webpPath, 90); // Simpan dengan kualitas 90
        imagedestroy($resized);

        $processedFiles[] = $webpPath;  // Menyimpan file yang telah diproses
    }

    // Buat ZIP jika ada file berhasil diproses
    if (!empty($processedFiles)) {
        $zipName = 'processed_images_' . time() . '.zip';
        $zipPath = $processedDir . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($processedFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            // Tampilkan link download ZIP
            echo "Processing complete! <a href='$zipPath' download id='download-link'>Download ZIP</a>";
        } else {
            echo "Failed to create ZIP file.";
        }
    } else {
        echo "No files were processed.";
    }
}
?>

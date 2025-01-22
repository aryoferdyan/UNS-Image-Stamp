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
        die('No files uploaded!');
    }

    $processedFiles = [];
    $uploadedFiles = [];
    foreach ($files['tmp_name'] as $index => $tmpName) {
        $originalName = $files['name'][$index];
        $uploadedPath = $uploadDir . basename($originalName);

        // Pindahkan file upload
        if (!move_uploaded_file($tmpName, $uploadedPath)) {
            echo "Failed to upload file: $originalName<br>";
            continue;
        }

        $uploadedFiles[] = $uploadedPath;  // Menyimpan file yang diupload

        // Resize gambar
        $image = imagecreatefromstring(file_get_contents($uploadedPath));
        if (!$image) {
            echo "Invalid image file: $originalName<br>";
            continue;
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        $newWidth = 1280;
        $newHeight = intval(($newWidth / $origWidth) * $origHeight);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Tambahkan watermark
        $watermark = imagecreatefrompng($watermarkFile);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);

        // Pindahkan watermark 20 px ke kiri dan 20 px ke atas
        $x = $newWidth - $wmWidth - 10 - 20; // 10 px dari tepi kanan, 20 px lebih ke kiri
        $y = $newHeight - $wmHeight - 10 - 20; // 10 px dari tepi bawah, 20 px lebih ke atas

        imagecopy($resized, $watermark, $x, $y, 0, 0, $wmWidth, $wmHeight);
        imagedestroy($watermark);

// Konversi ke WebP (lossless)
$outputPath = $processedDir . pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
imagewebp($resized, $outputPath, 100); // Menggunakan kualitas 100 untuk lossless

        // Bersihkan memori
        imagedestroy($image);
        imagedestroy($resized);

        $processedFiles[] = $outputPath;  // Menyimpan file yang telah diproses
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
            echo "Processing complete! <a href='$zipPath' download>Download ZIP</a>";

            // Setelah link download, set waktu tunggu 10 detik untuk menghapus file
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'delete_files.php?files=" . urlencode(json_encode($uploadedFiles)) . "&processed=" . urlencode(json_encode($processedFiles)) . "';
                }, 10000); // 10 detik
                </script>";
        } else {
            echo "Failed to create ZIP file.";
        }
    } else {
        echo "No files were processed.";
    }
}
?>

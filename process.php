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
        $newFileName .= '.' . $extension;

        $uploadedPath = $uploadDir . basename($newFileName);

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
        $newWidth = 1024;
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
        $outputPath = $processedDir . pathinfo($newFileName, PATHINFO_FILENAME) . '.webp';
        imagewebp($resized, $outputPath, 80); // Menggunakan kualitas 100 untuk lossless

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
            echo "Processing complete! <a href='$zipPath' download id='download-link'>Download ZIP</a>";


        } else {
            echo "Failed to create ZIP file.";
        }
    } else {
        echo "No files were processed.";
    }
}
?>

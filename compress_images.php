<?php

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'jsff5967_jsfloristnew';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Find out FCPATH
// Usually FCPATH is the public folder, let's assume this script is in root and FCPATH is root/public
$fcpath = __DIR__ . '/public/';
if (!is_dir($fcpath . 'assets')) {
    // maybe it's just root
    $fcpath = __DIR__ . '/';
}

function convertToWebp($sourcePath, $quality = 80) {
    if (!file_exists($sourcePath)) return false;
    
    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    if ($ext === 'webp') return false; // Already webp
    
    $mime = mime_content_type($sourcePath);
    $image = null;
    if ($mime === 'image/jpeg' || $ext === 'jpg' || $ext === 'jpeg') {
        $image = @imagecreatefromjpeg($sourcePath);
    } elseif ($mime === 'image/png' || $ext === 'png') {
        $image = @imagecreatefrompng($sourcePath);
        if ($image) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
    }
    
    if (!$image) return false;
    
    $dir = dirname($sourcePath);
    $filenameWithoutExt = pathinfo($sourcePath, PATHINFO_FILENAME);
    $destPath = $dir . '/' . $filenameWithoutExt . '.webp';
    
    if (imagewebp($image, $destPath, $quality)) {
        imagedestroy($image);
        return $filenameWithoutExt . '.webp';
    }
    
    imagedestroy($image);
    return false;
}

function processDirectoryAndDB($conn, $dir, $table, $column, $isRelative = false, $basePath = '') {
    echo "Processing directory: $dir\n";
    if (!is_dir($dir)) {
        echo "Directory not found.\n";
        return;
    }
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $sourcePath = $dir . '/' . $file;
        if (is_file($sourcePath)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $newFileName = convertToWebp($sourcePath);
                if ($newFileName) {
                    // Update DB
                    if ($isRelative) {
                        // find relative path from FCPATH
                        $oldDbValue = ltrim(str_replace(str_replace('\\', '/', $basePath), '', str_replace('\\', '/', $sourcePath)), '/');
                        $newDbValue = dirname($oldDbValue) . '/' . $newFileName;
                        if ($newDbValue[0] == '.' && $newDbValue[1] == '/') {
                            $newDbValue = substr($newDbValue, 2);
                        }
                    } else {
                        $oldDbValue = $file;
                        $newDbValue = $newFileName;
                    }
                    
                    $stmt = $conn->prepare("UPDATE `$table` SET `$column` = ? WHERE `$column` = ?");
                    if ($stmt) {
                        $stmt->bind_param("ss", $newDbValue, $oldDbValue);
                        $stmt->execute();
                        if ($stmt->affected_rows > 0) {
                            echo "Updated DB: $oldDbValue -> $newDbValue\n";
                        }
                        $stmt->close();
                    } else {
                        echo "Error preparing statement for $table\n";
                    }
                    
                    // Unlink original
                    unlink($sourcePath);
                    echo "Converted: $file -> $newFileName\n";
                }
            }
        }
    }
}

processDirectoryAndDB($conn, $fcpath . 'assets/img/gambar', 'products', 'gambar_url', false);
processDirectoryAndDB($conn, $fcpath . 'assets/img/variants', 'product_variants', 'gambar_varian_url', false);
processDirectoryAndDB($conn, $fcpath . 'assets/img/products', 'product_images', 'image_url', false);
processDirectoryAndDB($conn, $fcpath . 'assets/img/artikel', 'artikel', 'gambar', false);
processDirectoryAndDB($conn, $fcpath . 'uploads/comics/episodes', 'comic_episodes', 'cover_image', false);
processDirectoryAndDB($conn, $fcpath . 'uploads/comics/panels', 'comic_panels', 'image_path', false);
processDirectoryAndDB($conn, $fcpath . 'uploads/event_banners', 'event_banners', 'image_url', false);

processDirectoryAndDB($conn, $fcpath . 'uploads/profile', 'users', 'profile_photo', true, rtrim($fcpath, '/'));
processDirectoryAndDB($conn, $fcpath . 'public/uploads/proofs', 'orders', 'bukti_bayar', true, rtrim($fcpath, '/'));
processDirectoryAndDB($conn, $fcpath . 'uploads/proofs', 'orders', 'bukti_bayar', true, rtrim($fcpath, '/'));

echo "All done!\n";

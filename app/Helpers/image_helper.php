<?php

if (!function_exists('upload_and_convert_to_webp')) {
    /**
     * Uploads an image file, converts it to WebP, and saves it.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file The uploaded file instance
     * @param string $uploadPath The directory to save the file
     * @param string|null $fileNameWithoutExt Optional custom filename (without extension). If null, a random name is generated.
     * @param int $quality The WebP compression quality (0-100)
     * @return string|false Returns the saved file name (with .webp extension) on success, or false on failure.
     */
    function upload_and_convert_to_webp($file, string $uploadPath, ?string $fileNameWithoutExt = null, int $quality = 80)
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return false;
        }

        // Ensure upload directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if ($fileNameWithoutExt === null) {
            $fileNameWithoutExt = pathinfo($file->getRandomName(), PATHINFO_FILENAME);
        }

        $fileName = $fileNameWithoutExt . '.webp';
        $fullPath = rtrim($uploadPath, '/') . '/' . $fileName;
        $tempName = $file->getTempName();
        $mime = $file->getMimeType();

        try {
            $image = null;

            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $image = @imagecreatefromjpeg($tempName);
            } elseif ($mime === 'image/png') {
                $image = @imagecreatefrompng($tempName);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
            } elseif ($mime === 'image/webp') {
                // If already webp, we can just move it or rewrite it to compress it
                $image = @imagecreatefromwebp($tempName);
            }

            if ($image) {
                $success = imagewebp($image, $fullPath, $quality);
                imagedestroy($image);
                
                if ($success) {
                    return $fileName;
                }
            }

            // Fallback: If GD fails, just move the file natively (with original extension)
            $origName = $file->getRandomName();
            $file->move(rtrim($uploadPath, '/'), $origName);
            return $origName;
            
        } catch (\Exception $e) {
            log_message('error', 'Error converting image to WebP: ' . $e->getMessage());
            return false;
        }
    }
}

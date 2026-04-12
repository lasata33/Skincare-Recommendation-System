<?php
class FileHandler {
    public static function saveUploadedImage($file, $uploadDir) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return [false, "⚠️ No valid file uploaded. Please try again."];
        }

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $originalName = basename($file['name']);
        $targetPath = $uploadDir . $originalName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [false, "⚠️ Failed to save uploaded image."];
        }

        return [true, $targetPath];
    }
}
?>

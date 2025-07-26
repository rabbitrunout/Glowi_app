<?php
function process_image($uploadDir, $filename) {
    $sourcePath = $uploadDir . $filename;

    if (!file_exists($sourcePath)) {
        return false;
    }

    $info = getimagesize($sourcePath);
    if (!$info) return false;

    $width = 100;
    $height = 100;

    switch ($info['mime']) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $src = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    if (!$src) return false;

    $thumb = imagecreatetruecolor($width, $height);

    if ($info['mime'] === 'image/png' || $info['mime'] === 'image/gif') {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

    $targetFile = pathinfo($filename, PATHINFO_FILENAME) . '_100.' . pathinfo($filename, PATHINFO_EXTENSION);
    $targetPath = $uploadDir . $targetFile;

    switch ($info['mime']) {
        case 'image/jpeg':
            imagejpeg($thumb, $targetPath, 90);
            break;
        case 'image/png':
            imagepng($thumb, $targetPath);
            break;
        case 'image/gif':
            imagegif($thumb, $targetPath);
            break;
        case 'image/webp':
            imagewebp($thumb, $targetPath, 90);
            break;
    }

    imagedestroy($src);
    imagedestroy($thumb);

    return $targetFile;
}
?>

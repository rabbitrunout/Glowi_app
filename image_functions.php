<?php
function process_image($dir, $filename) {
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $i = strrpos($filename, '.');
    $image_name = substr($filename, 0, $i);
    $ext = substr($filename, $i);

    $image_path = $dir . $filename;
    $image_path_400 = $dir . $image_name . '_400' . $ext;
    $image_path_100 = $dir . $image_name . '_100' . $ext;

    resize_image($image_path, $image_path_400, 400, 300);
    resize_image($image_path, $image_path_100, 100, 100);
}

function resize_image($old_image_path, $new_image_path, $max_width, $max_height) {
    $image_info = getimagesize($old_image_path);
    $image_type = $image_info[2];

    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image_from_file = 'imagecreatefromjpeg';
            $image_to_file = 'imagejpeg';
            break;
        case IMAGETYPE_GIF:
            $image_from_file = 'imagecreatefromgif';
            $image_to_file = 'imagegif';
            break;
        case IMAGETYPE_PNG:
            $image_from_file = 'imagecreatefrompng';
            $image_to_file = 'imagepng';
            break;
        default:
            die('Файл должен быть изображением JPEG, GIF или PNG.');
    }

    $old_image = $image_from_file($old_image_path);
    $old_width = imagesx($old_image);
    $old_height = imagesy($old_image);

    $width_ratio = $old_width / $max_width;
    $height_ratio = $old_height / $max_height;

    if ($width_ratio > 1 || $height_ratio > 1) {
        $ratio = max($width_ratio, $height_ratio);
        $new_width = round($old_width / $ratio);
        $new_height = round($old_height / $ratio);

        $new_image = imagecreatetruecolor($new_width, $new_height);

        if ($image_type == IMAGETYPE_GIF) {
            $transparent_index = imagecolortransparent($old_image);
            if ($transparent_index >= 0) {
                $transparent_color = imagecolorsforindex($old_image, $transparent_index);
                $transparent_index_new = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new_image, 0, 0, $transparent_index_new);
                imagecolortransparent($new_image, $transparent_index_new);
            }
        }

        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
            imagefill($new_image, 0, 0, $transparent);
        }

        imagecopyresampled(
            $new_image, $old_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $old_width, $old_height
        );

        $image_to_file($new_image, $new_image_path);

        imagedestroy($new_image);
    } else {
        $image_to_file($old_image, $new_image_path);
    }

    imagedestroy($old_image);
}
?>

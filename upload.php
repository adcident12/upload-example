<?php

require './upload_compress.php';

$return_array = array();
$return_array['status'] = "fial";

$width = isset($_POST['width']) ? $_POST['width'] : "";
$height = isset($_POST['height']) ? $_POST['height'] : "";
$method = isset($_POST['method']) ? $_POST['method'] : "";
$quality = isset($_POST['quality']) ? $_POST['quality'] : "";

$images = restructureArray($_FILES);

if (!empty($images)) {
    foreach ($images as $key => $image) {

        $original_file_name = $image['name'];
        $s3_file_name = uniqid() . "_" . $original_file_name;
        $mime_type = $image['type'];
        $upload_folder = "./upload/" . $s3_file_name;
        $extension = "." . pathinfo($image['name'], PATHINFO_EXTENSION);

        if (in_array($extension, array('.jpg', '.jpeg', '.png', '.gif', '.bmp'))) {
            $image_file_content = imagecreatefromstring(file_get_contents($image['tmp_name']));
            $exif = exif_read_data($image['tmp_name']);
            if (!empty($exif['Orientation']) && in_array($exif['Orientation'], [2, 3, 4, 5, 6, 7, 8])) {
                if (in_array($exif['Orientation'], [3])) {
                    $image_file_content = imagerotate($image_file_content, 180, 0);
                }
                if (in_array($exif['Orientation'], [6])) {
                    $image_file_content = imagerotate($image_file_content, -90, 0);
                }
                if (in_array($exif['Orientation'], [8])) {
                    $image_file_content = imagerotate($image_file_content, 90, 0);
                }
                if (in_array($exif['Orientation'], [2])) {
                    imageflip($image_file_content, IMG_FLIP_HORIZONTAL);
                }
                if (in_array($exif['Orientation'], [4])) {
                    imageflip($image_file_content, IMG_FLIP_VERTICAL);
                }
                if (in_array($exif['Orientation'], [5])) {
                    imageflip($image_file_content, IMG_FLIP_VERTICAL);
                    $image_file_content = imagerotate($image_file_content, -90, 0);
                }
                if (in_array($exif['Orientation'], [7])) {
                    imageflip($image_file_content, IMG_FLIP_HORIZONTAL);
                    $image_file_content = imagerotate($image_file_content, -90, 0);
                }
            }
            $max_width = !empty($width) ? $width : imagesx($image_file_content);
            $max_height = !empty($height) ? $height : imagesy($image_file_content);
            $quality = !empty($quality) ? $quality : 100;
            $upload = new UploadCompress();
            $result = $upload->UploadAndCompress($method, $image_file_content, $upload_folder, $max_width, $max_height, $quality, $extension);
            if (empty($result['errors'])) {
                $return_array['status'] = "success";
            } else {
                $return_array['msg'] = $result;
            }
        } else {
            if (move_uploaded_file($image['tmp_name'], $upload_folder)) {
                $return_array['status'] = "success";
            } else {
                $return_array['msg'] = "There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.";
            }
        }
    }
}

echo json_encode($return_array);
exit();

function restructureArray(array $images)
{
    $result = array();
    foreach ($images as $key => $value) {
        foreach ($value as $k => $val) {
            for ($i = 0; $i < count($val); $i++) {
                $result[$i][$k] = $val[$i];
            }
        }
    }

    return $result;
}

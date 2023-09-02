<?php

$return_array = array();
$return_array['status'] = "fail";

$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : "";

if (!empty($file_name)) {
    if (unlink("./upload/" . $file_name)) {
        $return_array['status'] = 'success';
    }
}

echo json_encode($return_array);
exit();

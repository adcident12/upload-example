<?php
date_default_timezone_set('Asia/Bangkok');

$all_file = glob('./upload/*.*');

$return_array = array();
$return_array['status'] = "fail";

if (!empty($all_file)) {
    foreach ($all_file as $key => $value) {
        $tmp_file_name = explode("/", $value);
        $size = filesize($value);
        $file_creation_date = filectime($value);
        $return_array['list'][$key]['file_name'] = $tmp_file_name[2];
        $return_array['list'][$key]['type'] = mime_content_type($value);
        $return_array['list'][$key]['size'] = round($size / 1024 / 1024, 2)  . ' MB';
        $return_array['list'][$key]['date'] = date('d/m/Y H:i', $file_creation_date);
        $return_array['list'][$key]['path'] = $value;
        $return_array['list'][$key]['full_path'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/" . "upload/" . $tmp_file_name[2];
    }
    array_multisort(array_column($return_array['list'], 'date'), SORT_DESC, $return_array['list']);
    $return_array['status'] = "success";
}

echo json_encode($return_array);
exit();

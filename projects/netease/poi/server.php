<?php
// ini_set('display_errors', true);
// error_reporting(E_ALL);

require_once('database.php');
$database = new Database;
$database->connect();
$mysqli = $database->mysqli;

date_default_timezone_set('Asia/Shanghai');

$current_time = date('YmdHis');
$return_time = date('Y-m-d H:i:s');

$upload_dir = "./original_file/";

// 检查文件夹，或创建
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        $arr['result'] = false;
        $arr['error'] = '文件夹创建失败。';
        exit(json_encode($arr));
    }
}

// 检查是否有文件
if (empty($_FILES['file']['name'])) {
    $arr['result'] = false;
    $arr['error'] = $_FILES['file']['name']; 
    exit(json_encode($arr));
}

$file_name = $_FILES["file"]["name"];
$tmp_file = $_FILES["file"]["tmp_name"];
$upload_file = $upload_dir . 'O' . $current_time . "_" . $file_name;

// 完成上传文件
if ($error == UPLOAD_ERR_OK) {
    $mime_content_type = mime_content_type($tmp_file);

    if ($mime_content_type == 'text/plain') {
        if (move_uploaded_file($tmp_file, $upload_file)) {
            $arr['result'] = true;
        } else {
            $arr['error'] = $_FILES['file']['error'];
        }
    } else {
        $arr['error'] = "$file_name 上传失败：文件类型有误，仅支持 csv 格式的文件。如扩展名没问题，请使用 Excel 重新导出再试。";
    }
} elseif ($error = 2) {
    $arr['error'] = "$file_name 上传失败：文件太大，请上传小于 20M 的文件。";
} else {
    $arr['error'] = "$file_name 上传失败，错误代码：$error 。";
}

if ($arr['result']) {

    $array = file($upload_file);

    $num = preg_match_all('/,/', $array[0]);

    // 遍历每行
    foreach ($array as $value) {
        $arr = explode(',', $value);

        $sql = "SELECT pattern, replacement FROM rule";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->execute();
            $stmt->bind_result($pattern, $replacement);
        
            while ($stmt->fetch()) {
                $key_word = $arr[0];
                $arr = preg_replace("/$pattern/ui", $replacement, $arr);
                $arr[0] = $key_word;
            }
            $stmt->close();        
        }


        $arr = array_unique($arr);
        $arr = array_filter($arr);

        unset($arr[$num]);

        for ($i = 0; count($arr) < $num + 1; $i++) { 
            $temp = '';
            $arr[] = $temp;
        }

        $str = implode(',', $arr);

        file_put_contents("result_file/R{$current_time}_{$file_name}", $str . "\n", FILE_APPEND | LOCK_EX);
    }
    $arr['result'] = true;
    $arr['datetime'] = $return_time;
    $arr['file_name'] = $file_name;
    $arr['original_link'] = "original_file/O{$current_time}_{$file_name}";
    $arr['result_link'] = "result_file/R{$current_time}_{$file_name}";
    echo json_encode($arr);
}

$mysqli->close();


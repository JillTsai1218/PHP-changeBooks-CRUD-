<?php
require __DIR__. '/is_member.php';
require __DIR__. '/db_connect.php';

$upload_folder = __DIR__. '/BC_pic_uploads';

$output = [
    'success' => false ,
    'code' => 0 ,
    'error' => '參數不足',
];

$ext_map = [
    'image/jpeg' => '.jpg',
    'image/png' => '.png',
    'image/gif' => '.gif',
];




if(!isset($_POST['isbn']) or !isset($_POST['book_name'])){
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;


}

// TODO: 檢查欄位格式 這邊因為不是完整的SQL語法 所以不能直接PDO QUERY 會出錯


// 有沒有上傳圖
if(!empty($_FILES) and !empty($_FILES['BC_pic']['type']) and $ext_map[$_FILES['BC_pic']['type']]){
    $output['file'] = $_FILES;

    $filename = uniqid(). $ext_map[$_FILES['BC_pic']['type']];
    $output['filename'] = $filename;
    if(move_uploaded_file( $_FILES['BC_pic']['tmp_name'], $upload_folder. '/'. $filename)){
        $fields[] = "`BC_pic`= '$filename' ";
    }
}









$sql = "INSERT INTO `change_books`(
    `ISBN`, `book_name`, `book_condition`, `BC_pic`, `written_or_not`, `name_o_owner`, `email_o_owner`, `created_at`
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, NOW()
    )";
//來自用戶端的資料輸入，用prepare
$stmt = $pdo->prepare($sql);

$stmt->execute([
    $_POST['isbn'],
    $_POST['book_name'],
    $_POST['book_condition'],
    empty ($filename) ? NULL :($filename),
    $_POST['written_or_not'],
    $_SESSION['member']['name'],
    $_SESSION['member']['email'],




]);

$output['rowCount']  = $stmt->rowCount();
if($stmt->rowCount()){
    $output['success'] = true;
    unset($output['error']);

}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
    
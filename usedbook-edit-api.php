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



if(!isset($_POST['isbn'])  or !isset($_POST['book_name']) or ! isset($_POST['book_condition'])){
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;


}

// TODO: 檢查欄位格式 這邊因為不是完整的SQL語法 所以不能直接PDO QUERY 會出錯

if(!empty($_FILES) and !empty($_FILES['BC_pic']['type']) and $ext_map[$_FILES['BC_pic']['type']]){
    $output['file'] = $_FILES;

    $filename = uniqid(). $ext_map[$_FILES['BC_pic']['type']];
    $output['filename'] = $filename;
    if(move_uploaded_file( $_FILES['BC_pic']['tmp_name'], $upload_folder. '/'. $filename)){
        $fields[] = "`BC_pic`= '$filename' ";
    }
}





$sql = "UPDATE `change_books` SET `isbn`=?,`book_name`=?,`book_condition`=?,`BC_pic`=?,`written_or_not`=? ,`modifed_at`=NOW() WHERE `c_sid`=?";

$stmt = $pdo->prepare($sql);


// $url =  $_SERVER['QUERY_STRING'];
 //上面那行是，假設編輯編號13，這行會抓到網址上方c_sid=13

$url=$_REQUEST['c_sid'];
//上面那行是，假設編輯編號13，這行會抓到網址上方13，是純數字


//下面是若沒有上傳圖片，依照原來圖片的寫法
// $c_sql = "SELECT * FROM change_books WHERE 1 ORDER BY c_sid DESC" ;
$c_sql = "SELECT * FROM change_books WHERE `c_sid`='$url' " ;
$stmt2 = $pdo->query($c_sql);

$edit_row = $stmt2->fetch();


//
$stmt->execute([
    $_POST['isbn'],
    $_POST['book_name'],
    $_POST['book_condition'],
    // empty($filename) ? '5fe37032b92a4.jpg' : ($filename),//抓到檔名就可以
    empty($filename) ?   $edit_row['BC_pic'] : $filename,
    empty($_POST['written_or_not']) ?  $edit_row['written_or_not'] :  $_POST['written_or_not'],
    // $_POST['written_or_not'],
    $_POST['c_sid'],
    


]);

$output['rowCount']  = $stmt->rowCount();
if($stmt->rowCount()){
    $output['success'] = true;
    unset($output['error']);
}else{
    $output['error']= "資料沒有修改"
;}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
    
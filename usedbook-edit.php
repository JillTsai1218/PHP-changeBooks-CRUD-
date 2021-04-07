<?php
require __DIR__. '/is_member.php';
require __DIR__. '/db_connect.php';

if(! isset($_GET['c_sid'])){
    header('Location: change_list.php');
    exit;
}



$sid = intval($_GET['c_sid']);
$row=$pdo->query("SELECT * FROM change_books WHERE c_sid=$sid")
->fetch();

if(empty($row)){
    header('Location: change_list.php');
    exit;
}



?>

<?php include __DIR__ . '/parts/head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>
<style>
    /* class下 .form-text.error-msg也吃的到css */
    form small.error-msg{
        color:red;
    }

</style>

<div class="container">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-6">
            <!-- <?php //php if (isset($errorMsg)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php //$errorMsg ?> -->
                    <div class="alert alert-danger" role="alert" id="info" style="display: none">
+                錯誤
                </div>
            <!-- <?php //endif ?> -->

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">修改資料</h5>
                    <!-- 在form裡下標籤 novalidate ，可以忽視系統預設值，送出都不會被擋-->
                   <!-- <form name="form1" method="post"> -->
                   
                    <form name="form2" novalidate onsubmit="checkform(); return false;">
                    <input type="hidden" name="c_sid" value="<?= $sid ?>">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required value="<?= htmlentities($row['ISBN']) ?>">
                            <small class="form-text error-msg " style="display: none"></small>
                        </div>
                        <div class="form-group">
                            <label for="book_name">書名</label>
                            <input type="text" class="form-control" id="book_name" name="book_name" value="<?= htmlentities($row['book_name']) ?>">
                            <small class="form-text error-msg" style="display: none"></small>
                        </div>
                        <div class="form-group">
                            <label for="book_condition">書況</label>
                            <select name="book_condition" id="book_condition">
                                <option name="book_condition" value="1">1成新</option>
                                <option name="book_condition" value="3">3成新</option>
                                <option name="book_condition" value="5" selected>5成新</option>
                                <option name="book_condition" value="7">7成新</option>
                                <option name="book_condition" value="9">9成新</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="BC_pic">書況圖片</label>
                            <input type="file" class="" id="BC_pic" name="BC_pic" accept="image/*" value="<?= htmlentities($row['BC_pic']) ?>">
                        </div>
                        <div class="form-group">
                        <label for="written_or_not">有無塗改書寫</label>
                            <input type="radio" name="written_or_not" value="Y">有
                            <input type="radio" name="written_or_not" value="N">無
                        </div>
                        <button type="submit" class="btn btn-danger">修改</button>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/parts/script.php'; ?>
<script>
// document.querySelector('#name').style.borderColor = 'red'
const info = document.querySelector('#info');
const name = document.querySelector('#isbn');
const email = document.querySelector('#book_name');

function checkform(){
    info.style.display = 'none';
    let isPass = true ; 
    isbn.style.borderColor = '#CCCCCC';
    // name.closest('.form-group').querySelector('small').style.display = "none";
    isbn.nextElementSibling.style.display="none";
    book_name.style.borderColor = '#CCCCCC';
    // email.closest('.form-group').querySelector('small').style.display = "none";
    book_name.nextElementSibling.style.display = 'none';


    if(isbn.value.length<13){
        isPass = false ;
        name.style.borderColor="red";
        let small = name.closest('.form-group').querySelector('small');
        small.innerText = "請輸入正確的ISBN";
        small.style.display = "block" ;
    }




    if(isPass){
        const fd = new FormData(document.form2);
        // const fd = new FormData(document.forms[0]);

        fetch('usedbook-edit-api.php',{
            method:'POST', 
            body: fd
        })
        .then(r=>r.json())
        .then(obj=>{console.log(obj);
        if(obj.success){
            // 新增成功
            info.classList.remove('alert-danger');
            info.classList.add('alert-success');
                    info.innerHTML = '修改成功';
                } else {
                    info.classList.remove('alert-success');
                    info.classList.add('alert-danger');
                    info.innerHTML = obj.error || '修改失敗' ;
                }
                info.style.display = 'block';

            });
        }


    }



</script>

<?php include __DIR__ . '/parts/foot.php'; ?>
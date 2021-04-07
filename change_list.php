<?php
//連線資料庫 就會有db_connect.php裡  $pdo這個變數
//這邊檔案前面一定要打一個/

require __DIR__ . '/db_connect.php';

if (!isset($_SESSION['member'])) {
    include __DIR__ . '/change_list-noMember.php';
    exit;
}

$pagename = "change_list";

//intval是轉換成整數，取用戶端給的值可用$_GET
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
//search是字串所以不用使用intval去轉成整數
$search = isset($_GET['search']) ? ($_GET['search']) : '';
$params = [];

$where = 'WHERE 1 ';
if (!empty($search)) {
    //.=跟+=是一樣概念
    $where .= sprintf(" AND `book_name` LIKE %s ", $pdo->quote('%' . $search . '%'));
    $params['search'] = $search;
}

//測試搜尋我的交換單

$search_mine = isset($_GET['search_mine']) ? ($_GET['search_mine']) : '';
$my_params = [];


if (!empty($search_mine)) {
    //.=跟+=是一樣概念
    $where .= sprintf(" AND `name_o_owner` LIKE %s ", $pdo->quote('%' . $search_mine . '%'));
    $my_params['search_mine'] =  $search_mine;
}


//



$perPage = 5;
//這邊取名$t_sql的t是total的意思，COUNT(1)這個意思就是把每筆資料轉換成1再去算整個表格數量總共有幾筆
$t_sql = "SELECT COUNT(1) FROM change_books $where";
//$totalRows總比數 這邊pdo->query直接讓它執行，拿到一個statement的物件，fetch拿第一筆，因fetch拿出來是關聯式陣列，所以要打PDO::FETCH_NUM，來變成索引值，因為只有一筆，我們要取第一個值所以打[0]
// $totalRows = $pdo->query($t_sql)->fetch(PDO::FETCH_NUM)[0];
$totalRows = $pdo->query($t_sql)->fetch()['COUNT(1)'];
$totalPages = ceil($totalRows / $perPage);
//下面兩個條件式不能顛倒
if ($page > $totalPages) $page = $totalPages;
if ($page < 1) $page = 1;



//測試，這邊可以用exit或die離開頁面，只顯示下面跑出來的值，也可以用exit('')或die('')括弧裡面去填想跑出來的訊息
// echo $totalRows;
// exit;

//這邊->方法 query("")裡面是SQL語法，這裡的$stmt是一個代理物件，不是裡面的資料全部取出來設定給它的意思
//連結SQL語法，得到一個代理人(代理物件)
//這邊後面可加ORDER BY sid DSEC 改變排序 這邊是依照sid排序 DESC數值由大到小
// $stmt = $pdo->query("SELECT * FROM address_book ORDER BY sid DESC");

$p_sql = sprintf("SELECT * FROM change_books %s ORDER BY c_sid DESC LIMIT %s,%s", $where, ($page - 1) * $perPage, $perPage);

// echo  '$p_sql='.$p_sql;


//測試
// echo '<!-- ';
// echo $p_sql;
// echo ' -->';


$stmt = $pdo->query($p_sql); //拿到全部

//這邊是取出資料fetch是一次取單筆資料塞到記憶體裡面，fetchAll是取出全部的資料塞到記憶體裡面(所以如果有100萬筆資料，不要用fetchAll 記憶體會掛掉)
//再透過代理物件，去拿一筆(fetch)
//因為當初在db_connect.php裡面有設定這一筆 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ， 所以下面的$row = $stmt->fetch(); 就相當於是 $stmt->fetch(PDO::FETCH_ASSOC);，拿出資料的時候會是關聯式陣列，陣列前面會是KEY的名稱，這邊如果打$stmt->fetch(PDO::FETCH_Num); ，Num的話是索引，，陣列前面會是索引名稱(從0開始1.2.3...)，預設是stmt->fetch(PDO::FETCH_Both); ，Both的意思是我會同時給你兩種，一個是索引式的，一個是關聯式的，攪和在一起
//$row = $stmt->fetchAll();

$rows = $stmt->fetchAll();

//print_r 是輸出陣列
// print_r($row);

//使用echo json_encode();可以把資料轉為json
//下面的JSON_UNESCAPED_UNICODE跟JSON_UNESCAPED_SLASHES都是二元運算2的n次方，每個都是2的n次方的話，你用|或用+運算，得到的結果是一樣的
//echo json_encode($row , JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

?>

<?php include __DIR__ . '/parts/head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>
<style>
    .remove-icon a i {
        color: red;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?php
                                                    $params['page'] = 1;
                                                    echo http_build_query($params);
                                                    ?>">
                            <i class="fas fa-arrow-alt-circle-left"></i>
                        </a>
                    </li>
                    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?php $params['page'] = $page - 1;
                                                    echo http_build_query($params); ?>">
                            <i class="far fa-arrow-alt-circle-left"></i>
                        </a>

                    </li>
                    <?php for ($i = $page - 5; $i <= $page + 5; $i++) :
                        if ($i >= 1 and $i <= $totalPages) : ?>
                            <!-- 這邊class active是BS的class 可以切換頁面時有顏色跟著選頁 -->
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <!-- 這邊href的?連到同一個頁面但給不同的參數 -->
                                <a class="page-link" href="?<?php $params['page'] = $i;
                                                            echo http_build_query($params); ?>"><?= $i ?></a>
                            </li>
                    <?php endif;
                    endfor  ?>

                    <li class="page-item  <?= $page == $totalPages  ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?php $params['page'] = $page + 1;
                                                    echo http_build_query($params); ?>">
                            <i class="far fa-arrow-alt-circle-right"></i>
                        </a></li>
                    <li class="page-item <?= $page == $totalPages  ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?php $params['page'] = $totalPages;
                                                    echo http_build_query($params); ?>">
                            <i class="fas fa-arrow-alt-circle-right"></i>
                        </a></li>



                </ul>


            </nav>
        </div>
        <!--我的交換單  -->
        <div class="col d-flex flex-row-reverse bd-highlight search_mine">
            <form class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2" type="search" name="search_mine" placeholder="Search" aria-label="Search" value="<?php echo htmlentities($_SESSION['member']['name']) ?>" style="display: none">
                <button class="btn btn-outline-warning my-2 my-sm-0" type="submit">我的交換單</button>
            </form>
        </div>
        <!--  -->

        <div class="col d-flex flex-row-reverse bd-highlight">
            <form class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="找書名" aria-label="Search" value="<?= htmlentities($search) ?>">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <!-- 這邊欄位名稱是表格的欄位名稱，所以欄位名稱若是中文的也可以，欄位不一定要全取，像這次created_at就沒有取出來 -->
                        <th scope="col">編號</th>
                        <th scope="col">ISBN</th>
                        <th scope="col">書名</th>
                        <th scope="col">書況</th>
                        <th scope="col">書況圖</th>
                        <th scope="col">有無塗改書寫</th>
                        <th scope="col">原持有者</th>
                        <th scope="col">原持有者email</th>
                        <th scope="col">新增時間</th>
                        <th scope="col">修改時間</th>
                        <th scope="col">編輯</th>
                        <th scope="col">刪除</th>
                        <th scope="col">我想交換</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 這邊如果用while，fetch是從$stmt拿一筆出來去設定給$r，一筆一筆被拿出來，拿到最後就沒有了會拿到空值，就會看成false，就離開了，不用for迴圈的原因是不知道有幾筆 -->
                    <!-- 用foreach第一層就是索引陣列，所以不用拿到key如果要key就是打foreach($rows as $r)，下面如果你複製要在抓一次一樣的資料，就不會像while迴圈用法一樣，只能跑一次 -->
                    <?php foreach ($rows as $r) : ?>
                        <tr>
                            <!-- 這邊$r拿出來的是關聯式陣列(db_connect.php檔有設定)，所以要打欄位名稱 $r['欄位名稱'] -->

                            <td><?= $r['c_sid'] ?></td>
                            <td><?= $r['ISBN'] ?></td>
                            <td><?= htmlentities($r['book_name']) ?></td>
                            <td><?= $r['book_condition'] ?>成新</td>
                            <td>
                                <?php if ($r['BC_pic'] == NULL) : ?>
                                    <p>無</p>
                                    <!-- 或像下面，沒給寬高也可以，但就格子是空白 -->
                                    <!-- <img alt="" id="preview"  src="./BC_pic_uploads/<?php //$r['BC_pic'] 
                                                                                            ?>" > -->
                                <?php else : ?>
                                    <img alt="" id="preview" src="./BC_pic_uploads/<?= $r['BC_pic'] ?>" style="width: 200px; height: 200px; background-color: #ccc;">
                                <?php endif ?>
                            </td>
                            <td><?= $r['written_or_not'] ?></td>
                            <td><?= $r['name_o_owner'] ?></td>
                            <td><?= $r['email_o_owner'] ?></td>
                            <td><?= $r['created_at'] ?></td>
                            <td><?= $r['modifed_at'] ?></td>
                            <?php if ($_SESSION['member']['email'] == $r['email_o_owner']) : ?>
                                <td><a href="usedbook-edit.php?c_sid=<?= $r['c_sid'] ?>">
                                        <?= '<i class="fas fa-edit"></i>' ?></a></td>
                            <?php else : ?>
                                <td><a></a></td>
                            <?php endif ?>
                            <?php if ($_SESSION['member']['email'] == $r['email_o_owner']) : ?>
                                <td class="remove-icon"><a href="javascript:del_it(<?= $r['c_sid'] ?>)">
                                        <?= '<i class="fas fa-minus-circle"></i>' ?></a></td>
                            <?php else : ?>
                                <td><a></a></td>
                            <?php endif ?>

                            <?php if ($_SESSION['member']['email'] !== $r['email_o_owner']) : ?>
                                <td><a href="#">
                                        <?= '<button type="button" class="btn btn-dark">請求交換</button>' ?></a></td>
                            <?php else : ?>
                                <td><a></a></td>
                            <?php endif ?>


                        </tr>
                    <?php endforeach ?>
            </table>

        </div>
    </div>






</div>
<?php include __DIR__ . '/parts/script.php'; ?>
<script>
    const BC_pic = document.querySelector('#BC_pic');
    const preview = document.querySelector('#preview');
    const reader = new FileReader();

    reader.addEventListener('load', function(event) {
        preview.src = reader.result;
        preview.style.height = 'auto';
    });





    // function removeItem(event) {
    //     const t = event.target;
    //     t.closest('tr').remove();
    // }
    function del_it(sid) {
        if (confirm(`是否要刪除編號為 ${sid} 的資料?`)) {
            // 避免預設的行為
            // event.preventDefault();
            //跳窗
            location.href = 'usedbook-delete.php?c_sid=' + sid;

        }


    }
</script>
<?php include __DIR__ . '/parts/foot.php'; ?>
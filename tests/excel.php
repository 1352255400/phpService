<?php

require_once __DIR__ . '/../vendor/autoload.php';
$file = $msg = '';
function p($str = '')
{
    echo '<pre>';
    print_r($str);
    echo '</pre>';
}

use phpService\ExcelService;

$excle = new  ExcelService();

//导入
if (!empty($_FILES)) {
    $file = $_FILES['upfile'];
//    $file = 'file/demo.xlsx';
    $data = $excle->import($file);
    if ($data['code'] == '000') {
        p($data);
        die;
    }
    $msg = isset($data['msg']) ? $data['msg'] : '';
}

//导出
$type = isset($_GET['type']) ? $_GET['type'] : '';
if (!empty($type)) {
    //导出Excel
    $expTableData = [];
    $expTableData[] = [
        'name' => '测试1',//sheet名称
        'title' => ['标题1', '标题2', '标题3'], //表头
        'data' => [['a1', 'b1', 'c1'], ['aa1', 'bb1', 'cc1']], //内容
        'color' => 'FFCC0001',//字体颜色
        'color_row' => [['row' => 1, 'col_num' => 1], ['row' => 3, 'col_num' => 2]] //row第几行，col_num列数
    ];
    $expTableData[] = [
        'name' => '测试2',
        'title' => ['标题11', '标题22', '标题33'],
        'data' => [['a2', 'b2', 'c2'], ['aa2', 'bb2', 'cc2']],
    ];
    if ($type == 1) {
        $re = $excle->export('demo', $expTableData);
        exit();
    }
    //1.文件名，2.文件内容，3.保存地址（不填直接下载）
    $re = $excle->export('demo', $expTableData, 'file/');
    $file = isset($re['data']) ? $re['data'] : '';

}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>ims-phpexcel-demo</title>
</head>

<body>

<style>
    a {
        color: #000;
        text-decoration: none;
    }

    .main {
        width: 960px;
        margin: 50px auto;
    }

    .main .header {
        width: 100%;
        height: 80px;
        line-height: 80px;
        background: #1f336b;
        color: #fff;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
    }

    .main form {
        padding: 20px;
        border: 1px solid #1f336b;
    }

    /*表单输入框*/
    dl {
        width: 100%;
        overflow: hidden;
        padding: 15px;
        font-size: 12px;
        position: relative
    }

    dl dd {
        width: 100%;
        overflow: hidden;
        margin-bottom: 15px;
    }

    dl dd span {
        height: 40px;
        line-height: 40px;
        color: #999;
    }

    dl dd em {
        width: 80px;
        height: 40px;
        line-height: 40px;
        background: #FBFBFB;
        float: left;
        text-align: center;
        border: 1px solid #ccc;
        border-right: 0;
    }

    dl dd em.textarea {
        width: 900px;
        padding-left: 25px;
        text-align: left;
        border: 1px solid #ccc;
        border-bottom: 0;
    }

    dl dd select {
        width: 271px;
        height: 42px;
        line-height: 42px;
        float: left;
        border: 1px solid #ccc;
        padding: 0 10px
    }

    dl dd input[type='text'], dl dd input[type='file'] {
        width: 250px;
        height: 40px;
        line-height: 40px;
        float: left;
        border: 1px solid #ccc;
        padding: 0 10px
    }

    dl dd label {
        height: 40px;
        line-height: 40px;
        float: left;
        margin-left: 20px;
    }

    dl dd .radio {
        width: 250px;
        height: 40px;
        line-height: 40px;
        border: 1px solid #ccc;
        overflow: hidden;
        float: left;
        margin-right: 15px;
        padding: 0 10px;
    }

    .btn {
        width: 100px;
        height: 40px;
        line-height: 40px;
        background: #ccc;
        text-align: center;
        border: 0;
        display: inline-block;
    }
</style>
<div class="main">
    <div class="header">ims-phpexcel-demo</div>
    <form id="addform" action="" method="post" enctype="multipart/form-data">
        <dl>
            <dd>
                <em>Excel</em>
                <input type="file" class="btn_file_up" name='upfile'/>
            </dd>
            <dd>
                <?php if (!empty($file)) { ?>
                    <em>文件地址</em>
                    <div class="radio">
                        <?php echo $file; ?>
                        <a href="<?php echo $file; ?>">下载</a>
                    </div>
                <?php } ?>
            </dd>
            <dd>
                <input type="submit" class="btn" value="导入数据">
                <a href="?type=1" class="btn">下载文件</a>
                <a href="?type=2" class="btn">保存文件</a>
            </dd>
            <dd style="color: red">
                <?php echo $msg; ?>
            </dd>
        </dl>
    </form>
</div>

</body>
</html>

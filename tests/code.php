<?php

//引入公共文件
require_once 'common.php';

use phpService\Code\Code;

$api = new Code();

$code = isset($_GET['code']) ? $_GET['code'] : '';
if (!empty($code) && $code == 1) {
    //获取验证码
    $width = '350';
    $height = '50';
    $font_size = '20';
    echo $api->getCode($width, $height, $font_size);
    die;
} elseif (!empty($code)) {
    $data = $api->checkCode($code);
    echo json_encode($data);
    die;
}
?>

<style type="text/css">
    .code_div {
        width: 350px;
        background: #ccc;
        margin: 0 auto;
        margin-top: 150px;
        padding: 10px
    }

    .code_div h1 {
        color: #28A845;
        text-align: center;
        margin: 0
    }

    .code_div a {
        width: 350px;
        height: 50px;
        line-height: 50px;
        text-decoration: none;
        background: #333;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        display: block;
        text-align: center;
        margin-top: 10px
    }

    .code_div img {
        margin-top: 10px;
    }

    .code_div input {
        width: 350px;
        height: 50px;
        border: 1px solid #28A845;
        margin-top: 10px
    }

    .code_div input.btn {
        background: #28A845;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
    }

    .return_info {
        width: 350px;
        line-height: 30px;
        background: #ccc;
        margin: 0 auto;
        padding: 10px;
        padding-top: 0;
        text-align: center;
        display: none;
        font-weight: bold
    }
</style>
<div class="code_div">
    <h1>php动态验证码类</h1>
    <img class='code_img fr' src="code.php?code=1" onclick=this.src="code.php?code=1&"+Math.random()/>
    <input type="text" name="code" class="code" value="<?= $code ?>" placeholder="请输入验证码">
    <input type="button" class="btn" value="验 证">
    <a href="https://github.com/1352255400/code" target="_blank" title="composer require 1352255400/phpgifcode">获取源代码[GITHUB]</a>
</div>
<div class="return_info"></div>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
    $('.btn').click(function () {
        var code = $('.code').val();
        if (code == '') {
            $('.return_info').show().html('<span style="color: red;">请输入验证码</span>');
            return
        }
        ;
        $.ajax({
            type: 'get',
            url: 'code.php?code=' + code,
            dataType: 'JSON',
            success: function (data) {
                if (data.code == 0) {
                    $('.return_info').show().html('<span style="color: green;">' + data.msg + '</span>');
                    return
                }
                ;
                $('.return_info').show().html('<span style="color: red;">' + data.msg + '</span>');
            },
            error: function () {
                $('.return_info').show().html('<span style="color: red;">网络异常-_-</span>');
            }
        });/// end $.ajax
    });
</script>

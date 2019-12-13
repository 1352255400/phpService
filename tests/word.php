<?php

//引入公共文件
require_once 'common.php';

//pdf转成图片
use phpService\WordService;

//实例化
$api = new  WordService();

$data = [];
$data['file_name'] = 'word';
$data['content'] = "word<img src='http://ims.com/file/demo.jpg' /><img src='file/demo.jpg'/>";
$api->index($data, 'http://ims.com/');


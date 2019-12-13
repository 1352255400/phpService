<?php

require_once __DIR__ . '/../vendor/autoload.php';

//use phpService\BaseModel;
use phpService\ExcelService;

//$BaseModel = new BaseModel();
//echo $BaseModel->joinType;

$excle = new  ExcelService();

$excle->import();

//导出Excel
$expTableData = [];
$expTableData[] = [
    'name' => '测试1',//sheet名称
    'title' => ['标题1', '标题2', '标题3'], //标题
    'data' => [['a1', 'b1', 'c1'], ['aa1', 'bb1', 'cc1']] //内容
];
$expTableData[] = [
    'name' => '测试2',
    'title' => ['标题11', '标题22', '标题33'],
    'data' => [['a2', 'b2', 'c2'], ['aa2', 'bb2', 'cc2']],
    'color' => 'FFCC0001',//字体颜色
    'color_row' => [['row' => 1, 'col_num' => 1], ['row' => 3, 'col_num' => 2]] //row第几行，col_num列数
];
$re = $excle->export('demo', $expTableData, 'file/');
var_dump($re);
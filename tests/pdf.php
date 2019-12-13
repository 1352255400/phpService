<?php

//引入公共文件
require_once 'common.php';

//pdf转成图片
use phpService\PdfService;

//实例化
$api = new  PdfService();

//生成pdf
$data = [];
$data['file_name'] = 'pdf';
$data['title_header'] = '页首';
$data['title_footer'] = '页尾';
$data['content'] = '<img src="file/demo.jpg" style="width: 1100px"/>';
$data['water_text'] = '水印';
$data['is_down'] = 0;
$api->strToPdf($data);
die;


//use phpService\PdfService;

$pdf = new PdfService();
echo $file_path = BASE_PATH . 'file/demo.pdf';
//将pdf转成png
$imgArr = $pdf->pdfToPng($file_path, 'file/pdf');
p($imgArr);
die;
if (empty($imgArr)) {
    p('将pdf转成png失败');
}
//拼接图片
$img = '';
if (!empty($imgArr) && is_array($imgArr)) {
    $img = $pdf->spliceImg($imgArr, 'file/pdf/');
}
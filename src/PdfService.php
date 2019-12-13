<?php

namespace phpService;

/**
 * [PdfService 生成pdf,pdf转图片,图片拼接]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2019-12-12T17:21:14+0800
 */
class PdfService
{
    /**
     * 将文本转为pdf
     * @param array $data
     * @throws \MpdfException
     */
    public function strToPdf($data = [])
    {
        $file_name = isset($data['file_name']) ? $data['file_name'] : time();//文件名
        $title_header = isset($data['title_header']) ? $data['title_header'] : '';//页首
        $title_footer = isset($data['title_footer']) ? $data['title_footer'] : '';//页尾
        $content = isset($data['content']) ? $data['content'] : '';//内容
        $water_text = isset($data['water_text']) ? $data['water_text'] : '忆阁轩';//水印
        $is_down = isset($data['is_down']) ? $data['is_down'] : 1;//水印

        //composer require "mpdf/mpdf:6.*"（支持中文）
        //设置中文编码
        $mpdf = new \mPDF('zh-cn', 'A4', 0, '宋体', 0, 0);
        $mpdf->showWatermarkText = true;//启用水印
        $mpdf->SetWatermarkText($water_text, 0.1);//设置水印文字和透明度
        $mpdf->SetHTMLHeader('<p style="text-align:left;margin-left:20px;color:#0089FF;">' . $title_header . '</p>');
        $mpdf->SetHTMLFooter('<p style="text-align:right;margin-right:20px;color:#0089FF;">' . $title_footer . '-首创工作室</p>');
        $mpdf->WriteHTML($content);

        if ($is_down == 1) {
            //直接浏览器下载
            $mpdf->Output($file_name . '.pdf', true);
        } else {
            //浏览器输出
            $mpdf->Output();
        }
        exit;
    }

    /**
     * PDF2PNG
     * @param $pdf  待处理的PDF文件
     * @param $path 待保存的图片路径
     * @param int|待导出的页面 $page 待导出的页面 -1为全部 0为第一页 1为第二页
     * @return 保存好的图片路径和文件名 注：此处为坑 对于Imagick中的$pdf路径 和$path路径来说，   php版本为5+ 可以使用相对路径。php7+版本必须使用绝对路径。所以，建议大伙使用绝对路径。
     * 注：此处为坑 对于Imagick中的$pdf路径 和$path路径来说，   php版本为5+ 可以使用相对路径。php7+版本必须使用绝对路径。所以，建议大伙使用绝对路径。
     */
    public function pdfToPng($pdf, $path, $page = -1)
    {
        if (!extension_loaded('imagick')) {
            return ['code' => '1000', 'data' => [], 'msg' => '请安装imagick扩展'];
        }
        if (!file_exists($pdf)) {
            return ['code' => '1000', 'data' => [], 'msg' => 'pdf文件不存在'];
        }
        if (!is_readable($pdf)) {
            return ['code' => '1000', 'data' => [], 'msg' => 'pdf读取权限不足'];
        }

        $Return = [];
//        try {
        $im = new \Imagick();
        $im->setResolution(150, 150);
        $im->setCompressionQuality(100);
        if ($page == -1) {
            $im->readImage($pdf);
        } else {
            $im->readImage($pdf . "[" . $page . "]");
        }

        foreach ($im as $Key => $Var) {
            $Var->setImageFormat('png');
            $filename = $path . md5($Key . time()) . '.png';
            if ($Var->writeImage($filename) == true) {
                $Return[] = $filename;
            }
        }
        //返回转化图片数组，由于pdf可能多页，此处返回二维数组。
        return $Return;
//        } catch (\Exception $e) {
//            return ['code' => '1000', 'data' => [], 'msg' => '转换失败'];
//        }
    }


    /**
     * spliceimg
     * @param array $imgs pdf转化png  路径
     * @param string $img_path
     * @return string 将多个图片拼接为成图的路径
     * 注：多页的pdf转化为图片后拼接方法
     * @internal param string $path 待保存的图片路径
     */
    public function spliceImg($imgs = array(), $img_path = '')
    {

        $width = 600; //自定义宽度
        $height = null;
        $pic_tall = 0;//获取总高度
        foreach ($imgs as $key => $value) {
            $arr = getimagesize($value);
            $height = $width / $arr[0] * $arr[1];
            $pic_tall += $height;
        }
        $pic_tall = intval($pic_tall);
        // 创建长图
        $targetImg = imagecreatetruecolor($width, $pic_tall);
        //分配一个白色底色
        $color = imagecolorAllocate($targetImg, 255, 255, 255);
        imagefill($targetImg, 0, 0, $color);

        $tmp = 0;
        $tmpy = 0; //图片之间的间距
        $src = null;
        $size = null;
        foreach ($imgs as $k => $v) {
            $src = Imagecreatefrompng($v);
            $size = getimagesize($v);
            //5.进行缩放
            imagecopyresampled($targetImg, $src, $tmp, $tmpy, 0, 0, $width, $height, $size[0], $size[1]);
            //imagecopy($targetImg, $src, $tmp, $tmpy, 0, 0, $size[0],$size[1]);
            $tmpy = $tmpy + $height;
            //释放资源内存
            imagedestroy($src);
            unlink($imgs[$k]);
        }
        $returnfile = $img_path . date('Y-m-d');
        if (!file_exists($returnfile)) {
            if ($this->createFolders($returnfile)) {
                /* 创建目录失败 */
                return false;
            }
        }
        $return_imgpath = $returnfile . '/' . md5(time() . $pic_tall . 'pdftopng') . '.png';
        imagepng($targetImg, $return_imgpath);
        $return_imgpath = str_replace(BASE_PATH, '', $return_imgpath);
        return $return_imgpath;

    }

    //创建目录
    private function createFolders($dir = '')
    {
        return is_dir($dir) or (self::createFolders(dirname($dir)) and mkdir($dir, 0777));
    }
}


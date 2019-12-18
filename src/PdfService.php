<?php

namespace phpService;

/**
 * [PdfService 生成pdf,pdf转图片,图片拼接]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2011-11-11T11:11:11
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
     * 将pdf文件转化为多张png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)     *
     * @return array|bool
     */
    public function pdfToPngArr($pdf = '', $path = '')
    {
        if (!extension_loaded('imagick')) {
            return ['code' => '1000', 'data' => [], 'msg' => '请安装imagick扩展'];
        }
        if (!file_exists($pdf)) {
            return ['code' => '1000', 'data' => [], 'msg' => '请检查pdf文件路径'];
        }

        //检查存放目录
        $path = $path . date('Ymd') . '/';
        if (!file_exists($path)) {
            if (!$this->createFolders($path)) {
                /* 创建目录失败 */
                return ['code' => '1000', 'data' => $path, 'msg' => '创建目录失败'];
            }
        }

        try {
            $im = new \Imagick();
            $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
            $im->setCompressionQuality(100);
            $im->readImage($pdf);
            foreach ($im as $k => $v) {
                $v->setImageFormat('png');
                $fileName = $path . md5($k . time()) . '.png';
                if ($v->writeImage($fileName) == true) {
                    $return[] = $fileName;
                }
            }
            return ['code' => '000', 'data' => $return, 'msg' => 'ok'];
        } catch (Exception $e) {
            return ['code' => '1000', 'data' => $e, 'msg' => '转换异常'];
        }
    }

    /**
     * 将pdf转化为单一png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)
     * @return array
     */
    public function pdfToPng($pdf = '', $path = '')
    {
        if (!extension_loaded('imagick')) {
            return ['code' => '1000', 'data' => [], 'msg' => '请安装imagick扩展'];
        }
        if (!file_exists($pdf)) {
            return ['code' => '1000', 'data' => [], 'msg' => '请检查pdf文件路径'];
        }

        //检查存放目录
        $path = $path . date('Ymd') . '/';
        if (!file_exists($path)) {
            if (!$this->createFolders($path)) {
                /* 创建目录失败 */
                return ['code' => '1000', 'data' => $path, 'msg' => '创建目录失败'];
            }
        }

        try {
            $im = new \Imagick();
            $im->setCompressionQuality(100);
            $im->setResolution(120, 120);//设置分辨率 值越大分辨率越高
            $im->readImage($pdf);

            $canvas = new \Imagick();
            $imgNum = $im->getNumberImages();
            //$canvas->setResolution(120, 120);
            foreach ($im as $k => $sub) {
                $sub->setImageFormat('png');
                //$sub->setResolution(120, 120);
                $sub->stripImage();
                $sub->trimImage(0);
                $width = $sub->getImageWidth() + 10;
                $height = $sub->getImageHeight() + 10;
                if ($k + 1 == $imgNum) {
                    $height += 10;
                } //最后添加10的height
                $canvas->newImage($width, $height, new \ImagickPixel('white'));
                $canvas->compositeImage($sub, \Imagick::COMPOSITE_DEFAULT, 5, 5);
            }

            $canvas->resetIterator();
            $file_path = $path . microtime(true) . '.png';
            $canvas->appendImages(true)->writeImage($file_path);
            return ['code' => '000', 'data' => $file_path, 'msg' => 'ok'];
        } catch (Exception $e) {
            return ['code' => '1000', 'data' => $e, 'msg' => '转换异常'];
        }
    }

    //创建目录
    private function createFolders($dir = '')
    {
        return is_dir($dir) or (self::createFolders(dirname($dir)) and mkdir($dir, 0777));
    }
}


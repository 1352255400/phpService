<?php

namespace phpService\Code;

/**
 * [Code 验证码 辅助类]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2011-11-11T11:11:11
 */
class Code
{
    /**
     * [getCode 生成包含验证码的GIF图片的函数]
     * @Author   W_wang
     * @email    1352255400@qq.com
     * @DateTime 2018-06-06T15:03:56+0800
     * @param    integer $code_width [初始化验证码宽度]
     * @param    integer $code_height [初始化验证码高度]
     * @param    integer $fontsize [初始化验证码字体大小]
     * @return   [type]                                [description]
     */
    public function getCode($code_width = 175, $code_height = 40, $fontsize = 18)
    {
        //初始化字体信息
        $fonts = array("ARLRDBD", "ALGER", "BRLNSR", "STENCIL", "ELEPHNT", "ELEPHNTI");
        $random_keys = array_rand($fonts, 2);
        $font = $fonts[$random_keys[0]];
        $font = __DIR__ . '/fonts/' . $font . '.TTF';

        //创建随机码
        $authstr = '';
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789';
        $code_num = rand(4, 6);
        for ($i = 0; $i < $code_num; $i++) {
            $authstr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        // 生成一个32帧的GIF动画 
        for ($i = 0; $i < rand(22, 32); $i++) {
            ob_start();

            //创建一张背景图片
            $image = imagecreate($code_width, $code_height);
            imagecolorallocate($image, 0, 0, 0);

            // 设定文字颜色数组 （初始化）
            for ($ii = 0; $ii < rand(22, 32); $ii++) {
                $colorList[] = ImageColorAllocate($image, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            }

            //设置背景图片颜色
            $gray = ImageColorAllocate($image, 255, 255, 255); //背景色 mt_rand(100,255), mt_rand(157,255), mt_rand(157,255)多色
            imagefill($image, 0, 0, $gray);

            $space = ceil($code_width / strlen($authstr));// 字符间距
            //每个字母上下左右的距离
            if ($i > 0) {// 屏蔽第一帧（空白）
                for ($k = 0; $k < strlen($authstr); $k++) {
                    $colorRandom = mt_rand(0, sizeof($colorList) - 1); //选一张背景图片作为文字背景
                    $float_top = rand(ceil($code_height / 1.4), ceil($code_height / 2));//距离顶部的高度
                    $float_left = rand(ceil($space / 10), ceil($space / 6)); //距离左右的宽度
                    imagettftext($image, $fontsize, mt_rand(-30, 10), $space * $k + $float_left, $float_top, $colorList[$colorRandom], $font, substr($authstr, $k, 1));
                }
            }

            //添加小黑点
            for ($k = 0; $k < rand(10, 20); $k++) {
                $colorRandom = mt_rand(0, sizeof($colorList) - 1);
                imagesetpixel($image, rand(0, $code_width), rand(0, $code_height), $colorList[$colorRandom]);

            }

            // 添加干扰线 
            for ($k = 0; $k < rand(1, 10); $k++) {
                $colorRandom = mt_rand(0, sizeof($colorList) - 1);
                $todrawline = 1;
                if ($todrawline) {
                    imageline($image, mt_rand(0, $code_width), mt_rand(0, $code_height), mt_rand(0, $code_width), mt_rand(0, $code_height), $colorList[$colorRandom]);
                } else {
                    $w = mt_rand(0, $code_width);
                    $h = mt_rand(0, $code_width);
                    imagearc($image, $code_width - floor($w / 2), floor($h / 2), $w, $h, rand(90, 180), rand(180, 270), $colorList[$colorRandom]);
                }
            }

            imagegif($image);
            imagedestroy($image); //销毁图片资源释放内存
            $imagedata[] = ob_get_contents();
            ob_clean();
            // ++$i; 
        }

        //保存验证码到session
        if ($this->is_session_started() == FALSE) session_start();
        $code = md5(strtolower($authstr));
        $_SESSION['code'] = $code;

        //输出gif图片
        Header('Content-type:image/gif');
        $img = new GifEnCoderHelper($imagedata);
        echo $img->GetAnimation();
        exit();
    }


    /**
     * [checkCode 验证验证码]
     * @Author   W_wang
     * @email    1352255400@qq.com
     * @DateTime 2018-06-06T15:03:34+0800
     * @param    integer $code [description]
     * @return   [type]                         [description]
     */
    public function checkCode($code = 0)
    {
        if ($this->is_session_started() == FALSE) session_start();
        $code_sesion = isset($_SESSION['code']) ? $_SESSION['code'] : '';
        $captcha = md5(strtolower($code));
        if ($captcha != $code_sesion) {
            return array('code' => '1000', 'data' => $code, 'msg' => '验证码不正确！');
        }
        $_SESSION['code'] = '';
        return array('code' => '0', 'data' => [], 'msg' => '验证成功！');
    }

    //检查是否开启session
    private function is_session_started()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

}
<?php

require_once __DIR__ . '/../vendor/autoload.php';

header("Content-type: text/html; charset=utf-8");
//报告所有错误
error_reporting(E_ALL);

define('BASE_PATH', __DIR__ . '/');

/**
 * [p 打印函数]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-05-18T15:05:15+0800
 * @param    string $str [description]
 * @return   [type]                        [description]
 */
function p($str = '')
{
    echo '<pre>';
    print_r($str);
}

/*1、Rest模式get、put、post、delete含义与区别
GET（SELECT）：从服务器取出资源（一项或多项）。
POST（CREATE）：在服务器新建一个资源。
PUT（UPDATE）：在服务器更新资源（客户端提供改变后的完整资源）。
PATCH（UPDATE）：在服务器更新资源（客户端提供改变的属性）。
DELETE（DELETE）：从服务器删除资源。
不常用的HTTP动词
HEAD：获取资源的元数据。
OPTIONS：获取信息，关于资源的那些属性是客户端可以改变的。*/
/**
 * [curlRequest curl通用类]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-06-07T17:32:13+0800
 * @param    string $url [请求链接]
 * @param    string $method [请求类型]
 * @param    array $params [参数]
 * @param    array $header [header头]
 */
function curlRequest($url = '', $method = 'get', $params = array(), $header = array())
{
    $timeout = 15;//超时时长（s）
    $curl = curl_init();//初始化CURL句柄
    curl_setopt($curl, CURLOPT_URL, $url);//设置请求的URL
    curl_setopt($curl, CURLOPT_HEADER, false);// 不要http header 加快效率
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置连接等待时间
    curl_setopt($curl, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
    //echo curl_getinfo($ch, CURLINFO_HEADER_OUT); //官方文档描述是“发送请求的字符串”，其实就是请求的header。这个就是直接查看请求header，因为上面允许查看    

    //初始化header头
    if (empty($header)) {
        $header = array("Content-type:application/json;", "Accept:application/json", "Accept-Language: zh-CN;q=0.8", "User-Agent: php test");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    } else {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }

    //请求类型
    $method = strtolower($method);
    switch ($method) {
        case "get" :
            curl_setopt($curl, CURLOPT_HTTPGET, true);
            break;
        case "post":
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;//设置提交的信息
        case "put" :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
            break;
        case "delete":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
        case "patch":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);     //20170611修改接口，用/id的方式传递，直接写在url中了
    }

    $data = curl_exec($curl);//执行预定义的CURL
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值
    curl_close($curl);//关闭链接释放资源
    return ['code' => $status, 'data' => $data];
}
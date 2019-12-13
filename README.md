信息化公共服务
===============
基于tp5进行扩展
//完美支持tp5.1
//5.0兼容说明
/application/common.php新增env函数
use think\Env;
if (!function_exists('env')) {
    function env($str = '',$end = '')
    {
        return Env::get($str,$end);
    }
}


.env 配置：TP_VERSION=50



基本使用：

1.配置：使用.env
可能涉及到配置：
;缓存配置（开发环境）
REDIS_HOST = '127.0.0.1';
REDIS_PWD = '';
REDIS_PORT = 6379;
REDIS_SELECT = 10;
REDIS_PREFIX = 'work:';

;公共服务域名地址
COMMON_SERVICE_DOMAIN = 'https://imsapi-dev.xinchao.com/common/api';

2.使用
composer require ims/common-service

use ImsCommonService\CommonService;

$CommonService = new CommonService();
$re= $CommonService->getUser();
p($re);die;


3.基本类库
//公共服务
use ImsCommonService\BaseModel;
//基础model（加入缓存封装）
use ImsCommonService\BaseModel;
使用：
class DemoModel extends BaseModel



//基于tp缓存的封装
use ImsCommonService\TpCacheService;
使用：
$cache = new TpCacheService();
$key = 'demo';

//单key使用
//写入
$cache->set($key,time(),20);
//读取
$cache->get($key);
//删除
$cache->delete($key);


//缓存组使用
$keys = 'demo:';
for ($i=0; $i < 10; $i++) {
    //写入
    $cache->saveWithKey($keys, $key.$i, $i, 100);
}

//删除
$cache->delWithKey($keys);
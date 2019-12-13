# 欢迎使用php-service公共服务

**php-servicephp服务：（Excel：导入、导出。pdf：生成、转图片。word：生成）**

执行命令：`composer require ims/php-service`


| 类库  | 说明 |
| ------------- | ------------- |
| use phpService\BaseModel;  | 基础model  |
| use phpService\TpCacheService;  | 缓存封装（redis）  |
| use phpService\CommonService;  | 公共函数  |
| use phpService\Code\Code;  | 动态图片验证码  |
| use phpService\ExcelService;  | phpexcle(导入、导出)  |
| use phpService\WordService;  | word-生成 |
| use phpService\PdfService;  | pdf:生成，转图片  |


### 基础model use ImsCommonService\BaseModel;

> 该类封装了对model的操作，实现查询自动缓存，变化后自动清除缓存。

基本用法：

    <?php
        namespace app\index\model;
		
		use ImsCommonService\BaseModel;

		/**
		 * DemoModel demo 模型
		 * @version 1.0
		 * @author: W_wang
		 * @since: 2019/1/25 9:51
		 */
		class DemoModel extends BaseModel
		{

			public function __construct()
			{
				parent::__construct();
				//初始化表名称
				$this->table = env('DB_DATABASE') . '.wh_demo';
				// 排序字段
				$this->order = 'id desc';
				$this->fields = 'id,name,wh_demo.age';//初始化返回字段
				$this->isShowSql = 1;//初始化返回sql标识0不返回，1返回

				//链表配置(可选)
				$this->joinTable = 'wh_dept';
				$this->joinVal = 'wh_demo.demo_id = wh_dept.id';
				$this->joinType = 'left';
			}
		}
    ?>

```
.env配置说明
;数据库配置（开发环境）
DB_HOST = '127.0.0.1';
DB_USER = 'root';
DB_PWD = '';
DB_DATABASE = 'xinxinst';
DB_PORT = 3306;
DB_PREFIX = '';表前缀
```
----

### 缓存封装（redis） use ImsCommonService\TpCacheService;

> 对redis的封装

基本用法：

    <?php
        namespace app\index\controller;
		
		use think\Controller;
		use ImsCommonService\TpCacheService;

		/**
		 * DemoController demo 控制器
		 * @version 1.0
		 * @author: W_wang
		 * @since: 2019/1/25 9:51
		 */
		class DemoController extends Controller
		{

			public function cache()
			{
				$this->cache = new TpCacheService();
				//缓存key
				$cacheKey = 'demo';
				//写缓存
				$re = $this->cache->set($cacheKey, time(), 100);
				var_dump($re);
				//读缓存
				$re = $this->cache->get($cacheKey);
				var_dump($re);
				//删除缓存
				// $re = $this->cache->delete($cacheKey);
				var_dump($re);

				缓存组用法
				//缓存组key
				$cacheKeyMain = 'demo_main';
				//写缓存（组）
				$re = $this->cache->saveWithKey($cacheKeyMain, $cacheKey . rand(1, 100), array(
					"do" => 1,
					"data" => time()
				), 100);
				var_dump($re);
				//删除缓存（组）
				// $re = $this->cache->delWithKey($cacheKeyMain);
				var_dump($re);
			}
		}
    ?>

```
.env配置说明
;缓存配置（开发环境）
REDIS_HOST = '127.0.0.1';
REDIS_PWD = '';
REDIS_PORT = 6379;
REDIS_SELECT = 1;
REDIS_PREFIX = 'demo:';
```
----


### PHPExcel use phpService\ExcelService;

> PHPExcel 导入、导出

基本用法：
```
use phpService\ExcelService;
$excle = new  ExcelService();

导入：
$file = $_FILES['upfile'];//上传方式
$file = 'file/demo.xlsx';//文件方式
$data = $excle->import($file);

导出：（多个sheet）
//导出Excel
$data = [];
$data [] = [
	'name' => '测试1',//sheet名称
	'title' => ['标题1', '标题2', '标题3'], //表头
	'data' => [['a1', 'b1', 'c1'], ['aa1', 'bb1', 'cc1']] ,//内容
	'color' => 'FFCC0001',//字体颜色
	'color_row' => [['row' => 1, 'col_num' => 1], ['row' => 3, 'col_num' => 2]] //row第几行，col_num列数
];
$data [] = [
	'name' => '测试2',
	'title' => ['标题11', '标题22', '标题33'],
	'data' => [['a2', 'b2', 'c2'], ['aa2', 'bb2', 'cc2']],
];
//1.文件名，2.文件内容，3.保存地址（不填直接下载）
$re = $excle->export('demo', $data , 'file/');
```
    




### PdfService use phpService\PdfService;

> pdf：生成、转图片

基本用法：
```
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
```



### WordService use phpService\WordService;

> word：生成

基本用法：
```

//pdf转成图片
use phpService\WordService;

//实例化
$api = new  WordService();

$data = [];
$data['file_name'] = 'word';
$data['content'] = "word<img src='http://ims.com/file/demo.jpg' /><img src='file/demo.jpg'/>";
$api->index($data, 'http://ims.com/');

```



### End
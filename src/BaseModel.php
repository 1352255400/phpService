<?php

namespace ImsCommonService;

use think\Model;
use think\Db;
use think\App;


/**
 * @version 1.0
 * @author: W_wang
 * @since: 2019/1/25 15:17
 * Class BaseModel 基类 model
 * @package app\index\model
 */
class BaseModel extends Model
{

    /*
     * 初始化表名称
     */
    public $table;
    /*
     * 初始化返回字段
     */
    public $fields;
    /*
     * 排序字段
     */
    public $order;
    /*
     * 初始化返回sql标识0不返回，1返回
     */
    public $isShowSql;
    /*
     * join 表
     */
    public $joinTable;
    /*
     * join 值
     */
    public $joinVal;
    /*
     * join 方式
     */
    public $joinType;
    /*
     * 引入缓存
     */
    public $cache;

    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->joinType = 'left';
        //检查版本5.0无需处理
        $version = env('TP_VERSION', '51');
        if ($version == 50) {
            $this->cache = new Tp50CacheService();
        } else {
            $this->cache = new TpCacheService();
        }
    }


    /**
     * @desc 基本检查
     * @author W_wang
     * @since 2019/1/25
     * @return array
     */
    public function baseCheck()
    {
        if (empty($this->table) || empty($this->fields)) {
            return array('code' => '1000', 'data' => [], 'msg' => '请初始化数据表和字段！');
        }
        if (trim($this->fields) == '*') {
            return array('code' => '1000', 'data' => [], 'msg' => '请不要使用select *');
        }
        return array('code' => '000', 'data' => [], 'msg' => 'ok');
    }


    /**
     * @desc 清空缓存
     * @author W_wang
     * @since 2019/1/25
     * @param string $cacheKey
     * @return mixed
     */
    public function clearCache($cacheKey = '')
    {
        $re = $this->cache->delWithKey($cacheKey);
        return $re;
    }


    /**
     * @desc 获取缓存key
     * @author W_wang
     * @since 2019/1/25
     * @param array $data
     * @return string
     */
    public function getCacheKey($data = array())
    {
        //缓存字段初始化（表、公司、个人、id）
        $companyId = isset($data["company_id"]) ? $data["company_id"] : 0;
        $memberId = isset($data["member_id"]) ? $data["member_id"] : 0;
        $id = isset($data["cache_info_id"]) ? $data["cache_info_id"] : 0;
        return $this->table . '_' . $companyId . '_' . $memberId . '_' . $id;
    }


    /**
     * @desc 执行sql
     * @author W_wang
     * @since 2019/1/25
     * @param string $sql
     * @param int $cacheTime
     * @return array|mixed
     */
    public function query($sql = '', $cacheTime = 7200)
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //检查缓存是否存在
        $groupCacheKey = $this->getCacheKey();
        //初始化缓存key
        $cacheKey = '';
        if ($cacheTime > 0) {
            $cacheKey = $groupCacheKey . '_' . md5($sql);
            $cacheData = $this->cache->get($cacheKey);
            if ($cacheData["do"] == 1) {
                $cacheData["data"]['is_cache'] = 1;
                return array('code' => '000', 'data' => $cacheData["data"], 'msg' => 'ok');
            }
        }
        //检查缓存是否存在 end        

        $list = Db::query($sql);
        $data = array();
        $data['list'] = $list;

        //写入缓存
        if ($cacheTime > 0) {
            $this->cache->saveWithKey($groupCacheKey, $cacheKey, array(
                "do" => 1,
                "data" => $data
            ), $cacheTime);
        }

        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => $data, 'msg' => 'ok', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => $data, 'msg' => 'ok');

    }


    /**
     * @desc 获取列表
     * @author W_wang
     * @since 2019/1/25
     * @param array $where 条件
     * @param int $getTotalType 是否返回总数
     * @param int $cacheTime 缓存时间
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($where = array(), $getTotalType = 0, $cacheTime = 7200)
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //初始化缓存key
        $cacheKey = '';
        //初始化总数缓存key
        $cacheKeyTotal = '';
        //检查缓存是否存在
        //获取主key
        $groupCacheKey = $this->getCacheKey($where);
        // 获取主key值
        $cacheDataGroup = $this->cache->get($groupCacheKey);
        if ($cacheTime > 0) {
            ksort($where);
            $cacheKey = $groupCacheKey . '_' . md5(http_build_query($where) . $getTotalType . $this->order . $this->fields);
            $cacheData = $this->cache->get($cacheKey);
            if ($cacheData["do"] == 1 && !empty($cacheDataGroup) && in_array($cacheKey, $cacheDataGroup)) {
                $cacheData["data"]['is_cache'] = 1;
                return array('code' => '000', 'data' => $cacheData["data"], 'msg' => 'ok');
            }
        }
        //检查缓存是否存在 end


        //分页
        $page = isset($where["p"]) ? $where["p"] : 1;
        $pageSize = isset($where["n"]) ? $where["n"] : 0;
        $limit = ($page - 1) * $pageSize . ',' . $pageSize;
        if ($pageSize == 1) $limit = 1;
        //移除不必要的参数
        if (isset($where['p'])) {
            unset($where['p']);
        }
        if (isset($where['n'])) {
            unset($where['n']);
        }
        if (isset($where['where_no'])) {
            unset($where['where_no']);
        }

        //获取总数
        $total = 0;
        if ($cacheTime > 0) {
            ksort($where);
            $cacheKeyTotal = $groupCacheKey . '_' . md5(http_build_query($where) . $getTotalType . $this->order . 'total');
            $cacheData = $this->cache->get($cacheKeyTotal);
            if ($cacheData["do"] == 1) {
                $total = isset($cacheData["data"]) ? $cacheData["data"] : 0;
            }
        }//获取总数 end


        //移除不必要的参数
        //or
        $whereOr = isset($where['where_or']) ? $where['where_or'] : array();
        if (isset($where['where_or'])) {
            unset($where['where_or']);
        }
        //子查询
        $whereChild = isset($where['where_child']) ? $where['where_child'] : array();
        if (isset($where['where_child'])) {
            unset($where['where_child']);
        }

        //处理where（5.1格式）
        if (!empty($where)) {
            //格式化条件
            $where = $this->initWhere($where);
        }

        //处理where_or（5.1格式）
        if (!empty($whereOr)) {
            //格式化条件
            $whereOr = $this->initWhere($whereOr);
        }

        //处理where_child_where（5.1格式）
        if (isset($whereChild['where']) && !empty($whereChild['where'])) {
            //格式化条件
            $whereChild['where'] = $this->initWhere($whereChild['where']);
        }

        //查询
        //多表
        if ($this->joinTable && $this->joinVal) {
            if (!empty($whereChild)) {
                //多表子查询
                if ($pageSize == 0) {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                            $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                        })
                        ->join($this->joinTable, $this->joinVal, $this->joinType)
                        ->order($this->order)
                        ->select();
                } else {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                            $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                        })
                        ->join($this->joinTable, $this->joinVal, $this->joinType)
                        ->order($this->order)
                        ->limit($limit)
                        ->select();

                    //获取总数
                    if ($getTotalType == 1 && $total == 0) {
                        $total = Db::table($this->table)
                            ->where($where)
                            ->whereOr($whereOr)
                            ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                                $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                            })
                            ->join($this->joinTable, $this->joinVal, $this->joinType)
                            ->count();
                    }
                }
            } else {
                // 多表没有子查询
                if ($pageSize == 0) {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->join($this->joinTable, $this->joinVal, $this->joinType)
                        ->order($this->order)
                        ->select();
                } else {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->join($this->joinTable, $this->joinVal, $this->joinType)
                        ->order($this->order)
                        ->limit($limit)
                        ->select();

                    //获取总数
                    if ($getTotalType == 1 && $total == 0) {
                        $total = Db::table($this->table)
                            ->where($where)
                            ->whereOr($whereOr)
                            ->join($this->joinTable, $this->joinVal, $this->joinType)
                            ->count();
                    }
                }
            }
        } else {
            //单表
            if (!empty($whereChild)) {
                //单表子查询
                if ($pageSize == 0) {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                            $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                        })
                        ->order($this->order)
                        ->select();
                } else {
                    // whereOr
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                            $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                        })
                        ->order($this->order)
                        ->limit($limit)
                        ->select();

                    //获取总数
                    if ($getTotalType == 1 && $total == 0) {
                        $total = Db::table($this->table)
                            ->where($where)
                            ->whereOr($whereOr)
                            ->where($whereChild['field_main'], $whereChild['type'], function ($query) use ($whereChild) {
                                $query->table($whereChild['table_name'])->where($whereChild['where'])->field($whereChild['fields']);
                            })
                            ->count();
                    }
                }
            } else {
                //单表没有子查询
                if ($pageSize == 0) {
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->order($this->order)
                        ->select();
                } else {
                    // whereOr
                    $list = Db::table($this->table)
                        ->field($this->fields)
                        ->where($where)
                        ->whereOr($whereOr)
                        ->order($this->order)
                        ->limit($limit)
                        ->select();

                    //获取总数
                    if ($getTotalType == 1 && $total == 0) {
                        $total = Db::table($this->table)
                            ->where($where)
                            ->whereOr($whereOr)
                            ->count();
                    }
                }
            }
        }

        //格式化时间
        if (!empty($list) && isset($list[0]['time_add'])) {
            foreach ($list as $k => &$v) {
                if (isset($v['time_add']) && is_numeric($v['time_add'])) {
                    $v['time_add_date'] = date('Y-m-d H:i', $v['time_add']);
                }
                if (isset($v['time_update']) && is_numeric($v['time_update'])) {
                    $v['time_update_date'] = date('Y-m-d H:i', $v['time_update']);
                }
            }
        }

        $data = array();
        $data['list'] = $list;
        $total_page = $pageSize > 0 ? ceil($total / $pageSize) : 1;
        $data['pagination'] = [
            'page' => (int)$page,
            'page_size' => (int)$pageSize,
            'total_num' => (int)$total,
            'total_page' => $total_page
        ];

        //写入缓存
        if ($cacheTime > 0) {
            $this->cache->saveWithKey($groupCacheKey, $cacheKey, array(
                "do" => 1,
                "data" => $data
            ), $cacheTime);

            //缓存总数
            $this->cache->saveWithKey($groupCacheKey, $cacheKeyTotal, array(
                "do" => 1,
                "data" => $total
            ), $cacheTime);
        }
        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => $data, 'msg' => 'ok', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => $data, 'msg' => 'ok');

    }


    /**
     * @desc 新增
     * @author W_wang
     * @since 2019/1/25
     * @param array $data
     * @return array
     */
    public function add($data = array())
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //检查写入数据
        if (empty($data)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'insert data is null ！');
        }

        //检查重复提交
        ksort($data);
        $arrCache = array();
        foreach ($data as $k => $v) {
            if (!in_array($k, array('create_time', 'update_time', 'time_add', 'time_update'))) {
                $arrCache[$k] = $v;
            }
        }
        $cacheKey = md5(http_build_query($arrCache));
        $cacheData = $this->cache->get($cacheKey);
        if (!empty($cacheData)) {
            return array('code' => '1000', 'data' => [], 'msg' => '提交太频繁了');
        }
        $this->cache->set($cacheKey, 1, 5);
        //检查重复提交

        //写入数据
        try {
            $addId = Db::table($this->table)->insertGetId($data);
        } catch (\Exception $e) {
            return array('code' => '1000', 'data' => ['e' => $e], 'msg' => '新增异常', 'add_data' => $data);
        }

        if (empty($addId)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'insert data is error ！');
        }

        //清除缓存
        $dataCache = array();
        $cacheKey = $this->getCacheKey($dataCache);
        $this->clearCache($cacheKey);

        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => ['id' => $addId], 'msg' => '新增成功！', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => ['id' => $addId], 'msg' => '新增成功！');
    }


    /**
     * @desc 批量新增
     * @author W_wang
     * @since 2019/1/25
     * @param array $data
     * @return array
     */
    public function addAll($data = array())
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //检查写入数据
        if (empty($data)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'insert data is null ！');
        }

        //写入数据
        try {
            $addId = Db::table($this->table)->insertAll($data);
        } catch (\Exception $e) {
            return array('code' => '1000', 'data' => ['e' => $e], 'msg' => '批量新增异常', 'add_data' => $data);
        }

        if (empty($addId)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'insert data is error ！');
        }

        //清除缓存
        $dataCache = array();
        $cacheKey = $this->getCacheKey($dataCache);
        $this->clearCache($cacheKey);

        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => [], 'msg' => '批量新增成功！', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => [], 'msg' => '批量新增成功！');
    }


    /**
     * @desc 编辑
     * @author W_wang
     * @since 2019/1/25
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit($data = array(), $where = array())
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //检查写入数据
        if (empty($data)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'update data is null ！');
        }

        //检查条件
        if (empty($where)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'update where is null ！');
        }

        //格式化条件
        $where = $this->initWhere($where);

        //操作数据库
        try {
            $re = Db::table($this->table)->where($where)->update($data);
        } catch (\Exception $e) {
            return array('code' => '1000', 'data' => ['e' => $e], 'msg' => '编辑异常', 'edit_data' => $data);
        }

        if (empty($re)) {
            if ($this->isShowSql == 1 && config('app_debug') == true) {
                return array('code' => '1000', 'data' => [], 'msg' => 'update data is error ！', 'sql' => Db::getLastSql());
            }
            return array('code' => '1000', 'data' => [], 'msg' => 'update data is error ！');
        }

        //清除缓存
        $dataCache = array();
        $cacheKey = $this->getCacheKey($dataCache);
        $this->clearCache($cacheKey);

        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => [], 'msg' => '编辑成功！', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => [], 'msg' => '编辑成功！');
    }


    /**
     * @desc 删除
     * @author W_wang
     * @since 2019/1/25
     * @param array $where
     * @param int $limit
     * @return array
     */
    public function del($where = array(), $limit = 0)
    {
        //基本检查
        $baseCheck = $this->baseCheck();
        if ($baseCheck['code'] != '000') {
            return $baseCheck;
        }

        //检查条件
        if (empty($where)) {
            return array('code' => '1000', 'data' => [], 'msg' => 'delete where is null ！');
        }

        //格式化条件
        $where = $this->initWhere($where);

        //操作数据库
        try {
            $re = Db::table($this->table)->where($where)->limit($limit)->delete();
        } catch (\Exception $e) {
            return array('code' => '1000', 'data' => ['e' => $e], 'msg' => '删除异常！');
        }

        if (empty($re)) {
            if ($this->isShowSql == 1 && config('app_debug') == true) {
                return array('code' => '1000', 'data' => [], 'msg' => 'delete data is error ！', 'sql' => Db::getLastSql());
            }
            return array('code' => '1000', 'data' => [], 'msg' => 'delete data is error ！');
        }

        //清除缓存
        $dataCache = array();
        $cacheKey = $this->getCacheKey($dataCache);
        $this->clearCache($cacheKey);

        if ($this->isShowSql == 1 && config('app_debug') == true) {
            return array('code' => '000', 'data' => [], 'msg' => '删除成功！', 'sql' => Db::getLastSql());
        }
        return array('code' => '000', 'data' => [], 'msg' => '删除成功！');
    }


    /**
     * @desc 获取表字段
     * @author W_wang
     * @since 2019/1/25
     * @param int $type
     * @return array|string
     */
    public function getTableFields($type = 0)
    {
        $sql = 'show full columns from ' . $this->table;
        $data = Db::query($sql);
        $fields = '';
        foreach ($data as $k => $v) {
            if ($type == 0) {
                $fields = $fields . ',' . $v['Field'];
            } else {
                $fields = $fields . ",'" . $v['Field'] . "'";
            }
        }
        $fields = trim($fields, ',');
        return $fields;
    }


    /**
     * @desc 格式化where條件(兼容tp5写法)
     * @author W_wang
     * @since 2019/8/1
     * @param array $where
     * @return array
     */
    private function initWhere($where = [])
    {
        //检查版本5.0无需处理
        $version = env('TP_VERSION', '51');
        if ($version == 50) {
            return $where;
        }

        if (!empty($where)) {
            $whereTmp = array();
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    $whereTmp[] = array($k, $v[0], $v[1]);
                } else {
                    $whereTmp[] = array($k, '=', $v);
                }
            }
            $where = $whereTmp;
        }
        return $where;
    }


}


<?php

namespace phpService;

use think\Cache;


/**
 * [TpCacheService tp缓存 Logic]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2011-11-11T11:11:11
 */
class Tp50CacheService
{
    //定义表前缀
    private $keyPrefix;
    //定义缓存驱动类型
    private $store;

    public function __construct()
    {
        //初始化缓存配置
        $redisConfig = config('cache.redis');
        //缓存前缀
        $this->keyPrefix = isset($redisConfig['prefix']) && !empty($redisConfig['prefix']) ? '' : 'cache:';
        //指定缓存驱动（redis）
        $this->store = 'redis';
    }

    /**
     * [set 设置缓存]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $id
     * @param string $data
     * @param int $time
     * @return bool
     */
    public function set($id = '', $data = '', $time = 0)
    {
        $time = intval($time);
        $time = $time > 0 ? $time : 7200;
        if (!$data || !$id) {
            return false;
        }
        $id = $this->keyPrefix . $id;
        $re = Cache::store($this->store)->set($id, json_encode($data), $time);
        return $re;
    }

    /**
     * [get 获取缓存]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $id
     * @return mixed
     */
    public function get($id = '')
    {
        $id = $this->keyPrefix . $id;
        $re = Cache::store($this->store)->get($id);
        $re = json_decode($re, true);
        return $re;
    }

    /**
     * [delete 删除缓存]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $id
     * @return bool
     */
    public function delete($id = '')
    {
        if (!$id) {
            return false;
        }
        $id = $this->keyPrefix . $id;
        $re = Cache::store($this->store)->rm($id);
        return $re;
    }

    /**
     * [saveWithKey 缓存组]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $ids
     * @param string $id
     * @param string $data
     * @param int $time
     * @return bool
     */
    public function saveWithKey($ids = '', $id = '', $data = '', $time = 0)
    {
        if (!$ids || !$id) {
            return false;
        }

        $arrayVal = $this->get($ids);
        if (is_array($arrayVal)) {
            if (!in_array($id, $arrayVal)) {
                array_push($arrayVal, $id);
                $re = $this->set($ids, $arrayVal, $time);
                if (!$re) {
                    return $re;
                }
            }
        } else {
            $arrayVal = array($id);
            $re = $this->set($ids, $arrayVal, $time);
            if (!$re) {
                return $re;
            }
        }

        $re = $this->set($id, $data, $time);
        return $re;
    }

    /**
     * [delWithKey 删除缓存组]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $ids
     * @return bool
     */
    public function delWithKey($ids = '')
    {
        if (!$ids) {
            return false;
        }

        $arrayVal = $this->get($ids);
        if (is_array($arrayVal)) {
            foreach ($arrayVal as $v) {
                $this->delete($v);
            }
        }
        $re = $this->delete($ids);
        return $re;
    }
}

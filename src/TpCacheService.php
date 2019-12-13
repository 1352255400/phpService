<?php

namespace phpService;

use think\facade\Cache;

/**
 * [TpCacheService tp缓存 Logic]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2011-11-11T11:11:11
 */
class TpCacheService
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
     * @param string $key
     * @param string $data
     * @param int $time
     * @return bool
     */
    public function set($key = '', $data = '', $time = 0)
    {
        $time = intval($time);
        $time = $time > 0 ? $time : 7200;
        if (!$data || !$key) {
            return false;
        }
        $key = $this->keyPrefix . $key;
        $re = Cache::store($this->store)->set($key, json_encode($data), $time);
        return $re;
    }

    /**
     * [get 获取缓存]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $key
     * @return mixed
     */
    public function get($key = '')
    {
        $key = $this->keyPrefix . $key;
        $re = Cache::store($this->store)->get($key);
        $re = json_decode($re, true);
        return $re;
    }

    /**
     * [delete 删除缓存]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $key
     * @return bool
     */
    public function delete($key = '')
    {
        if (!$key) {
            return false;
        }
        $key = $this->keyPrefix . $key;
        $re = Cache::store($this->store)->rm($key);
        return $re;
    }

    /**
     * [saveWithKey 缓存组]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $keys
     * @param string $key
     * @param string $data
     * @param int $time
     * @return bool
     */
    public function saveWithKey($keys = '', $key = '', $data = '', $time = 0)
    {
        if (!$keys || !$key) return false;

        //获取主缓存数据
        $arrayVal = $this->get($keys);
        //向主缓存追加子缓存key
        if (is_array($arrayVal)) {
            if (!in_array($key, $arrayVal)) {
                array_push($arrayVal, $key);
                $re = $this->set($keys, $arrayVal, $time);
                if (!$re) {
                    return $re;
                }
            }
        } else {
            $arrayVal = array($key);
            $re = $this->set($keys, $arrayVal, $time);
            if (!$re) {
                return $re;
            }
        }

        //写入缓存数据
        $re = $this->set($key, $data, $time);
        return $re;
    }

    /**
     * [delWithKey 删除缓存组]
     * @Author   W_wang
     * @since 2019/3/25
     * @param string $keys
     * @return bool
     */
    public function delWithKey($keys = '')
    {
        if (!$keys) return false;

        //删除子缓存数据
        $arrayVal = $this->get($keys);
        if (is_array($arrayVal)) {
            foreach ($arrayVal as $v) {
                $this->delete($v);
            }
        }

        //删除主缓存
        $re = $this->delete($keys);
        return $re;
    }
}

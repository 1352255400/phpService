<?php

namespace phpService;

/**
 * [CommonService 公共服务]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-04-17T13:51:51+0800
 */
class CommonService
{
    /*
     * 初始化域名配置
     */
    private $CommonServiceDomain, $cache;

    public function __construct()
    {
        //初始化域名配置
        $this->CommonServiceDomain = env('COMMON_SERVICE_DOMAIN');
        //检查版本5.0无需处理
        $version = env('TP_VERSION', '51');
        if ($version == 50) {
            $this->cache = new Tp50CacheService();
        } else {
            $this->cache = new TpCacheService();
        }
    }


    /**
     * @desc 获取用户
     * @author W_wang
     * @since 2019/2/20
     * @param array $where =>['type'=>'all','field'=>'isIncludeLeave','value'=>'false']
     * @return array|mixed
     */
    public function getUser($where = [])
    {
        //查询方式（all、list、info、department）
        $type = isset($where['type']) ? $where['type'] : 'all';
        switch ($type) {
            case 'all':
                //参数：['type'=>'all','field'=>'isIncludeLeave','value'=>'false']
                //字段：includeLeave
                $field = 'includeLeave';
                //字段值：一个或者多个（逗号隔开）
                $value = isset($where['value']) ? $where['value'] : 'false';
                //1.获取所有人员信息数据(是否包含离职 true:包含 false:不包含)
                $url = $this->CommonServiceDomain . '/employee/listAllEmployees?' . $field . '=' . $value;
                break;
            case 'info':
                //参数：['type'=>'info','field'=>'sid','value'=>'1']
                //字段：sid、jobCode、dingId
                $field = isset($where['field']) ? $where['field'] : 'sid';
                //字段值
                $value = isset($where['value']) ? $where['value'] : '';
                //2通过(sid、jobCode、dingId)其中之一获取人员详情
                $url = $this->CommonServiceDomain . '/employee/getEmployeeInfo?pattern=' . $field . '&param=' . $value;
                break;
            case 'list':
                //参数：['type'=>'list','field'=>'sid','value'=>'1,2']
                //字段：sid、jobCode、dingId
                $field = isset($where['field']) ? $where['field'] : 'sid';
                //字段值：一个或者多个（逗号隔开）
                $value = isset($where['value']) ? $where['value'] : '1,2';
                //3通过(sid、jobCode、dingId)其中之一列表获取多个人员详
                $url = $this->CommonServiceDomain . '/employee/listEmployees?pattern=' . $field . '&param=' . $value;;
                break;
            case 'department':
                //参数：['type'=>'department','field'=>'isIncludeLeave','value'=>'92706657']
                //字段：sid、jobCode、dingId
                $field = 'deptId';
                //字段值：一个或者多个（逗号隔开）
                $value = isset($where['value']) ? intval($where['value']) : 0;
                //4.通过部门id获取部门下所有人员信息
                $url = $this->CommonServiceDomain . '/employee/listEmployeesByDeptId?' . $field . '=' . $value;
                break;
            case 'pageEmployeesFuzzy':
                $url = $this->CommonServiceDomain . '/employee/pageEmployeesFuzzy?pageNum=' . $where['pageNum'] . '&pageSize=' . $where['pageSize'] . '&fuzzyStr=' . urlencode($where['fuzzyStr']);
                break;
            default:
                return array('code' => '1000', 'data' => [], 'msg' => '参数错误！');
        }

        //获取数据
        $data = $this->curlRequest($url);
        if (isset($data['code']) && $data['code'] == '000') {
            return array('code' => '000', 'data' => $data['data'], 'msg' => '获取用户成功！');
        }

        return array('code' => '1000', 'data' => $data, 'msg' => '获取数据失败！');
    }

    /**
     * @desc 获取部门部门信息
     * @author W_wang
     * @since 2019/2/20
     * @param array $where =>['type'=>'all','field'=>'isIncludeLeave','value'=>'false']
     * @return array
     */
    public function getDepartment($where = [])
    {
        //查询方式（all、list、one、department）
        $type = isset($where['type']) ? $where['type'] : 'all';
        switch ($type) {
            case 'all':
                //参数：['type'=>'all','isHasTree'=>'false','isHasUser'=>'false']
                //字段：是否展示树形层级关系
                $isHasTree = isset($where['isHasTree']) ? $where['isHasTree'] : 'false';
                //字段值：是否带有人员信息
                $isHasUser = isset($where['isHasUser']) ? $where['isHasUser'] : 'false';

                //5获取所有部门列表 X
                $url = $this->CommonServiceDomain . '/department/listAllDepartments?hasTree=' . $isHasTree . '&hasEmployee=' . $isHasUser;
                break;
            case 'getSuperiorDepartment':
                //参数：['type'=>'getSuperiorDepartment','field'=>'sid','value'=>'28']
                //字段：sid、jobCode、dingId
                $field = isset($where['field']) ? $where['field'] : 'sid';
                //字段值
                $value = isset($where['value']) ? $where['value'] : '';

                //6根据人员(sid、jobCode、dingId)其一获取部门至顶层部门树形结构
                $url = $this->CommonServiceDomain . '/department/getSuperiorDepartments?pattern=' . $field . '&param=' . $value;//X
                break;
            case 'info':
                //参数：['type'=>'info','field'=>'deptId','value'=>'92706657']
                //字段：deptId、deptCode
                $field = isset($where['field']) ? $where['field'] : 'deptId';
                //字段值：一个或者多个（逗号隔开）
                $value = isset($where['value']) ? $where['value'] : '';

                //7.根据(deptId、deptCode)其中之一获取部门详情
                $url = $this->CommonServiceDomain . '/department/getDepartment?pattern=' . $field . '&param=' . $value;
                break;
            case 'list':
                //参数：['type'=>'list','field'=>'deptId','value'=>'79198393,92703394']
                //字段：deptId、deptCode
                $field = isset($where['field']) ? $where['field'] : 'deptId';
                //字段值：一个或者多个（逗号隔开）
                $value = isset($where['value']) ? $where['value'] : '';

                //8.根据(deptId、deptCode)其中之一列表获取多个部门详细信息列表 X
                $url = $this->CommonServiceDomain . '/department/listDepartments?pattern=' . $field . '&param=' . $value;
                break;

            default:
                return array('code' => '1000', 'data' => [], 'msg' => '参数错误！');
        }

        //获取数据
        $data = $this->curlRequest($url);
        if (isset($data['code']) && $data['code'] == '000') {
            return array('code' => '000', 'data' => $data['data'], 'msg' => '获取部门成功！');
        }

        return array('code' => '1000', 'data' => $data, 'msg' => '获取数据失败！');
    }

    /**
     * @desc 发送消息
     * @author W_wang
     * @since 2019/2/20
     * @param array $data
     * @return array|mixed
     */
    public function sendMessage($data = [])
    {
        $userIdList = isset($data['userIdList']) ? $data['userIdList'] : '';
        $content = isset($data['content']) ? $data['content'] : '';
        if (empty($userIdList) || empty($content)) {
            return array('code' => '1000', 'data' => [], 'msg' => '接收人和消息内容不能为空！');
        }

        //10.发送工作通知消息
        $data = array();
        $data['agentId'] = env('DING_AGENT_ID');//必填
        $data['userIdList'] = $userIdList;//接收者的用户userid列表，最大列表长度：20
        //$data['deptIdList'] = ;//接收者的部门id列表，最大列表长度：20, 接收者是部门id下(包括子部门下)的所有用户
        //$data['toAllUser'] = ;//是否发送给企业全部用户
        $msg = [];
        $msg['msgtype'] = 'text';
        $msg['text'] = ['content' => $content . time()];
        $data['msg'] = json_encode($msg);//必填
        $url = $this->CommonServiceDomain . '/message/sendMessage';
        $data = $this->curlRequest($url, 'post', $data, []);

        return $data;
    }


    /**
     * 发送卡片消息
     * @author XuLongCai
     * @date 2019/4/3 下午5:13
     * @param $userIdList
     * @param $content
     * @return array|mixed
     */
    public function sendDingMsgCard($userIdList, $content, $msgtype = 'action_card')
    {
        $msg = [];
        if ($msgtype == 'action_card') {
            //卡片消息
            $msg['msgtype'] = "action_card";
            $msg['action_card'] = $content;
        } else {
            $msg['msgtype'] = "text";
            $msg['text'] = $content;
        }
        $data = array();
        $data['agentId'] = env('DING_AGENT_ID');//必填
        $data['sids'] = $userIdList;
        $data['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
        $url = $this->CommonServiceDomain . '/message/sendMessageBySids';
        $data = $this->curlRequest($url, 'post', $data, []);
        return $data;
    }


    /**
     * @desc 发送消息
     * @author W_wang
     * @since 2019/2/20
     * @param array $data
     * @return array|mixed
     */
    public function sendMultipleMessages($data)
    {
        $url = $this->CommonServiceDomain . '/message/sendMultipleMessages';
        $data = $this->curlRequest($url, 'post', $data);
        return $data;
    }

    /**
     * @desc 获取token用户信息
     * @author W_wang
     * @since 2019/3/28
     * @return array|int
     */
    public function getTokenInfo($token = '')
    {
        //获取token
        $token = !empty($token) ? $token : request()->header('Authorization');
        $token = $token ? trim(substr($token, 6)) : '';
        if (empty($token)) {
            return ['code' => '1000', 'data' => [], 'msg' => '请传入token'];
        }

        //获取用户信息
        $tokenUser = $this->cache->get($token);
        if (empty($tokenUser)) {
            //通过token获取用户信息（接口）
            $url = env('PLATFORM_SERVICE_DOMAIN', 'http://platform-service/');
            $url = $url . 'auth/checkAccessToken?accessToken=' . $token;
            $data = $this->curlRequest($url);
            if (isset($data['code']) && $data['code'] == '000') {
                //缓存用户信息
                $tokenUser = isset($data['data']['userInfo']) ? $data['data']['userInfo'] : [];
                $this->cache->set($token, $tokenUser, 7200);
            }
        }

        if (empty($tokenUser)) {
            return ['code' => '1000', 'data' => [$url], 'msg' => '登录失败'];
        }
        return ['code' => '000', 'data' => $tokenUser, 'msg' => 'ok'];
    }

    /**
     * @desc 退出登录
     * @author W_wang
     * @since 2019/8/26
     * @param string $token
     * @return array|mixed
     */
    public function logout($token = '')
    {
        //获取token
        $token = !empty($token) ? $token : request()->header('Authorization');
        $token = $token ? trim(substr($token, 6)) : '';
        if (empty($token)) {
            return ['code' => '1000', 'data' => [], 'msg' => '请传入token'];
        }

        //退出登录
        $url = env('PLATFORM_SERVICE_DOMAIN', 'http://platform-service/');
        $re = $this->curlRequest($url . 'auth/logout', 'post', json_encode(['accessToken' => $token]));
        if ($re['code'] != '000') {
            return $re;
        }

        //清除缓存
        $this->cache->delete($token);
        return $re;
    }


    /**
     * [curlRequest curl请求]
     * @Author   W_wang
     * @email    1352255400@qq.com
     * @DateTime 2018-03-04T15:37:17+0800
     * @param    [type]                   $url      [链接]
     * @param    [type]                   $data     [参数：空get请求]
     * @param    integer $is_build [description]
     * @return   [type]                             [description]
     */
    private function curlRequest($url, $type = 'get', $data = '', $headers = array('Content-Type: application/json; charset=utf-8'))
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($type == 'post') {
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    /**
     * 发送短信服务
     * @author TianChao
     * @since 2019/9/10
     * @param $sids
     * @param $type
     * @param $templateParas
     * @return array
     */
    public function sendMsgMessage($sids, $type, $templateParas)
    {
        switch ($type) {
            case 1://已付款待确认
                $data = [
                    'code' => 'T021',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 2://异常订单
                $data = [
                    'code' => 'T020',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 3://待发货
                $data = [
                    'code' => 'T019',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 4://待收货 物流
                $data = [
                    'code' => 'T018',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 5://待收货 虚拟
                $data = [
                    'code' => 'T017',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 6://待收货 自提
                $data = [
                    'code' => 'T016',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            case 7://商品30天过期
                $data = [
                    'code' => 'T015',
                    'templateParas' => [$templateParas],
                    'signature' => '潮人公社',
                ];
                break;
            default:
        }
        //根据sid获取所有人员的电话号码
        $users_info = $this->getUser(['type' => 'list', 'field' => 'sid', 'value' => $sids]);
        if (!isset($users_info['code']) || $users_info['code'] != '000') {
            return ['code' => '500', 'data' => [], 'msg' => '获取人员信息错误'];
        }

        //发送短信
        $url = $this->CommonServiceDomain . '/sms/sendSms';
        foreach ($users_info['data'] as $v) {
            $data['phoneNum'] = $v['phone'];
            $this->curlRequest($url, 'post', json_encode($data));
        }
    }

}

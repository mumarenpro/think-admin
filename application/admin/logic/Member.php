<?php

namespace app\admin\logic;

use think\facade\Config;
use think\facade\App;
use think\Db;
use Filipac\Ip;
use helper\Helper;

class Member
{

    public $errorCode = EC_SUCCESS;

    public function login($params)
    {
        if (!captcha_check($params ['captcha'], '', Config::get('captcha'))) {
            $this->errorCode = EC_AD_REG_CAPTCHA_ERROR;
            return false;
        };

        // 获取管理员信息
        $info = App::model('Member')->where([
            'nickname' => $params ['nickname']
        ])->find();

        if (!$info) {
            $this->errorCode = EC_AD_MEMBER_NOT_EXIST;
            return false;
        }

        if (md5($params['password'] . $info['salt']) != $info['password']) {
            $this->errorCode = EC_AD_MEMBER_PASSWORD_ERROR;
            return false;
        }

        // 生成Token
        $memberInfo = [
            'uid' => $info ['uid'],
            'nickname' => $params ['nickname'],
            'userAgent' => $params ['userAgent'],
        ];

        $memberInfo ['token'] = Helper::generateToken($memberInfo);
        //获取管理员优先级
        $groupAccess = Db::table('ds_auth_group_access_new')->alias('a')->join('ds_auth_group_new b', 'a.group_id = b.id', 'LEFT')->field('a.*,b.sort')->where('a.uid', $info['uid'])->order('b.sort asc')->select();
        $memberInfo ['sort'] = !empty($groupAccess) ? $groupAccess[0]['sort'] : 999999;

        // 获取访问授权列表返回
        $authGroupLogic = App::model('AuthGroup', 'logic');
        $authAccessList = $authGroupLogic->getAccessList($memberInfo);
        $memberInfo ['authAccessList'] = $authAccessList;

        // 修改管理员表登录信息
        $updateData = array();
        $updateData ['login'] = $info ['login'] + 1;
        $updateData ['last_login_time'] = time();
        $updateData ['last_login_ip'] = ip2long(Ip::get());
        App::model('Member')->save($updateData, [
            'uid' => $info ['uid']
        ]);
        //记录行为
        App::model('AuthGroup', 'logic')->actionLog('user_login', 'Member', $info ['uid'], $info ['uid']);

        return $memberInfo;
    }

}
<?php

namespace app\admin\controller;

use Filipac\Ip;
use think\facade\Request;
use think\facade\App;
use think\facade\Config;
use data_format\DataFormat;

class Member
{

    /**
     * 管理员登录
     * @return \think\response\Json
     */
    public function login()
    {
        $params ['nickname'] = Request::param('nickname');
        $params ['password'] = Request::param('password');
        $params ['captcha'] = Request::param('captcha');

        //获取userAgent
        $params['userAgent'] = Request::header('user-agent');
        try {
            $memberLogic = App::model('Member', 'logic');
            $memberInfo = $memberLogic->login($params);
            $token = $memberInfo ['token'];
            unset ($memberInfo ['token']);
            return json([
                'errorcode' => $memberLogic->errorCode,
                'message' => Config::get('errorcode') [$memberLogic->errorCode],
                'data' => DataFormat::outputFormat($memberInfo),
            ], 200, [
                'Auth-Token' => $token,
            ]);
        } catch (\Exception $e) {
            return json(
                [
                    'errorcode' => EC_AD_MEMBER_LOGIN_ERROR,
                    'message' => $e->getMessage(),
                    'data' => []
                ],
                500,
                ['Auth-Token' => '']
            );
        }
    }


}
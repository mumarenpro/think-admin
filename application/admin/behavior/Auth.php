<?php

namespace app\admin\behavior;

use Firebase\JWT\JWT;
use logger\Logger;
use Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;
use think\facade\App;

class Auth
{

    /**
     * 验证Token
     */
    public function run()
    {
        Logger::save('Auth->run', 'auth-debug.log');
        // 排除不执行校验Toen的方法, 配置文件中配置
        $authAllow = Config::get('common.admin_auth_allow');
        $controller = Request::controller();
        $action = $controller . '/' . Request::action(true);
        if (in_array($controller, $authAllow ['controller']) || in_array($action, $authAllow ['action'])) {
            return;
        }

        // 从header获取Token和userAgent
        $token = Request::header('auth-token');
        $userAgent = Request::header('user-agent');

        // 验证token合法性
        try {
            $tokenInfo = Jwt::decode($token, Config::get('common.token_sign_key'));
        } catch (Exception $exception) {
            Logger::save($exception->getMessage(), 'auth.log');
            $data = [
                'errorcode' => EC_AD_TOKEN_ERROR,
                'message' => Config::get('errorcode') [EC_AD_TOKEN_ERROR]
            ];
            json($data, 200, get_cross_headers())->send();
            exit ();
        }

        if (false === $tokenInfo) {
            $data = [
                'errorcode' => EC_AD_TOKEN_ERROR,
                'message' => Config::get('errorcode') [EC_AD_TOKEN_ERROR]
            ];
            json($data, 200, get_cross_headers())->send();
            exit ();
        }


        // 验证是否被人踢下线或者token过期
        // redis里存每个uid对应token的创建时间，如果redis里的创建时间大于token里面的过期时间，则自动退出,如果cookie不一样，则认为是不同用户，强制退出
        $tokenExpireTime = Cache::tag('member')->get(Config::get('token_cache_key') . $tokenInfo->uid . '_expire');
        $tokenUserAgent = Cache::tag('member')->get(Config::get('token_cache_key') . $tokenInfo->uid);

        if (empty ($tokenExpireTime) || time() > $tokenExpireTime || empty ($tokenUserAgent) || $userAgent != $tokenUserAgent) {
            $data = [
                'errorcode' => EC_AD_OTHER_LOGIN,
                'message' => Config::get('errorcode') [EC_AD_OTHER_LOGIN]
            ];
            json($data, 200, get_cross_headers())->send();
            exit ();
        }

        // 验证通过，记录UID
        define('MEMBER_ID', $tokenInfo->uid);
        define('MEMBER_NAME', $tokenInfo->nickname);

        // 刷新token
        $memberInfo = [
            'uid' => $tokenInfo->uid,
            'nickname' => $tokenInfo->nickname,
            'userAgent' => $userAgent,
        ];
        App::model('Member', 'logic')->generateToken($memberInfo);
    }
}
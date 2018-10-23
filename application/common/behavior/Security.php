<?php

namespace app\common\behavior;

use think\facade\Cache;
use think\facade\Config;
use think\Controller;
use think\facade\Env;
use think\facade\Log;
use think\facade\Request;
use think\facade\Response;
use think\facade\Hook;
use think\exception\HttpResponseException;
use logger\Logger;

class Security extends Controller
{

    /**
     * 验证签名以及重放攻击检测
     */
    public function run()
    {
        //排除不执行的方法, 配置文件中配置
        $authAllow = Config::get('common.security_allow');
        $controller = Request::controller();
        $action = Request::controller() . '/' . Request::action(true);
        $module = Request::module();
        $type = $this->getResponseType();
        if (in_array($controller, $authAllow['controller']) || in_array($action, $authAllow['action']) || in_array($module, $authAllow['module'])) {
            Logger::save($module.'方法不走安全验证', 'debug.log');
            return true;
        }
        $params = Request::post();
        $requestSign = Request::header('Sign');
        //是否需要验证签名
        if (Env::get('app.sign_check') && !empty($params)) {
            $linkString = build_link_string($params);
            $signKey = Env::get('app.sign_key');
            $sign = md5($linkString . $signKey);
            if ($requestSign !== $sign) {
                Log::write('host:' . Request::host() . ' | linkString:' . $linkString . ' | signKey:' . $signKey . ' | sign:' . $sign . ' | requestSign:' . $requestSign, 'app_info');
                $data = ['errorcode' => EC_SIGN_ERROR, 'message' => Config::pull('errorcode')[EC_SIGN_ERROR]];
                $response = Response::create($data, $type);
                Hook::listen('Cross', $response);
                throw new HttpResponseException($response);
            }
        }

        //重放攻击验证，timestamp和nonce不为空的时候才验证
        //验证timestamp, 一分钟内有效
        $validTime = 60;

        if (isset($params['timestamp']) && $params['timestamp'] > 0 && time() - $params['timestamp'] > $validTime) {
            $data = ['errorcode' => EC_API_TIMEOUT, 'message' => Config::pull('errorcode')[EC_API_TIMEOUT]];
            $response = Response::create($data, $type);
            Hook::listen('Cross', $response);
            throw new HttpResponseException($response);
        }

        //验证nonce是否存在
        if (isset($params['nonce']) && !empty($params['nonce'])) {
            $nonceCacheKey = Config::get('cache_option.prefix')['nonce_cache_key'] . $params['nonce'];
            $nonce = Cache::get($nonceCacheKey);
            if ($nonce) {
                $data = ['errorcode' => EC_NONCE_USED, 'message' => Config::pull('errorcode')[EC_NONCE_USED]];
                $response = Response::create($data, $type);
                Hook::listen('Cross', $response);
                throw new HttpResponseException($response);
            }
            Cache::set($nonceCacheKey, $params['nonce'], $validTime);
        }
    }
}
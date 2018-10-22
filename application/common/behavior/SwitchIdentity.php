<?php

namespace app\common\behavior;

use think\Controller;
use think\facade\Env;
use think\facade\Request;
use think\facade\Response;
use think\facade\Hook;
use think\facade\Config;
use think\exception\HttpResponseException;

class SwitchIdentity extends Controller
{

    public function run($identity)
    {
        $action = Request::controller() . '/' . Request::action(true);
        $authIdentity = Request::header('Auth-Identity') ?: Request::param('identity');
        if (isset($identity) && $identity && in_array($identity, ['normal', 'guest', 'special'])) {
            $status = $identity;
        } elseif (in_array($action, Config::get('common.guest_action'))) {
            $status = 'guest';
        } elseif (in_array($action, Config::get('common.agent_action'))) {
            $status = 'special';
        } elseif ($authIdentity) {
            $status = $authIdentity;
        } else {
            $status = 'normal';
        }

        if (!in_array($status, ['normal', 'guest', 'special'])) {
            $data = ['errorcode' => EC_IDENTITY_STATUS_ERROR, 'message' => Config::pull('errorcode')[EC_IDENTITY_STATUS_ERROR]];
            $type = $this->getResponseType();
            $response = Response::create($data, $type);
            Hook::listen('Cross', $response);
            throw new HttpResponseException($response);
        }

        $GLOBALS['auth_identity'] = $status;

        //重新加载数据库配置文件和缓存文件
        $dbFilename = Env::get('config_path') . DIRECTORY_SEPARATOR . 'database.php';
        $cacheFilename = Env::get('config_path') . DIRECTORY_SEPARATOR . 'cache.php';
        Config::load($dbFilename, 'database');
        Config::load($cacheFilename, 'cache');
    }
}
<?php

namespace app\common\behavior;

use think\Controller;
use think\facade\Hook;
use think\facade\App;
use think\facade\Request;
use think\facade\Response;
use think\exception\HttpResponseException;

class Validate extends Controller
{

    private $action;

    /**
     * 全局验证
     * @return bool
     */
    public function run()
    {
        $controller = Request::controller();
        $action = Request::action(true);
        $this->action = $action;
        try {
            $validate = App::model($controller, 'validate');
        } catch (\Exception $e) {
            return true;
        }
        if (empty($validate->scene)) {
            return true;
        }
        $arrActionName = array_filter(array_keys($validate->scene), function ($v) {
            return $this->action === $v ? true : false;
        });
        if (!empty($arrActionName)) {
            $strActionName = reset($arrActionName);
            $params = Request::param();
            if (!$validate->scene($strActionName)->check($params)) {
                $data = ['errorcode' => EC_PARAMS_ILLEGAL, 'message' => $validate->getError()];
                $type = $this->getResponseType();
                $response = Response::create($data, $type);
                Hook::listen('Cross', $response);
                throw new HttpResponseException($response);
            }
        };
        return true;
    }
}
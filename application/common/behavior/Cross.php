<?php

namespace app\common\behavior;

use think\Controller;
use think\facade\Response;

class Cross extends Controller
{

    public function run($dispatch)
    {
        $hostName = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "*";
        $headers = [
            'Access-Control-Allow-Origin' => $hostName,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'x-token, x-uid, x-token-check, x-requested-with, Host, Sign, Auth-Token, Auth-Identity, Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
        ];
        if ($dispatch instanceof Response) {
            $dispatch->header($headers);
        } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            json()->code(200)->header($headers)->send();
        }
    }
}
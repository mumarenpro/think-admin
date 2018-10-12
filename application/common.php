<?php
// 应用公共文件

function get_cross_headers()
{
    $host_name = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "*";
    $headers = [
        "Access-Control-Allow-Origin" => $host_name,
        "Access-Control-Allow-Credentials" => 'true',
        "Access-Control-Allow-Headers" => "x-token,x-uid,x-token-check,x-requested-with,content-type,Host,auth-token,Authorization",
        "Access-Control-Expose-Headers" => 'auth-token'
    ];

    return $headers;
}
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


/**
 * 生成待签名字符串
 * 对数组里的每一个值从a到z的顺序排序，若遇到相同首字母，则看第二个字母以此类推。
 * 排序完成后，再把所有数组值以‘&’字符连接起来
 * @param  array $params 待签名参数
 * @return string
 */
function build_link_string($params)
{
    //sign和空值不参与签名
    $paramsFilter = array();
    while (list($key, $val) = each($params)) {
        if ($key == 'sign' || $val === '') {
            continue;
        } else {
            if (is_array($params[$key])) {
                $paramsFilter[$key] = json_encode($params[$key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $paramsFilter[$key] = $params[$key];
            }
        }
    }

    //对待签名参数数组排序a-z
    ksort($paramsFilter);
    reset($paramsFilter);

    //生成签名结果
    //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    $query = '';
    while (list($key, $val) = each($paramsFilter)) {
        $query .= $key . '=' . $val . '&';
    }
    //去掉最后一个&字符
    $query = substr($query, 0, count($query) - 2);

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $query = stripslashes($query);
    }

    return $query;
}
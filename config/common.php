<?php

return [

    //定义某些控制器或方法不走安全验证
    'security_allow' => [
        //定义某个控制器不走auth行为
        'controller' => ['General'],

        //控制器里的某个方法不走auth行为，规范controller/action
        'action' => [''],

        //定义某个模块不走安全验证
        'module' => ['index', 'index'],
    ],

    //定义后台某些控制器或方法不走安全验证
    'admin_auth_allow' => [
        //定义某个控制器不走auth行为
        'controller' => ['General'],

        //控制器里的某个方法不走auth行为，规范controller/action
        'action' => [
            'Member/memberLogin',
            'Member/memberLogout',
            'Member/getMenuList',
            'Menu/getAllMenuList',
            'Test/index',
            'Member/twoAuth',
        ],
    ],


    //存储token创建时间的缓存key
    'token_cache_key' => 'admin_token:',

    //Token签名key
    'token_sign_key'  => 'EB99258A-8112664A-F0889B08-2B42747F',

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '7w5fSD*Nz_9sg/#OX]oJ^', //默认数据加密KEY

    //Token有效时间,单位秒
    'token_expires'   => 21600,



];
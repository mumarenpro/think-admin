<?php


return [

    EC_SUCCESS => '操作成功',
    EC_FAILURE => '操作失败',

    //公共模块
    EC_SIGN_ERROR              => '签名错误',
    EC_API_TIMEOUT             => '当前请求已过期失效，请您校准设备时间',
    EC_NONCE_USED              => 'API重复请求',
    EC_PARAMS_ILLEGAL          => '请求参数不合法',
    EC_EVENTS_TYPE_EMPTY       => '赛事类型获取失败',
    EC_SPORT_INFO_EMPTY        => '体育项目获取失败',
    EC_DATABASE_ERROR          => '数据库操作错误',
    EC_IDENTITY_STATUS_ERROR   => '身份不合法',
    COLLECT_SYSTEM_MAINTENANCE => '抱歉！系统正在维护，暂停下注！请联系客服。',
    EC_ADVERTISING_NAME_EXISTS => '广告位名称不能重复',
    EC_ADVERTISING_ADD_FAIL    => '广告信息添加失败',
    EC_DETAIL_NO_EXISTS        => '详情不存在',
    EC_DATE_RANGE_ILLEGAL      => '超出查询时间范围限制',
];
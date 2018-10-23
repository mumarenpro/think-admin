<?php

namespace app\admin\validate;

use think\Validate;

class Index extends Validate
{

    /**
     * 规则定义
     * @var array
     */
    protected $rule = [
        'id' => 'require',
    ];

    /**
     * 提示信息定义
     * @var array
     */
    protected $message = [
        'id.require' => 'id不能为空',
    ];

    /**
     * 场景定义
     * @var array
     */
    public $scene = [
        'test' => ['id'],

    ];
}

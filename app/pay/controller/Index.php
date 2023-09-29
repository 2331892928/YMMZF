<?php
declare (strict_types = 1);

namespace app\pay\controller;

use think\facade\Request;
use think\facade\Validate;
use think\facade\View;

class Index
{
    public function index()
    {
        $data = Request::get();
        // 参数验证
        $validate = Validate::rule([
            "type"=>'require|alpha',
            "trade_no"=>'require|alphaNum',
        ]);
        if (!$validate->check($data)) {
            return View::fetch("error",['msg'=>"当前url不规范"]);
        }

        return '您好！这是一个[pay]示例应用';
    }
}

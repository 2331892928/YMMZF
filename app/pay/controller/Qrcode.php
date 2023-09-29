<?php
declare (strict_types = 1);

namespace app\pay\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;
use Utils;

class Qrcode
{
    protected Utils $Utils;
    public function __construct(){
        $this->Utils = new Utils;
    }
    public function index()
    {
        $data = Request::param();
        // 参数验证
        $validate = Validate::rule([
            "trade_no"=>'require|alphaNum',
        ]);
        if (!$validate->check($data)) {
            return View::fetch("submit@index:error",['msg'=>"URL参数不符合规范"]);
        }
        $order = $this->Utils->getOrder(['trade_no'=>$data['trade_no']]);
        $now_time = time();
        // 是否存在
        if ($order == null){
            return View::fetch("submit@index:error",['msg'=>"当前订单已过期"]);
        }
        // 是否过期

        if ($order['status'] == '2'){
            return View::fetch("submit@index:error",['msg'=>"当前订单已过期"]);
        }
        if ($order['endtime']<=$now_time){
            if ($order['status'] != '3'){
                Db::name("order")->where(['id'=>$order['id']])->update(['status'=>'3']);
            }
            return View::fetch("submit@index:error",['msg'=>"当前订单已过期"]);
        }
        // 没过期，取出通道二维码
        $pay_heartbeat = $this->Utils->getConfig("pay_heartbeat");
        $channel = $this->Utils->getChannel([
            'id'=>$order['tdid'],
            'status'=>'0',
            ['lasttime','>',$now_time-$pay_heartbeat]
        ]);
        if ($channel==null || count($channel)==0){
            // 发送商户
            return View::fetch("error",['msg'=>"当前商户通道已掉线"]);
        }
        $channel = $channel[0];
        $qrcode = $channel['qrcode'];// 二维码数据
        $ent_time = $order['endtime'] - $now_time;
        return View::fetch("index:wx_qrcodev2",[
            'order'=>$order,
            'code_url'=>$qrcode,
            'end_time'=>$ent_time
        ]);
    }
}

<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;
use Utils;

class Index
{
    protected Utils $Utils;
    public function __construct(){
        $this->Utils = new Utils;
    }
    public function index()
    {
        print_r($this->Utils->getClientIp());
        print_r("</br>");
        print_r($this->Utils->getClientIp("HTTP_CLIENT_ip"));
        print_r("</br>");
        print_r($this->Utils->getClientIp("REMOTE_ADDR"));
        print_r("</br>");
        return '您好！这是一个[api]示例应用';
    }
    public function getshop(){
        $data = Request::param();
        // 参数验证
        $validate = Validate::rule([
            "trade_no"=>'require|alphaNum',
        ]);
        if (!$validate->check($data)) {
            return json(['code'=>400,'msg'=>'url参数不合法']);
        }
        // 获取数据库数据
        $order = $this->Utils->getOrder(['trade_no'=>$data['trade_no']]);
        $now_time = time();
        // 是否存在
        if ($order == null){
            return json(['code'=>201,'msg'=>'当前订单已过期','url'=>null]);
        }
        // 是否过期

//        if ($order['status'] == '3'){
//            return json(['code'=>201,'msg'=>'当前订单已过期','url'=>$order['return_url']]);
//        }
        if ($order['endtime']<=$now_time){
            if ($order['status'] != '3'){
                Db::name("order")->where(['id'=>$order['id']])->update(['status'=>'3']);
            }
            return json(['code'=>201,'msg'=>'当前订单已过期','url'=>$order['return_url']]);
        }
        // 支付成功
        if ($order['status'] == '2'){
            // 没过期，取出通道二维码
            $pay_heartbeat = $this->Utils->getConfig("pay_heartbeat");
            $channel = $this->Utils->getChannel([
                'id'=>$order['tdid'],
                'status'=>'0',
                ['lasttime','>',$now_time-$pay_heartbeat]
            ]);
            if ($channel==null || count($channel)==0){
                // 发送商户
                return json(['code'=>202,'msg'=>'当前商户通道已掉线，不可支付','url'=>$order['return_url']]);
//                return View::fetch("error",['msg'=>"当前商户通道已掉线"]);
            }
            return json(['code'=>200,'msg'=>'支付成功','url'=>$order['return_url']]);
        } else {
            return json(['code'=>100,'msg'=>'该订单未支付','url'=>$order['return_url']]);
        }



    }
}

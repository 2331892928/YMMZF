<?php
declare (strict_types = 1);

namespace app\submit\controller;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
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
        $data = Request::param();
//        ^ array:9 [▼
//  "pid" => "1000"
//  "type" => "wxpay"
//  "notify_url" => "http://ypay.mljf1.cn/pay/notify/2023092118153982155/"
//  "return_url" => "http://ypay.mljf1.cn/pay/return/2023092118153982155/"
//  "out_trade_no" => "2023092118153982155"
//  "name" => "支付测试"
//  "money" => "1"
//  "sign" => "199f47db4e797f73b176d15550e83a0a"
//  "sign_type" => "MD5"
//]
        // 参数验证
        $validate = Validate::rule([
            "pid"=>'require|number',
            "type"=>'require|alpha',
            "out_trade_no"=>'require|alphaNum',
            "notify_url"=>'require|url',
            "return_url"=>'require|url',
            "name"=>'require',
            "money"=>'require|float',
//            "param"=>'require',
            "sign"=>'require|alphaNum',
            "sign_type"=>'require|alphaNum',
        ]);
        if (!$validate->check($data)) {
            return View::fetch("error",['msg'=>$validate->getError()]);
        }
//        halt($data);
        if ($data['sign_type']!='MD5'){
            return View::fetch("error",['msg'=>"签名类型错误"]);
        }
        // 查询pid
        $user = $this->Utils->getUser($data['pid']);
        if (!$user){
            return View::fetch("error",['msg'=>"商户不存在"]);
        }
        // 先验证sign
        if (!$this->Utils->verifySign($data,$user['key'])){
            return View::fetch("error",['msg'=>"签名校验失败，请返回重试！"]);
        }
        if ($user['status'] == '1' || $user['pay']==1){
            return View::fetch("error",['msg'=>"商户已封禁，无法支付！"]);
        }
        $domain = $this->Utils->getdomain($data['notify_url']);
        if(strlen($data['name'])>127)$data['name']=mb_strcut($data['name'], 0, 127, 'utf-8');
        // 转换支付类型
        $pay_type = '0';
        switch ($data['type']){
            case 'wxpay':
                $pay_type = '0';
                break;
            case 'alipay':
                $pay_type = '1';
                break;
            case 'qqpay':
                $pay_type = '2';
                break;
            default:
                $pay_type = '3';
        }
        $now_time = time();
        $pay_heartbeat = $this->Utils->getConfig("pay_heartbeat");
        $order = $this->Utils->getorder([
            'out_trade_no'=>$data['out_trade_no'],
            'userid'=>$user['id'],
            'type'=>$pay_type,
            ['endtime','>',$now_time]
        ]);
//        ,[
//            ['status','=','0'],
//            ['status','=','1']
//        ]
        if ($order){
            // 已有此订单，查询是否支付失败或超时,在sql已做
//            if ($order['status'] != '3'){
//                Db::name("order")->where(['id'=>$order['id']])->update(['status'=>'3']);
//            }
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
//            $qrcode = app()->getRootPath() . 'public/storage'.$channel['qrcode'];
//            // 是否有此文件
//            if (!file_exists($qrcode)){
//                // 发送商户此通道二维码丢失
//                return View::fetch("error",['msg'=>"当前商户通道收款二维码丢失"]);
//            }
//            print_r($qrcode);
            // 展示二维码
            return View::fetch("jump",[
                'url'=>'../pay/qrcode/index/trade_no/'.$order['trade_no']
            ]);
        } else {
            // 订单不存在，创建

            // 在此用户的通道下，随机一个可用通道

            $channel = $this->Utils->getChannel([
                'userid'=>$user['id'],
                'type'=>$pay_type,
                'status'=>'0',
                ['lasttime','>',$now_time-$pay_heartbeat]
            ]);
            if ($channel==null || count($channel)==0){
                // 通知商户无通道
                return View::fetch("error",['msg'=>"当前商户无通道可用"]);
            }
            // 找出所有店员（站长）
            try {
                $dz = Db::name("channel")->field(['id'])->where([
                    'collection_type' => '4',
                    'type' => $pay_type,
                    'status' => '0',
                    ['lasttime', '>', $now_time - $pay_heartbeat]
                ])->select();
            } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
                return View::fetch("error",['msg'=>"当前网站错误"]);
            }
            $dz = $dz->toArray();
            // 随机一个通道
            $dz_flag = false;
            for ($i=0;$i<=count($channel)-1;$i++){
                $tdid = rand($i,count($channel)-1);
                $tdid = $channel[$tdid];

                // 如果是店员模式，获取站长店员数据
//                if ($tdid['collection_type']=='0'){
//                    $tdid = $this->Utils->getChannel([
//                        'status'=>"0",
//                        ['lasttime','>',$now_time-$pay_heartbeat],
//                        'id'=>$tdid['software_main_id']
//                    ],true);
//                    if ($tdid == null){
//                        // 通知商户配置错误，店员不存在
//                        return View::fetch("error",['msg'=>"当前商户无通道可用"]);
//                    }
//
//                }

                if ($tdid['collection_type']=='0') {
                    // 查询该通道的店员是否可用
                    if (in_array(['id' => $tdid['software_main_id']], $dz)) {
                        $dz_flag = true;
                        break;
                    }
                } else {
                    $dz_flag = true;
                    break;
                }
            }
            if (!$dz_flag){ // 店员(站长) 不在线
                return View::fetch("error",['msg'=>"当前商户无通道可用"]);
            }

            $tdid_id = $tdid['id'];
            $tdid_nickname = $tdid['nickname'];

            // 获取此金额是否被占用
            $money = $data['money'];

            // 设置并发 1000000
            for ($i = 0;$i<=1000000;$i++){
                $order = $this->Utils->getorder([
                    'money'=>$money,
                    'userid'=>$user['id'],
                    'type'=>$pay_type,
                    ['endtime','>',$now_time]
                ],[
                    ['status','=','0'],
                    ['status','=','1']
                ]);
                if ($order){
                    $money = bcadd($money,"0.01",2);
                } else {
                    break;
                }
            }
            if ($i>=1000001){
                return View::fetch("error",['msg'=>"生成订单失败，请返回重新生成"]);
            }
            $trade_no = $this->Utils->create_trade_no("");
            $data_parma = [];
            $data_parma['out_trade_no'] = $data['out_trade_no'];
            $data_parma['trade_no'] = $trade_no;
            $data_parma['trade_status'] = "TRADE_SUCCESS";
            $data_parma['money'] = $data['money'];
//            $data_parma['trade_status'] = "TRADE_SUCCESS";
            $data_sign = $this->Utils->makeSign($data_parma,$user['key']);
            $data_parma['sign'] = $data_sign;
            $data_parma['sign_type'] = "MD5";
            $insert_data = [
                'tdid'=>$tdid_id,
                'userid'=>$user['id'],
                'type'=>$pay_type,
                'realmoney'=>$data['money'],
                'money'=>$money,
                'status'=>'0',
                'notify_url'=>$data['notify_url'].'?'.http_build_query($data_parma),
                'return_url'=>$data['return_url'].'?'.http_build_query($data_parma),
                'param'=>array_key_exists('param',$data) ? $data['param'] : null,
                'addtime'=>$now_time,
                'addtime_time'=>date('Y-m-d H:i:s',$now_time),
                'endtime'=>$now_time+$user['pay_time'],
                'endtime_time'=>date('Y-m-d H:i:s',$now_time+$user['pay_time']),
                'domain'=>$domain,
                'ip'=>$this->Utils->getClientIp(),
                'trade_no'=>$trade_no,
                'out_trade_no'=>$data['out_trade_no'],
                'robotid'=>$tdid['software_id'],
                'dz_nickname'=>$tdid_nickname,
                'name'=>$data['name']
            ];
//            halt($insert_data);
            // 放入order
            $id = Db::name("order")->insert($insert_data,true);
            if ($id == 0){
                return View::fetch("error",['msg'=>"生成订单失败，请返回重新生成"]);
            }
            // 展示二维码
            return View::fetch("jump",[
                'url'=>'../pay/qrcode/index/trade_no/'.$trade_no
            ]);

        }
    }
}

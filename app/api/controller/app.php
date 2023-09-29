<?php
declare (strict_types = 1);

namespace app\api\controller;

use Aes;
use Rsa;
use think\facade\Request;
use think\facade\Validate;
use Utils;

class App
{
    protected Utils $Utils;
    protected Aes $Aes;
    protected Rsa $Rsa;
    private array $post_data = [];
    public function __construct(){

        $this->Utils = new Utils();
        $key = $this->Utils->getConfig("app_aes_key");
        $iv = $this->Utils->getConfig("app_aes_iv");
        $pt = $this->Utils->getConfig("app_private_key") == null ? "" : $this->Utils->getConfig("app_private_key");
        $pb = $this->Utils->getConfig("app_public_key") == null ? "" : $this->Utils->getConfig("app_public_key");
        try{
            $this->Aes = new Aes($key,$iv);

            $this->Rsa = new Rsa($pt,$pb);
        }catch (\Exception $e){
            json(['code'=>401,'msg'=>'密钥未初始化'])->send();
        }



        // 控制器初始化
        $this->initialize();
    }
    protected function initialize(){
        // 进行统一解密
        // 把post参数进行解密
        $post_data = Request::post();
//        print_r($post_data);
        // 判断该url是否应该有data
        if (!array_key_exists('data',$post_data)){
            $this->post_data = [];
        } else {
            $data = $post_data['data'];
            // 服务端私钥加密，客户端公钥解密
            // 客户端共钥加密，服务端私钥解密
            //
            try{
                $data = str_replace(" ","+",$data);
                $data = $this->Rsa->privDecrypt($data);
                if ($data == null){
                    json(['code'=>400,'msg'=>'解密失败1'])->send();
                }
                $data = $this->Aes->decrypt($data);
                if ($data == null){
                    json(['code'=>400,'msg'=>'解密失败'])->send();
                }

                $this->post_data = json_decode($data,true);
            }catch (\TypeError $e){
                // $this->post_data强制为array，解密后不是array都是失败
                json(['code'=>400,'msg'=>'解密失败或参数格式错误'])->send();
            }
        }



    }
    public function index()
    {
        return '您好！这是一个[api.app]示例应用';
    }
    public function kpwx(){
        // 参数验证
        $validate = Validate::rule([
            "robotid"=>'require|alphaDash',
            "tdid"=>'require|number',
            "userid"=>'require|number',
            "status"=>'require|number|max:1',
            "transid"=>'require|alphaDash',
            "username"=>'require|alphaDash',
            "displayname"=>'require',
            "timestamp"=>'number|max:10',
            "voice_content"=>'float',
            "outtradeno"=>'alphaDash',
        ]);
        $data = $this->post_data;
        if (!$validate->check($data)) {
            return json(['code'=>201,"msg"=>$validate->getError()]);
        }
        if ($data['status'] != 0 && $data['status'] != 1 && $data['status'] != 2){
            return json(['code'=>201,"msg"=>"状态参数异常"]);
        }
        $flag = false;
//        if ($data['status'] == 0){
//            $flag = $this->Utils->kpwx_order_add($data['robotid'],$data['userid'],$data['tdid'],$data['status'],$data['transid'],$data['username'],$data['displayname'],$data['timestamp']);
//
//        } else {
//            $flag = $this->Utils->kpwx_order_edit($data['robotid'],$data['userid'],$data['tdid'],$data['status'],$data['transid'],$data['username'],$data['displayname'],$data['timestamp'],$data['voice_content'],$data['outtradeno']);
//
//        }
        $flag = $this->Utils->kpwx_order_edit($data['robotid'],$data['userid'],$data['tdid'],$data['status'],$data['transid'],$data['username'],$data['displayname'],$data['timestamp'],$data['voice_content'],$data['outtradeno']);

        if ($flag){
            return json(['code'=>200]);
        } else {
            return json(['code'=>402]);
        }
    }

    public function kpwx_dianyuan(){
        // 参数验证
        $validate = Validate::rule([
            "robotid"=>'require|alphaDash',
            "timestamp"=>'require|number|max:10',
            "voice_content"=>'require|float',
            "nickname"=>'require',
            "pid"=>'require|number',
            "key"=>'require|alphaDash',
        ]);
        $data = $this->post_data;
        if (!$validate->check($data)) {
            return json(['code'=>201,"msg"=>$validate->getError()]);
        }
//        if ($data['status'] != 0 && $data['status'] != 1 && $data['status'] != 2){
//            return json(['code'=>201,"msg"=>"状态参数异常"]);
//        }
        $flag = $this->Utils->kpwx_dianyuan_order_edit($data['nickname'],$data['robotid'],$data['timestamp'],$data['voice_content']);
        if ($flag['code']){
            return json(['code'=>200,"msg"=>"success"]);
        } else {
            return json(['code'=>400,"msg"=>$flag['msg']."该次信息：".json_encode($data)]);
        }
    }

    public function heartbeat(){
        // 参数验证
        $validate = Validate::rule([
            "robotid"=>'require|alphaDash',
            "timestamp"=>'require|number|max:10',
            "pid"=>'require|number',
            "nickname"=>'require',
            "avatar_address"=>'require',
            "key"=>'require|alphaDash',
        ]);
        $data = $this->post_data;
        if (!$validate->check($data)) {
            return json(['code'=>201,"msg"=>$validate->getError()]);
        }

        // 检查是否正确
        if ($data['pid']==0){//店员模式
            $key = $this->Utils->getConfig("key");

        } else {//其他
            $userinfo = $this->Utils->getUser($data['pid']);
            $key = $userinfo['key'];
        }
        if ($data['key']!=$key){
            return json(['code'=>201,'msg'=>"无此用户或密钥错误"]);
        }
        // 记录心跳

        $flag = $this->Utils->channel_heartbeat($data['pid'],$data['robotid'],$data['nickname'],$data['avatar_address'],4);
        if ($flag){
            return json(['code'=>200,'msg'=>"success",'data'=>$this->Utils->e(['code'=>200])]);
        } else {
            return json(['code'=>201,'msg'=>"无此通道"]);
        }


    }
}

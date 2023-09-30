<?php
declare (strict_types = 1);

namespace app\user\controller;

use Channel;
use Endroid\QrCode\QrCode;
use PHPZxing\PHPZxingDecoder;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Validate;
use think\facade\View;
use UserUtils;
use Utils;

class Ajax
{
    protected Utils $Utils;
    protected Channel $Channel;
    protected UserUtils $UserUtils;
    protected $UserInfo;

    public function __construct(){
        $this->Utils = new Utils();
        $this->Channel = new Channel();
        $this->UserUtils = new UserUtils();
        $whitelist = ['add_channel','login','logout'];
        $whitelist2 = ['login','logout'];
        $action = Request::action();

        // 是否登录
        $this->UserInfo = $this->UserUtils->isLogin();
        if ($this->UserInfo === false){
            if (!in_array($action,$whitelist2)){
                json(['code'=>405,'msg'=>"未登录"])->send();
            }
        }


        if (!in_array($action,$whitelist)){
            $check = Request::checkToken('__token__');
            // 参数验证
            $validate_type = Validate::rule([
                "type"=>'require|alphaDash',
            ]);
            $data = Request::post();
            if ($validate_type->check($data)) {
                // 存在
                if ($data['type'] != 'image_update'){
                    if(false === $check) {
                        throw new ValidateException('异常请求，请刷新重试');
                    }
                }
            } else {
                if(false === $check) {
                    throw new ValidateException('异常请求，请刷新重试');
                }
            }

        }

    }
    public function login(){
        $validate = Validate::rule([
            "username"=>'require|alphaDash',
            "password"=>'require|alphaDash',
            "lot_number"=>'require|alphaDash',
            "pass_token"=>'require|alphaDash',
            "gen_time"=>'require|number',
            "captcha_output"=>'require',
        ]);
        $data = Request::post();
        if (!$validate->check($data)) {
//            "用户名或密码只能是：字母和数字，下划线_及减号-"
            return json(['code'=>204,'msg'=>$validate->getError()]);
        }
        $flag = $this->UserUtils->validate($data['lot_number'],$data['captcha_output'],$data['pass_token'],$data['gen_time']);
        if (!$flag['code']){
            return json(['code'=>202,'msg'=>$flag['msg']]);
        }
        $flag = $this->UserUtils->Login($data['username'],$data['password']);
        if (!$flag){
            return json(['code'=>201,'msg'=>"登录失败，用户名或密码错误"]);
        }
        return json(['code'=>200,'msg'=>"success"]);

    }
    public function logout(){

    }
    public function add_channel(){
        // 参数验证
        $validate_type = Validate::rule([
            "type"=>'require|alphaDash',
        ]);
        $data = Request::post();
        if (!$validate_type->check($data)) {
            return json(['code'=>204,'msg'=>$validate_type->getError()]);
        }
        switch ($data['type']){
            case "wx":
                // 参数验证
                $validate = Validate::rule([
                    "nickname"=>'require',
                    "collection_type"=>'require|number'
                ]);
                // 店员
                $validate_dy = Validate::rule([
                    "dzid"=>'require|number'
                ]);
                $data = Request::post();
                if (!$validate->check($data)) {
                    return json(['code'=>204,'msg'=>$validate->getError()]);
                }
                $collection_type = $data['collection_type'];

                if ($collection_type!=1 && $collection_type!=2 && $collection_type!=3 && $collection_type!=0){
                    return json(['code'=>204,'msg'=>"通道类型错误"]);
                }
                $qrcode = null;
                $dzid = 0;
                switch ($collection_type){ // 店员模式
                    case "0":
                        if (!$validate_dy->check($data)) {
                            return json(['code'=>204,'msg'=>"微信店员模式需要提供店员id"]);
                        }
                        $dzid = $data['dzid'];
                        // 店员是否存在
                        try {
                            $dzinfo = Db::name("channel")->where([
                                'id' => $dzid,
                                'collection_type' => 4
                            ])->find();
                        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
                            return json(['code'=>201,'msg'=>"数据库错误"]);
                        }
                        if ($dzinfo==null){
                            return json(['code'=>201,'msg'=>"此店员不存在"]);
                        }
                        $pay_heartbeat = $this->Utils->getConfig("pay_heartbeat");
                        if ($dzinfo['lasttime']<=time()-$pay_heartbeat){
                            return json(['code'=>201,'msg'=>"此店员不在线"]);
                        }
                        // 判断此昵称是否存在
                        try {
                            $channel_info = Db::name("channel")->where([
                                'type'=>'0',
                                ['collection_type','<>','1'],
                                ['collection_type','<>','4'],
                                'nickname' => $data['nickname']
                            ])->find();
                        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
                            throw new ValidateException('未知异常，请刷新重试');
                        }
                        if ($channel_info!=null){
                            return json(['code'=>201,'msg'=>"此昵称/店铺名称已存在，请更改后再次提交"]);
                        }

                        $qrcode = Session::pull('image_url');
                        if ($qrcode==null){
                            return json(['code'=>204,'msg'=>"还没有上传或收款二维码图片失效，请重新上传!"]);
                        }
                        // 判断二维码是否微信
                        if (stripos($qrcode,"wxp://")!==0){
                            return json(['code'=>201,'msg'=>"这不是有效的微信收款二维码，请重新上传!"]);
                        }
                        break;
                    case "1":
                    case "2":
                    case "3":
                        return json(['code'=>201,'msg'=>"暂未支持"]);
                    default:
                        return json(['code'=>204,'msg'=>"通道类型错误"]);
                }
                $flag = $this->Channel->add_channel($this->UserInfo['id'],$data['nickname'],$dzid,$qrcode,0,$collection_type);
                if (!$flag['code']){
                    return json(['code'=>201,'msg'=>$flag['msg']]);
                }
                return json(['code'=>200,'msg'=>"success"]);

                break;
            case "shop_assistant_qr_code":
                $validate = Validate::rule([
                    "id"=>'require|number'
                ]);
                $data = Request::post();
                if (!$validate->check($data)) {
                    return json(['code'=>204,'msg'=>"无此通道id"]);
                }
                $channel_info = $this->Utils->getChannel([
                    'userid'=>$this->UserInfo['id'],
                    'collection_type'=>'0',
                    'type'=>'0',
                    'id'=>$data['id']
                ],true);
                if ($channel_info==null){
                    return json(['code'=>201,'msg'=>"无此通道id"]);
                }
                $channel_info = $this->Utils->getChannel([
                    'collection_type'=>'4',
                    'type'=>'0',
                    'id'=>$channel_info['software_main_id']
                ],true);
                if ($channel_info==null){
                    return json(['code'=>201,'msg'=>"无此店员，请重新绑定"]);
                }
                return json(['code'=>200,'msg'=>"success",'data'=>['qrcode'=>$channel_info['qrcode'],'nickname'=>$channel_info['nickname']]]);
            case "image_update":
                // 获取表单上传文件
                Session::delete('image_url');

                $files = request()->file();
                if ($files==null){
                    return json(['code'=>204,'msg'=>"上传的文件格式不正确"]);
                }
                try {
                    validate(['image'=>'fileSize:4194304|fileExt:jpg,png,jpeg,bmp'])
                        ->check($files);
                    $savename = [];
                    foreach($files as $file) {
                        $savename[] = \think\facade\Filesystem::putFile( 'topic', $file);
                    }
                } catch (\think\exception\ValidateException $e) {
                    return json(['code'=>204,'msg'=>$e->getError()]);
                }
                if (count($savename)>1){
                    return json(['code'=>204,'msg'=>"上传的文件格式不正确"]);
                }
                $ROOT_PATH = app()->getRootPath();
                ini_set('memory_limit','1024M');
                $savename = $ROOT_PATH . 'runtime/storage/' . $savename[0];
                $config = array(
                    'try_harder' => true,
                );
                $decoder = new PHPZxingDecoder($config);
//                $decoder->setJavaPath('java');

                try{
                    $decodedData = $decoder->decode($savename);
                }catch (\Exception $e){
                    // 默认java位置无java，转变yumjava
                    $decoder->setJavaPath('/usr/lib/jvm/jre-1.8.0');
                    try {
                        $decodedData = $decoder->decode($savename);
                    } catch (\Exception $e) {
                        return json(['code'=>900,'msg'=>"网站未配置正确，请咨询站长进行配置，当前错误代码：900".$e->getMessage()]);
                    }
//                    return json(['code'=>900,'msg'=>"网站未配置正确，请咨询站长进行配置，当前错误代码：900".$e->getMessage()]);
                }
                // 获取二维码内容
                if(!$decodedData->isFound()) {
                    return json(['code'=>201,'msg'=>"这不是二维码图片或改图片存在多个二维码"]);
                }
                if (strcmp($decodedData->getFormat()," QR_CODE")!=0 || strcmp($decodedData->getType()," URI")!=0){
                    return json(['code'=>203,'msg'=>"这不是有效的收款二维码"]);
                }
                $text = $decodedData->getImageValue();
                //放入session
                Session::flash('image_url', $text);
                return json(['code'=>200,'msg'=>"success"]);
            default:
                return json(['code'=>404,'msg'=>"请求异常!"]);
        }
    }

    public function edit_channel(){
        // 参数验证
        $validate = Validate::rule([
            "id"=>'require|number',
            "type"=>'require|alpha',
        ]);
        $data = Request::post();
        if (!$validate->check($data)) {
            return json(['code'=>204,'msg'=>"参数异常"]);
        }
        switch ($data['type']) {
            case "status":
                $flag = $this->Channel->change_status($data['id'],$this->UserInfo['id']);
                if (!$flag['code']){
                    return json(['code'=>201,'msg'=>$flag['msg']]);
                }
                return json(['code'=>200,'msg'=>"success"]);
                break;
            default:
                return json(['code'=>404,'msg'=>"请求异常"]);
        }
    }
    public function user_random_key(){
        $this->UserUtils->user_random_key($this->UserInfo['id']);
        return json(['code'=>200,'msg'=>"success"]);
    }
}
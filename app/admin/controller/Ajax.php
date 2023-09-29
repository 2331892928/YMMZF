<?php
declare (strict_types = 1);

namespace app\admin\controller;

use AdminUtils;
use Channel;
use PHPZxing\PHPZxingDecoder;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Validate;
use think\facade\View;
use Utils;

class Ajax
{
    protected Utils $Utils;
    protected Channel $Channel;
    protected AdminUtils $AdminUtils;
    protected $UserInfo;
    public function __construct(){
        $this->Channel = new Channel();
        $this->AdminUtils = new AdminUtils();
        $whitelist = ['image_upload','login','logout'];
        $whitelist2 = ['login','logout'];
        $action = Request::action();

        // 是否登录
        $this->UserInfo = $this->AdminUtils->isLogin();
        if ($this->UserInfo === false){
            if (!in_array($action,$whitelist2)){
                json(['code'=>405,'msg'=>"未登录"])->send();
            }
        }


        if (!in_array($action,$whitelist)){
            $check = Request::checkToken('__token__');

            if(false === $check) {
                throw new ValidateException('异常请求，请刷新重试');
            }
        }
    }
    public function image_upload(){
        // 获取表单上传文件
        Session::delete('admin_image_url');

        $files = request()->file();
        if ($files==null){
            return ['code'=>204,'msg'=>"上传的文件格式不正确"];
        }
        try {
            validate(['image'=>'fileSize:4194304|fileExt:jpg,png,jpeg,bmp'])
                ->check($files);
            $savename = [];
            foreach($files as $file) {
                $savename[] = \think\facade\Filesystem::putFile( 'topic', $file);
            }
        } catch (\think\exception\ValidateException $e) {
            return ['code'=>204,'msg'=>$e->getError()];
        }
        if (count($savename)>1){
            return ['code'=>204,'msg'=>"上传的文件格式不正确"];
        }
        $ROOT_PATH = app()->getRootPath();
        ini_set('memory_limit','1024M');
        $savename = $ROOT_PATH . 'runtime/storage/' . $savename[0];
        $config = array(
            'try_harder' => true,
        );
        $decoder = new PHPZxingDecoder($config);
//                $decoder->setJavaPath();

        try{
            $decodedData = $decoder->decode($savename);
        }catch (\Exception $e){
            return ['code'=>900,'msg'=>"网站未配置正确，请咨询站长进行配置，当前错误代码：900"];
        }
        // 获取二维码内容
        if(!$decodedData->isFound()) {
            return ['code'=>201,'msg'=>"这不是二维码图片或改图片存在多个二维码"];
        }
        if (strcmp($decodedData->getFormat()," QR_CODE")!=0 || strcmp($decodedData->getType()," URI")!=0){
            return ['code'=>203,'msg'=>"这不是有效的二维码"];
        }
        $text = $decodedData->getImageValue();
        //放入session
        Session::flash('admin_image_url', $text);
        return ['code'=>200,'msg'=>"success"];
    }

    /** 店员
     * @return \think\response\Json
     */
    public function add_channel_wx(){
        // 参数验证
        $validate = Validate::rule([
            "wxid"=>'require|alphaDash',
        ]);
        $data = Request::post();
        if (!$validate->check($data)) {
            return json(['code'=>204,'msg'=>$validate->getError()]);
        }
        // 店员加好友二维码
        $qrcode = Session::pull('admin_image_url');
        if ($qrcode==null){
            return json(['code'=>204,'msg'=>"还没有上传或二维码图片失效，请重新上传!"]);
        }
        // 是否是加好友的
        if (stripos($qrcode,"https://u.wechat.com/")!==0){
            return json(['code'=>201,'msg'=>"这不是有效的加好友二维码，请重新上传!"]);
        }
        $id = Db::name("channel")->insert([
            'software_id' => $data['wxid'],
            'userid' => 0,
            'status' => '0',
            'addtime' => time(),
            'collection_type' => '4',
            'qrcode' => $qrcode,
            'type' => '0'
        ],true);
        if ($id == 0){
            return json(['code'=>201,'msg'=>'添加失败']);
        }
        return json(['code'=>200,'msg'=>'success']);
    }
    public function edit_channel_wx(){
        // 参数验证
        $validate_type = Validate::rule([
            "type"=>'require|alphaDash',
            "id"=>'require|number',
        ]);
        $data = Request::post();
        if (!$validate_type->check($data)) {
            return json(['code'=>204,'msg'=>$validate_type->getError()]);
        }
        switch ($data['type']){
            case "status":
                $flag = $this->Channel->change_status($data['id']);
                if (!$flag['code']){
                    return json(['code'=>201,'msg'=>$flag['msg']]);
                }
                return json(['code'=>200,'msg'=>'success']);
                break;
            case "delete":
                $flag = $this->Channel->delete($data['id']);
                if (!$flag['code']){
                    return json(['code'=>201,'msg'=>$flag['msg']]);
                }
                return json(['code'=>200,'msg'=>'success']);
                break;
            default:
                return json(['code'=>404,'msg'=>'非法请求']);
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
        $flag = $this->AdminUtils->validate($data['lot_number'],$data['captcha_output'],$data['pass_token'],$data['gen_time']);
        if (!$flag['code']){
            return json(['code'=>202,'msg'=>$flag['msg']]);
        }
        $flag = $this->AdminUtils->Login($data['username'],$data['password']);
        if (!$flag){
            return json(['code'=>201,'msg'=>"登录失败，用户名或密码错误"]);
        }
        return json(['code'=>200,'msg'=>"success"]);
    }
}
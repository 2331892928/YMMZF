<?php
// 这是系统自动生成的公共文件
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Request;

class UserUtils{
    protected Geetest $Geetest;
    protected Utils $Utils;
    public function __construct(){
        $this->Utils = new Utils();
    }

    /**
     * 判断是否登录，已登陆返回用户信息
     * @return array|false|mixed|Db|\think\Model
     */
    public function isLogin(){
        // 获取cookie中的值，并判断此cookie和此ip是否是上一次登录的对应的，否则强制退出
        $userToken = Cookie::get('usertoken');
        $userIp = Request::ip();
        try {
            $userinfo = Db::name("user")->where([
                'ordinary_token' => $userToken,
            ])->find();
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return false;
        }
        if (!$userinfo){
            return false;
        }
//        if ($userinfo['ip']!=$userIp){
//            // 强制退出
//            $this->logout(true);
//            return false;
//        }
        return $userinfo;

    }

    /**
     * 登录，成功返回用户信息，否则返回假
     * @param $username
     * @param $password
     * @return array|false|mixed|Db|\think\Model
     */
    public function Login($username,$password){
        try {
            $userinfo = Db::name("user")->where([
                'account' => $username,
                'pwd' => md5($username.$password.'ymwlgzs-ymmzf-amen'),
            ])->find();
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return false;
        }
//        print_r(md5($username.$password.'ymwlgzs-ymmzf-amen'));
//        print_r($username.$password.'ymwlgzs-ymmzf-amen');
//        print_r($userinfo);
//        die();
        if (!$userinfo){
            return false;
        }
        $userToken = md5($userinfo['account'].$userinfo['pwd'].time().'ymwl');
        cookie::set('usertoken', $userToken, 86400);
        try {
            $userinfo = Db::name("user")->where([
                'id' => $userinfo['id']
            ])->update([
                'ordinary_token' => $userToken,
                'ip' => Request::ip(),
                'lasttime' => time()
            ]);
        } catch (\think\db\exception\DbException $e) {
//            print_r($e->getMessage());
            return false;
        }
        return $userinfo;
    }

    /**
     * 强制退出
     * @return void
     */
    public function logout($qz = false){
        $userToken = Cookie::get('usertoken');
        $userIp = Request::ip();
        cookie('usertoken', null);
//        当前ip与上一次登录ip不一样，禁止强制退出，使用普通退出
        try {
            $userinfo = Db::name("user")->where([
                'ordinary_token' => $userToken,
            ])->find();
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            $userinfo = false;
        }
        if ($userinfo!=null && $userinfo['ip']==$userIp){
            try {
                Db::name("user")->where([
                    'ordinary_token' => $userToken,
                ])->update([
                    'ordinary_token' => "",
                ]);
            } catch (\think\db\exception\DbException $e) {
            }
        }
        if ($qz){
            try {
                Db::name("user")->where([
                    'ordinary_token' => $userToken,
                ])->update([
                    'ordinary_token' => "",
                ]);
            } catch (\think\db\exception\DbException $e) {
            }
        }
    }

    public function validate($lot_number,$captcha_output,$pass_token,$gen_time){ //验证码
        return $this->Utils->validate($lot_number,$captcha_output,$pass_token,$gen_time);
    }
    public function userid2user(){

    }
    public function user_random_key($userid){
        try {
            Db::name("user")->where([
                'id' => $userid
            ])->update([
                'key' => md5($this->Utils->generateRandomString())
            ]);
        } catch (\think\db\exception\DbException $e) {
            return false;
        }
        return true;
    }
}
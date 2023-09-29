<?php
declare (strict_types = 1);

namespace app\admin\controller;

use AdminUtils;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;
use Utils;

class Index
{
    protected Utils $Utils;
    protected AdminUtils $AdminUtils;
    protected $UserInfo;
    public function __construct(){
        $this->Utils = new Utils();
        $this->AdminUtils = new AdminUtils();
        $page_config = [
            'admin' => Request::root() .'/index/'
        ];
        View::assign([
            'page_config' => $page_config
        ]);
        $page_menu = View::fetch("index/other/menu",['page_config' => $page_config]);
        $page_modal = View::fetch("index/other/modal");
        // 获取当前方法
        $action = Request::action(true);
        // 获取方法前缀：channel_list channel
        // 方法转活动子菜单(主菜单活动，子菜单活动，主菜单图标更改)
        if (!(strrpos($action,"_")===false)){
            $action_prefix = substr($action,0,strrpos($action,"_"));
            $page_menu = str_replace('submenu_prefix="'.$action_prefix.'"','style="display: block;"',$page_menu);
            $page_menu = str_replace('span_ico_'.$action_prefix.'_sidebar-nav-sub-ico-rotate','sidebar-nav-sub-ico-rotate',$page_menu);
            $page_menu = str_replace('menu_main_a_'.$action_prefix,'active',$page_menu);

        }
        // 方法转活动菜单
        $page_menu = str_replace('class="'.$action.'"','class="sub-active"',$page_menu);
        $this->UserInfo = $this->AdminUtils->isLogin();
        $geetest_id = $this->Utils->getConfig("geetest_id");
        $geetest_key = $this->Utils->getConfig("geetest_key");
        View::assign([
            'page_menu' => $page_menu,
            'page_modal' => $page_modal,
            'variable_user' => $this->UserInfo,
            'variable_domain' => Request::domain(true),
            'variable_geetest_id' => $geetest_id,
            'variable_geetest_key' => $geetest_key,
//            'page_config' => $page_config
        ]);
        // 是否登录
        $whitelist = ['login'];

        if ($this->UserInfo == null){
            if (!in_array($action,$whitelist)){
                print_r("<script>window.location.href='".Request::root() ."/index/login"."'</script>");
            }
        }
    }
    public function index()
    {
        $url = Request::url();
        $root_url = Request::root();
        // 跳转统一首页，让静态文件正常加载
        if ($url==$root_url.'/index/index' or $url==$root_url.'/index/index/' or $url==$root_url.'/'){
            return redirect($root_url);
        }
        return View::fetch("index");
    }
    public function channel_list(){
        // 参数验证
        $validate = Validate::rule([
            "page"=>'require|number',
            'option'=>'number',
        ]);
        $data = Request::get();
        if (!$validate->check($data)) {
            $page = 1;
        } else {
            $page = $data['page'];
        }
        $option = 4;
        $field = '';
        if (array_key_exists('option',$data)){
            $option = $data['option'];
        }
        if (array_key_exists('field',$data)){
            $field = $data['field'];
        }
        $channel_array = $this->Utils->page_channel($page,(string)$option,$field);
        $pay_heartbeat = $this->Utils->getConfig("pay_heartbeat");
        $now_time = time();
        return View::fetch("channel_list",[
            'variable_channel_list' => $channel_array['list'],
            'variable_channel_page' => $channel_array['page'],
            'variable_channel_pay_heartbeat' => $pay_heartbeat,
            'variable_channel_now_time' => $now_time,
            'variable_channel_page_number' => $page
        ]);
    }
    public function login(){
        return View::fetch("login");
    }

}

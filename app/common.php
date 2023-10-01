<?php
// 应用公共文件
use think\facade\Db;
use app\common\QRcode;

class Utils{
    public  function getqrcode($url){
        $ROOT_PATH = app()->getRootPath();
        require_once $ROOT_PATH.'app/common/phpqrcode.php';
        $QRcode = new QRcode();
        ob_start();
        $QRcode->png($url, false, 'L', 4, 3);
        $qrcodedata = ob_get_contents();
        ob_end_clean();
        return $qrcodedata;
    }
    public function create_trade_no($prefix='ymmzf')
    {
        return $prefix . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
    }
    public function getConfig($key){
        try {
            $config = Db::name("config")->where('key', $key)->find();

        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return null;
        }
        if ($config != null){
            return $config['value'];
        }
        return null;
    }
    public function setConfig($key,$value){
        try {
            Db::name("config")->where('key', $key)->update(['value' => $value]);
        } catch (\think\db\exception\DbException $e) {
            return false;
        }
        return true;
    }

    public function getUser($id){
        try {
            $user = Db::name("user")->where('id', $id)->find();

        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return null;
        }
        return $user;
    }

    /**
     * 必须拥有：tdid userid type money  realmoney status addtime endtime addtime_time endtime_time
     * @param $data
     * @return bool
     */
    public function addOrder($data){
        $id = Db::name("order")->insert($data,true);
        if ($id!=0){
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $where_data array and 条件
     * @param $where_data_or array or 条件
     * @return array|mixed|Db|\think\Model|null
     */
    public function getOrder($where_data,$where_data_or=null){
        try {
            if ($where_data_or==null){
                $order = Db::name("order")->where($where_data)->find();

            } else {
//                $order = Db::name("order")->fetchSql()->whereOr($where_data_or)->where($where_data)->find();
                $order = Db::name('order')->where(function ($query) use($where_data_or) {
                    $query->whereOr($where_data_or);
                });
                $order = $order->where($where_data)->find();
            }
//            halt($order);
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return null;
        }
        return $order;
    }

    public function getChannel($where_data,$find=false){
        try {
            if ($find){
                $channel = Db::name("channel")->where($where_data)->find();
            } else {
                $channel = Db::name("channel")->where($where_data)->select();
            }


        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return null;
        }
        if ($channel == null){
            return null;
        }
        if (is_array($channel)){
            return $channel;
        }
        return $channel->toArray();
    }

    public function kpwx_order_add($robotid,$userid,$tdid,$status,$transid,$username,$displayname,$timestamp){
        // 当前逻辑不行，改为传统订单加价模式
        // 只有扫码了才创建订单，原因：服务端创建订单后，微信并不知道，索性服务端不创建，扫码之后再创建，之后x分钟其他人扫提示已被扫，取消不做取消订单，只做记录方便查看
        $id = Db::name("order")->insert([
            'tdid'=>$tdid,
            'userid'=>$userid,
            'status'=>$status,
            'trade_no'=>$this->create_trade_no(),
            'robotid'=>$robotid,
            'type'=>'0',
            'transid'=>$transid,
            'username'=>$username,
            'timestamp'=>$timestamp,
            'displayname'=>$displayname
        ],true);
        if ($id != 0){
            return true;
        }else {
            return false;
        }
    }

    /**
     *
     * @param string $nickname 店长名称
     * @param string $robotid 店员机器人id/自挂店长机器人id
     * @param string $timestamp 支付完成时间戳10位
     * @param string $money 支付金额
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function kpwx_dianyuan_order_edit($nickname,$robotid,$timestamp,$money){
        $orderinfo = Db::name("order")->where([
            'money'=>$money,
            'type'=>'0',
            'dz_nickname'=>$nickname,
            'robotid'=>$robotid,
            'status'=>'0',
        ])->order('addtime','desc')->find();
//        return ['code'=>false,'msg'=>'无此订单'.$orderinfo];
        if ($orderinfo == null){
            return ['code'=>false,'msg'=>'无此订单'];
        }
        try {
            $id = Db::name("order")->where(['id'=>$orderinfo['id']])->update([
                'successtime' => $timestamp,
                'successtime_time' => date('Y-m-d H:i:s',$timestamp),
                'getrealmoney'=>$money,
                'getmoney'=>$money,
                'status'=>'2'
            ]);
        } catch (\think\db\exception\DbException $e) {
            return ['code'=>false,'msg'=>'数据库错误'.$e->getMessage()];
        }
        if ($id != 0){
            return ['code'=>true,'msg'=>'success'];
        }else {
            return ['code'=>false,'msg'=>'没有可用的订单'];
        }
    }
    public function kpwx_order_edit($robotid,$userid,$tdid,$status,$transid,$username,$displayname,$timestamp,$voice_content=null,$outtradeno=null){
        try {
//            $id = Db::name("order")->where([
//                'transid' => $transid
//            ])->update([
//                'status' => $status,
//                'username' => $username,
//                'timestamp' => $timestamp,
//                'displayname' => $displayname,
//                'getrealmoney' => $voice_content,
//                'getmoney' => $voice_content,
//                'out_trade_no' => $outtradeno
//            ]);
            $id = Db::name("order")->where([
                'type' => '0',
                'realmoney' => $voice_content,
                'tdid' => $tdid,
                'userid' => $userid,

            ])->update([
                'status'=>$status,
                'robotid'=>$robotid,
                'transid'=>$transid,
                'username'=>$username,
                'displayname'=>$displayname,
                'getrealmoney' => $voice_content,
                'getmoney' => $voice_content,
                'out_trade_no' => $outtradeno,
                'successtime' => (string)$timestamp,
                'scantime_time' => date('Y-m-d H:i:s',$timestamp)
            ]);
        } catch (\think\db\exception\DbException $e) {
            return false;
        }
        if ($id != 0){
            return true;
        }else {
            return false;
        }

    }

    public function channel_heartbeat($userid,$robotid,$nickname,$avatar_address,$collection_type=4){
        $where_list = [
            'software_id'=>$robotid,
            'type'=>'0'
        ];

        if ($userid!=0){
            $where_list['collection_type'] =  $collection_type;
            $where_list['userid']=$userid;
        } else {
            $where_list['collection_type'] = 4;
            $where_list['userid'] = 0;
        }
        try {

            $id = Db::name("channel")->where($where_list)->find();
        } catch (\think\db\exception\DbException $e) {

            return false;
        }
        if ($id == null){
            return false;
        }
        // 是否禁用
        if ($id['status']=='1'){

        }
        if ($userid==0){
            Db::name("channel")->where([
                'software_main_id'=>$id['id'],
                'collection_type' => '0'
            ])->update([
                'lasttime'=>time()
            ]);
        }

        $id = Db::name("channel")->where(['id'=>$id['id']])->update([
            'lasttime'=>time(),
            'nickname'=>urldecode($nickname),
            'avatar_address'=>urldecode($avatar_address)
        ]);
        if ($id != 0){
            return true;
        }else {
            return false;
        }
    }

    public function page_channel($page=1,$option='4',$where_str=null,$userid=0,$type='0',$collection_type='4'){
        try {
            $where_array = [
                'userid' => $userid,

            ];
            if ($type!=-1){
                $where_array['type'] = $type;
            }
            if ($collection_type==5){
                $where_array[] = ['collection_type','<>','4'];
            } else {
                $where_array['collection_type'] = $collection_type;
            }

            if ($option!='4'){
                switch ($option){
                    case '0':
                    case '1':
                        $where_array['status'] = $option;
                        break;
                    case '2':
                        $pay_heartbeat = $this->getConfig("pay_heartbeat");
                        $where_array[] = ['lasttime','>',time()-$pay_heartbeat];
                        break;
                    case '3':
                        $pay_heartbeat = $this->getConfig("pay_heartbeat");
                        $where_array[] = ['lasttime','<=',time()-$pay_heartbeat];
                        break;
                }
            }
//            if ($where_str!=null){
//                $where_array[] = ['id','like',$where_str];
//            }
//            ->where(['id','like',$where_str])
            if ($where_str!=null){
                $channel = Db::name("channel")->fetchSql()->where($where_array)->order('id', 'desc')->paginate(10);
            } else {
                $channel = Db::name("channel")->fetchSql()->where($where_array)->order('id', 'desc')->paginate(10);
            }
            // 获取分页显示
            $page = $channel->render();
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            halt($e->getMessage());
            return null;
        }
        return ['page'=>$page,'list'=>$channel];
    }

    //验证码
    public function validate($lot_number,$captcha_output,$pass_token,$gen_time){
//        获取数据库配置
        $captcha_id = $this->getConfig("geetest_id");
        $geetest_key = $this->getConfig("geetest_key");
        $Geetest = new Geetest($captcha_id, $geetest_key);
        return $Geetest->validate($lot_number,$captcha_output,$pass_token,$gen_time);

    }

    /**
     * 获取客户端ip ，传入type优先获取传入的，否则自动获取最真实的客户端ip
     * @param string $type HTTP_X_FORWARDED_FOR HTTP_CLIENT_ip REMOTE_ADDR
     * @return mixed
     */
    public function getClientIp($type=null){
        if ($type == null){
            if (array_key_exists("HTTP_X_FORWARDED_FOR",$_SERVER)){
                return $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                return $_SERVER["REMOTE_ADDR"];
            }
        } else {
            return $_SERVER[$type];
        }


    }
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return substr(sha1($string), 0, $length);
    }
    //生成签名
    public function makeSign($data, $key) {
        ksort($data);
        $signStr = '';
        foreach ($data as $k => $v) {
            if($k != 'sign' && $k != 'sign_type' && $v != ''){
                $signStr .= $k . '=' . $v . '&';
            }
        }
        $signStr = substr($signStr, 0, -1);
        $sign = md5($signStr . $key);
        return $sign;
    }

    //验证签名
    public function verifySign($data, $key) {
        if(!isset($data['sign'])) return false;
        $sign = self::makeSign($data, $key);
        return $sign === $data['sign'];
    }

    public function getdomain($url){
        $arr=parse_url($url);
        $host = $arr['host'];
        if(isset($arr['port']) && $arr['port']!=80 && $arr['port']!=443)$host .= ':'.$arr['port'];
        return $host;
    }

    /**
     * 输出加密
     * @param array $data
     * @return array|string
     */
    public function e($data){
        $key = $this->getConfig("app_aes_key");
        $iv = $this->getConfig("app_aes_iv");
        $pt = $this->getConfig("app_private_key") == null ? "" : $this->getConfig("app_private_key");
        $pb = $this->getConfig("app_public_key") == null ? "" : $this->getConfig("app_public_key");
        try{
            $Aes = new Aes($key,$iv);
            $Rsa = new Rsa($pt,$pb);
        }catch (\Exception $e){
            // 密钥未初始化，输出原文
            return $data;
        }
        // 先字符串
        $data_str = json_encode($data);
        $data_str = $Aes->encrypt($data_str);
        if ($data_str==null){
            return $data;
        }
        $data_str = $Rsa->privEncrypt($data_str);
        if ($data_str==null){
            return $data;
        }
        return $data_str;

    }
}
class Channel{
    public function change_status($id,$userid=0){
        $whereArray = [
            'id' => $id,
        ];
        if ($userid!=0){
            $whereArray['userid'] = $userid;
        }
        try {
            $channel = Db::name("channel")->where($whereArray)->find();
        } catch (\think\db\exception\DataNotFoundException|\think\db\exception\ModelNotFoundException|\think\db\exception\DbException $e) {
            return ['code'=>false,'msg'=>"此通过不存在"];
        }
        if (!$channel){
            return ['code'=>false,'msg'=>"此通过不存在"];
        }
        $status = '0';
        if ($channel['status']=='0'){
            $status = '1';
        }
        try {
            Db::name("channel")->where([
                'id' => $id
            ])->update([
                'status' => $status
            ]);
        } catch (\think\db\exception\DbException $e) {
            return ['code'=>false,'msg'=>"更新失败"];
        }
        return ['code'=>true,'msg'=>'success'];
    }
    public function delete($id){
        try {
            $id_update = Db::name("channel")->where([
                'id' => $id
            ])->delete();
        } catch (\think\db\exception\DbException $e) {
            return ['code'=>false,'msg'=>"删除失败"];
        }
        if ($id_update == 0){
            return ['code'=>false,'msg'=>"删除失败"];
        } else {
            return ['code'=>true,'msg'=>"success"];
        }
    }

    /**
     * 添加通道
     * @param int $userid
     * @param string $qrcode
     * @param int $channel_type
     * @param int $collection_type
     * @return array
     */
    public function add_channel($userid,$nickname,$software_main_id=0,$qrcode=null,$channel_type=0,$collection_type=0){
        $insert_into = [
            'userid' => $userid,
            'qrcode' => $qrcode,
            'type' => $channel_type,
            'nickname' => $nickname,
            'collection_type' => $collection_type,
            'software_main_id' => $software_main_id,
            'addtime' => time(),
            'status' => 0,
        ];
        if ($collection_type==4){
            unset($insert_into['nickname']);
            $insert_into['software_id'] = $nickname;
        }
        $id = Db::name("channel")->insert($insert_into,true);
        if ($id == 0){
            return ['code'=>false,'msg'=>"添加失败"];
        }
        return ['code'=>true,'msg'=>"success"];

    }
}
class Aes
{
    private $key = null;
    private $iv = null;

    /**
     * @param $key string 密钥
     * @param $iv string iv，需要16位非特殊字符
     */
    public function __construct($key,$iv)
    {
        // 需要小伙伴在配置文件app.php中定义aeskey
        $this->iv = $iv;
        $this->key = $key;
        $this->key = base64_encode(hash('sha256', $this->key, true));

    }

    /**
     * 加密
     * @param $input
     * @return string
     */
    public function encrypt($input)
    {
        $data = openssl_encrypt($input, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->iv);
        $data = base64_encode($data);
        return $data;
    }

    /**
     * 解密
     * @param $input
     * @return false|string
     */
    public function decrypt($input)
    {
        $decrypted = openssl_decrypt(base64_decode($input), 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->iv);
        return $decrypted;
    }
    function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {

            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
}


class Rsa
{

    private string $private_key;
    private string $public_key;

    public function __construct($private_key, $public_key){
        $this->private_key = $private_key;
        $this->public_key = $public_key;
    }
    /**
     * 获取私钥
     * @return bool|resource
     */
    private function getPrivateKey()
    {
//        $abs_path = dirname(__FILE__) . '/rsa_private_key.pem';
//        $content = file_get_contents($abs_path);
//        return openssl_pkey_get_private($content);

        return openssl_pkey_get_private($this->private_key);
    }

    /**
     * 获取公钥
     * @return bool|resource
     */
    private function getPublicKey()
    {
//        $abs_path = dirname(__FILE__) . '/rsa_public_key.pem';
//        $content = file_get_contents($abs_path);
//        return openssl_pkey_get_public($content);
        return openssl_pkey_get_public($this->public_key);
    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public function privEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, self::getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, self::getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function privDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? $decrypted : null;
    }

    /**
     * 生成公钥私钥
     */
    public function generateCertKey()
    {
//        $dn = array('countryName'=>'CN', 'stateOrProvinceName'=>'beijing', 'localityName'=>'beijing','organizationName'=>'amen',
//            'organizationalUnitName'=>'amen', 'commonName'=>'amen', 'emailAddress'=>'amen@ymwl.edu.kg');
        //创建公钥
        $res = openssl_pkey_new(["private_key_bits" => 4096]);//array('private_key_bits'=>512) 这一串参数不加，否则只能加密54个长度的字符串
        //提取私钥
        $flag = openssl_pkey_export($res, $private_key);
        if ($flag===false){
            return ['code'=>201,'private_key'=>null,'public_key'=>null,'msg'=>"生成私钥失败"];
        }
        //生成公钥
        $public_key = openssl_pkey_get_details($res);
        if ($public_key===false){
            return ['code'=>201,'private_key'=>null,'public_key'=>null,'msg'=>'生成公钥失败'];
        }
        $public_key_str = $public_key['key'];
        $this->public_key = $public_key_str;
        $this->private_key = $private_key;
        return ['code'=>200,'private_key'=>$private_key,'public_key'=>$public_key_str];
    }

}

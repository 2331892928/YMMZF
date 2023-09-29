<?php
class Geetest{
    protected string $api_server = "http://gcaptcha4.geetest.com";
    protected string $captcha_id;
    protected string $captcha_key;
    public function __construct($captcha_id,$captcha_key){
        $this->captcha_id = $captcha_id;
        $this->captcha_key = $captcha_key;
    }
    public function validate($lot_number,$captcha_output,$pass_token,$gen_time){
        $sign_token = hash_hmac('sha256', $lot_number, $this->captcha_key);
        $query = array(
            "lot_number" => $lot_number,
            "captcha_output" => $captcha_output,
            "pass_token" => $pass_token,
            "gen_time" => $gen_time,
            "sign_token" => $sign_token
        );
        $url = sprintf($this->api_server . "/validate" . "?captcha_id=%s", $this->captcha_id);
        $res = $this->post_request($url,$query);
        $obj = json_decode($res,true);
//        echo sprintf('{"login":"%s","reason":"%s"}', $obj['result'], $obj['reason']);
        if ($obj['result']!="success" || $obj['status']!="success"){
            return ['code'=>false,'msg'=>$obj['reason']];
        }
        return ['code'=>true,'msg'=>"success"];
    }
    private function post_request($url, $postdata) {
        $data = http_build_query($postdata);

        $options    = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'content' => $data,
                'timeout' => 5
            )
        );
        $context = stream_context_create($options);
        $result    = file_get_contents($url, false, $context);
        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);
        if($responsecode != 200){
            $result = array(
                "result" => "success",
                "status" => "success",
                "reason" => "request geetest api fail"
            );
            return json_encode($result);
        }else{
            return $result;
        }
    }
}
<?php
namespace app\controller;

class Wx
{
    private $appid  = "wxc6e2459bdea9cd72";   //你的appId
    private $secret = "2d46bb85fee34fba3401796b5a224283";   //你的appSecret

    //获取用户的openid
    function index(){
        //1.获取到code=================================
       // $redirect_uri = urlencode("http://".$_SERVER['HTTP_HOST']."/BiYe2/public/wx/getUserInfo");//这里的地址需要http://
       // $redirect_uri = ("http://".$_SERVER['HTTP_HOST']."/BiYe2/public/wx/getUserInfo");
       // dump( $redirect_uri);
        $redirect_uri = 'http://6pua9f.natappfree.cc/BiYe2/public/wx/getUserInfo';
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
        //header('location:'.$url);
        return redirect($url);
    }

    function getUserInfo(){
        $data = request()->get();
        $code   = $data['code'];
        //2.获取到网页授权的access_token===============
        //第一步，获取access_token
        //新建一个文件存储access_token，避免多次获取，access_token的有效时间是2个小时，这里的目录是根目录：www.xxx.xxx/access_token.txt
        $file_name = 'access_token.txt';
        if (!file_exists($file_name)) {     //文件不存在，新建并附赋权限
            $my_file = fopen($file_name, "w");
            $file_name = $my_file;
            //chmod($file_name,0777);
        }
        $content = file_get_contents($file_name);  //读取文件内容
        $arr = json_decode($content,true);  //转化为数组
        if (is_array($arr)) {
            if (isset($arr['end_time']) && $arr['end_time'] > time()) {  //access_token未过时，直接拿来使用
                $token = $arr;
            } else {    //access_token超时，重新获取
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->secret";
                $token = $this->getJson($url);
                $token['start_time'] = time();
                $token['end_time']   = time()+7000;
                $my_file = fopen($file_name, "w") or die("Unable to open file!");
                $txt = json_encode($token);
                fwrite($my_file, $txt);
                fclose($my_file);
            }
        } else {     //用于新建文件时的写入
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->secret";
            $token = $this->getJson($url);
            $token['start_time'] = time();
            $token['end_time']   = time()+7000;
            $my_file = fopen($file_name, "w") or die("Unable to open file!");
            $txt = json_encode($token);
            fwrite($my_file, $txt);
            fclose($my_file);
        }
        //第二步:取得openid
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->secret&code=$code&grant_type=authorization_code";
        $oauth2 = $this->getJson($oauth2Url);
        //第三步:根据全局access_token和openid查询用户信息
        $access_token = $token["access_token"];
        //var_dump($oauth2);
        $openid = $oauth2['openid'];
        $get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $user_info = $this->getJson($get_user_info_url);

        //打印用户信息
        //print_r($user_info);
        //返回用户信息，这里不能直接返回数组
        return json($user_info);
    }

    function getJson($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }
}

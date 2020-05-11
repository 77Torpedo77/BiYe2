<?php

/*
文件名：Users.php
描述：与操作相关的controller
作者：星辰后端 18级 廖武耀
修改日志：2020.2.22 添加微信相关方法，并于getuserinfo方法后增加返回分数--星辰后端 19级 潘永雷
*/

namespace app\controller;
//header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
//header('Access-Control-Allow-Headers:DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Max-Age: 1728000');
use think\Controller;
use think\Loader;
use think\facade\Request;
use Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Palette\RGB;
use Imagine\Image\ImageInterface;
use app\model\Config;
use app\model\Problems;
use app\model\Record;
use app\model\User;
use app\model\Image;
use think\facade\Log;

class Users extends Controller
{
	function initialize()
    {
        session_start();
		$this->ConfigModel = new Config();
		$this->ProblemsModel = new Problems();
		$this->RecordModel = new Record();
		$this->UserModel = new User();
		$this->ImageModel = new Image();
    }
	//提交答题情况
	function submit()
	{
		$getuserinfo_return;
		if(empty(Request::post('openid')))
		{
			$getuserinfo_return['try']=-1;
			return json($getuserinfo_return);
		}
		//查找用户信息并更新考试次数
		$getuserinfo_mysql=$this->UserModel->where('wxid',Request::post('openid'))->select();
		if(empty($getuserinfo_mysql[0]))
		{
			$getuserinfo_return['try']=1;
			$newuser=new User;
			$newuser->wxid=Request::post('openid');
			$newuser->try=1;
			$newuser->save();
		}
		else
		{
			$getuserinfo_return['try'] = $getuserinfo_mysql[0]['try'] + 1;
			if($getuserinfo_return['try'] > 2)
			{
				return json($getuserinfo_return);
			}
			$newuser = User::where('wxid',Request::post('openid'))->find();
			$newuser->try = $newuser->try + 1;
			$newuser->save();
		}
		$scoresum=0;
		$correct_array = Request::post('correct');
		//根据examid查找
		$oldrecord = Record::where('wxid',Request::post('openid'))->where('score',-1)->where('id',Request::post('examid'))->find();
		if(empty($oldrecord))
		{
			$getuserinfo_return['try']=-1;
			return json($getuserinfo_return);
		} 
		$problem_array=explode(",",$oldrecord->problemid); 
		//统计分数并更新展示次数与正确次数
		for($i=0;$i<count($correct_array);++$i)
		{
			$aproblem = Problems::where('id',$problem_array[$i])->find();
			$aproblem->show_time = $aproblem->show_time + 1;
			//for($j=0;$j<count($correct_array);++$j)
			//{
				if($aproblem->answer==$correct_array[$i])
				{
					$aproblem->pass_time = $aproblem->pass_time + 1;
					$scoresum += 10;
				}
			//}
			$aproblem->save();
		}
		$oldrecord->score=$scoresum;
		$oldrecord->save();
		$getuserinfo_return['score']=$scoresum;
		return json($getuserinfo_return);
	}
	//获取问题
	function getproblem()
	{
		$newrecord_problemid="";
		$getproblem_data=$this->ProblemsModel->where('type',"基础知识")->limit(4)->orderRaw('rand()')->select();
		for($i=0;$i<4;++$i)
		{
			$return_data[$i]=$getproblem_data[$i];
			$newrecord_problemid = $newrecord_problemid . $getproblem_data[$i]['id'] . ",";
		}
		$getproblem_data=$this->ProblemsModel->where('type',"名教师")->limit(1)->orderRaw('rand()')->select();
		$return_data[4]=$getproblem_data[0];
		$newrecord_problemid = $newrecord_problemid . $getproblem_data[0]['id'];
		$getproblem_data=$this->ProblemsModel->where('type',"名课程")->limit(1)->orderRaw('rand()')->select();
		$return_data[5]=$getproblem_data[0];
		$newrecord_problemid = $newrecord_problemid . "," . $getproblem_data[0]['id'];
		$getproblem_data=$this->ProblemsModel->where('type',"名讲座")->limit(1)->orderRaw('rand()')->select();
		$return_data[6]=$getproblem_data[0];
		$newrecord_problemid = $newrecord_problemid . "," . $getproblem_data[0]['id'];
		$getproblem_data=$this->ProblemsModel->where('type',"大事记")->limit(3)->orderRaw('rand()')->select();
		for($i=0;$i<3;++$i)
		{
			$return_data[$i+7]=$getproblem_data[$i];
			$newrecord_problemid = $newrecord_problemid . "," . $getproblem_data[$i]['id'];
		}
		$newrecord = new Record;
		$newrecord->wxid=Request::post('openid');
		$newrecord->problemid=$newrecord_problemid;
		$newrecord->score=-1;
		$newrecord->save();
		$return_datas['examid'] = $newrecord->id;
		$return_datas['data'] = $return_data;
		return json($return_datas);
	}
	//获取用户信息
	function getuserinfo()
	{
		if(empty(Request::post('openid')))
		{
			$getuserinfo_return['try']=-1;
			return json($getuserinfo_return);
		}
		$getuserinfo_mysql=$this->UserModel->where('wxid',Request::post('openid'))->select();
		$final_score= Record::where('wxid',Request::post('openid'))->order('score','desc')->limit(1)->find();
		if(empty($getuserinfo_mysql[0]))
		{
			$getuserinfo_return['try']=0;
			$newuser = new User;
			$newuser->wxid=Request::post('openid');
			$newuser->try=0;
			$newuser->save();
		}
		else
		{
			$getuserinfo_return['try'] = $getuserinfo_mysql[0]['try'];
		}
		if(empty($final_score))
		{
			$getuserinfo_return['score']='-700';
		}
		else
		{
			$getuserinfo_return['score']=$final_score['score'];
		}
		return json($getuserinfo_return);
	}
	//获取用户证书
	function getusercla()
	{
		if(!empty(Request::post('nickname')) && !empty(Request::post('iconurl')) && !empty(Request::post('openid')))
		{
			$arecord= Record::where('wxid',Request::post('openid'))->order('score','desc')->limit(1)->find();
			$newimage= new Image;
			if(!empty($arecord))
			{
				if($arecord['score']!=-1)
				{
					$newimage->score=$arecord['score'];
					$newimage->nickname=Request::post('nickname');
					$newimage->url=Request::post('iconurl');
					$newimage->save();
					return json(['imageurl' => $newimage->id]);
				}
			}
		}
		return json(['imageurl' => "error"]);
	}
	function getimage()
	{
		if(!empty($_GET['imageid']))
		{
			$aimage = Image::where('id',$_GET['imageid'])->find();
			if(!empty($aimage))
			{
				$imagine = new Imagine\Gd\Imagine();
				$background_image = $imagine->open(__DIR__ . '/background.png');
				$backgronud_width=$background_image->getsize()->getWidth()/2;
				$background_image1 = $imagine->open(__DIR__ . '/background.png');
				$hat_image = $imagine->open(__DIR__ . '/hat.png');
				$score_image = $imagine->open(__DIR__ . '/' . $aimage['score'] . '.png');
				if($aimage['score']=="100")
				{
					$xue_image=$imagine->open(__DIR__ . '/xue1.png');
				}
				else if($aimage['score']=="90" || $aimage['score']=="80" || $aimage['score']=="70")
				{
					$xue_image=$imagine->open(__DIR__ . '/xue2.png');
				}
				else if($aimage['score']=="60" || $aimage['score']=="50" || $aimage['score']=="40")
				{
					$xue_image=$imagine->open(__DIR__ . '/xue3.png');
				}
				else if($aimage['score']=="30" || $aimage['score']=="20" || $aimage['score']=="10" || $aimage['score']=="0" || $aimage['score']=="-1")
				{
					$xue_image=$imagine->open(__DIR__ . '/xue4.png');
				}
				$xue_image->resize(new Box($xue_image->getsize()->getWidth()/$xue_image->getsize()->getHeight()*60,60));
				$touxiang_image = $imagine->open($aimage['url']);//打开头像图
				if($touxiang_image->getsize()->getWidth()>$touxiang_image->getsize()->getHeight())
				{
					$touxiang_image->resize(new Box($touxiang_image->getsize()->getWidth()/$touxiang_image->getsize()->getHeight()*85,85));
					$background_image->paste($touxiang_image,new point($backgronud_width-$touxiang_image->getsize()->getWidth()/2,100-$touxiang_image->getsize()->getHeight()/2));//合成头像
				}
				else
				{
					$touxiang_image->resize(new Box(85,$touxiang_image->getsize()->getHeight()/$touxiang_image->getsize()->getWidth()*85));
					$background_image->paste($touxiang_image,new point($backgronud_width-$touxiang_image->getsize()->getWidth()/2,100-$touxiang_image->getsize()->getHeight()/2));//合成头像
				}
				$background_image->paste($background_image1,new point(0,0));
				$background_image->paste($xue_image,new point($backgronud_width-$xue_image->getsize()->getWidth()/2,220));//合成学
				$background_image->paste($score_image,new point(20,180));//合成分数
				$background_image->paste($hat_image,new point(120,30));//合成帽子
				return response($background_image->show('png'))->header(['Content-Type' => 'image/png']);
			}
			else
			{
				return '-800';
			}
		}
		else
		{
			return '-900';
		}
	}
	function testimage()
	{
		$imagine = new Imagine\Gd\Imagine();
		$background_image = $imagine->open(__DIR__ . '/background.png');
		$backgronud_width=$background_image->getsize()->getWidth()/2;
		$background_image1 = $imagine->open(__DIR__ . '/background.png');
		$hat_image = $imagine->open(__DIR__ . '/hat.png');
		$score_image = $imagine->open(__DIR__ . '/100.png');
		$xue_image=$imagine->open(__DIR__ . '/xue1.png');
		$xue_image->resize(new Box($xue_image->getsize()->getWidth()/$xue_image->getsize()->getHeight()*60,60));
		$touxiang_image = $imagine->open(__DIR__ . '/touxiang.png');//打开头像

		if($touxiang_image->getsize()->getWidth()>$touxiang_image->getsize()->getHeight())
		{
			$touxiang_image->resize(new Box($touxiang_image->getsize()->getWidth()/$touxiang_image->getsize()->getHeight()*85,85));
			$background_image->paste($touxiang_image,new point($backgronud_width-$touxiang_image->getsize()->getWidth()/2,100-$touxiang_image->getsize()->getHeight()/2));//合成头像
		}
		else
		{
			$touxiang_image->resize(new Box(85,$touxiang_image->getsize()->getHeight()/$touxiang_image->getsize()->getWidth()*85));
			$background_image->paste($touxiang_image,new point($backgronud_width-$touxiang_image->getsize()->getWidth()/2,100-$touxiang_image->getsize()->getHeight()/2));//合成头像
		}
		$background_image->paste($background_image1,new point(0,0));
		$background_image->paste($xue_image,new point($backgronud_width-$xue_image->getsize()->getWidth()/2,220));//合成学
		$background_image->paste($score_image,new point(20,180));//合成分数
		$background_image->paste($hat_image,new point(120,30));//合成帽子
		return response($background_image->show('png'))->header(['Content-Type' => 'image/png']);
	}
	//以下为新增方法
	public function checksignature(Request $request)//微信接口认证方法
	{
		Log::write($request::param());
	    $signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];
	    $echostr = $_GET["echostr"];
		
	    $token = 'uestc';
	    $tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr, SORT_STRING);
	    $tmpStr = implode( $tmpArr );
	    $tmpStr = sha1( $tmpStr );
	    if( $tmpStr == $signature ){
	        return $echostr;
	    }else{
	        return '验证失败';
	    }
	}
	public function getopenid()
	{
		if (!isset($_GET['code'])) 
		{
			$url = $this->getcode();
            Header("Location: $url");
            exit();
        }
        else
        {
			$code   = $_GET['code'];
			$appid  = 'wxc6e2459bdea9cd72';
			$secret = '2d46bb85fee34fba3401796b5a224283';
			$code   = $_GET['code'];
			$weixin1 =  file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code');//通过code换取网页授权access_token
			$jsondecode = json_decode($weixin1); //对JSON格式的字符串进行编码
			$array = get_object_vars($jsondecode);//转换成数组
			if (!isset($array['openid'])) {
				$url = $this->getcode();
            	Header("Location: $url");
            	exit();
			}
			$openid = $array['openid'];//输出openid
			$access_token = $array['access_token'];
			$weixin2 =  file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');//通过access_token和openid换取用户信息
			$return_data['nickname']=get_object_vars(json_decode($weixin2))['nickname'];
			$return_data['openid']=get_object_vars(json_decode($weixin2))['openid'];
			$return_data['iconurl']=get_object_vars(json_decode($weixin2))['headimgurl'];
			return json($return_data);
		}
		return "error";


		
	}
	public function getcode()
	{
		$appid  = 'wxc6e2459bdea9cd72';
		$redirect_uri = 'http://kvczys.natappfree.cc/BiYe2/public/users/getopenid';//获取code后跳转getopenid方法取得openid
		$url    = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
		return $url;
		// $appid  = 'wxc6e2459bdea9cd72';
		// $redirect_uri = 'http://kvczys.natappfree.cc/BiYe2/public/users/getcode';//获取code后跳转getopenid方法取得openid
		// $url    = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
 

 	// 	$ci = curl_init();
  //       /* Curl settings */
  //       curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  //       curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
  //       curl_setopt($ci, CURLOPT_TIMEOUT, 30);
  //       curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

  //       curl_setopt($ci, CURLOPT_URL, $url);
  //       $response = curl_exec($ci);

  //       curl_close($ci);
  //       $info = json_decode($response, TRUE);
  //       return $info;
	}


	function getrank()
	{
		$ranks = $this->ImageModel->field('url,nickname,wxid,score')->group('wxid')->limit(50)->orderRaw('score desc')->select();
		//$ranks = $this->RecordModel->field('wxid,score,max(score)')->group('wxid')->limit(50)->orderRaw('score desc')->select();
		for ($i=0; $i < count($ranks); $i++) 
		{ 
			$return_rank[$i]['wxid']=$ranks[$i]->wxid;
			$return_rank[$i]['score']=$ranks[$i]->score;
			$return_rank[$i]['url']=$ranks[$i]->url;
			$return_rank[$i]['nickname']=$ranks[$i]->nickname;
			//var_dump($records[$i]->score.'----'.$records[$i]->wxid);
		}
		return json($return_rank);
		
	}

	function saveinfo()
	{
		if(!empty(Request::post('nickname')) && !empty(Request::post('iconurl')) && !empty(Request::post('openid')))
		{
			$arecord= Record::where('wxid',Request::post('openid'))->order('score','desc')->limit(1)->find();
			$newimage= new Image;
			if(!empty($arecord))
			{
				if($arecord['score']!=-1)
				{
					$newimage->score=$arecord['score'];
					$newimage->wxid=Request::post('openid');
					$newimage->nickname=Request::post('nickname');
					$newimage->url=Request::post('iconurl');
					$newimage->save();
					return json(['imageurl' => $newimage->id]);
				}
			}
		}
		return json(['imageurl' => "error"]);
	}

    function index()
    {
        return 1;
    }

     public function get_authorize_url($redirect_uri = 'http://kvczys.natappfree.cc/BiYe2/public/users/getopenid', $state = '')
    {
        $redirect_uri = urlencode($redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }
    /**
     * 获取微信openid
     */
    public function getOpenid2($turl)
    {
        if (!isset($_GET['code'])) {
            //触发微信返回code码
            $url = $this->get_authorize_url($turl, $this->state);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $access_info = $this->get_access_token($code);
            return $access_info;
        }
    }
    /**
     * 获取授权token网页授权
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token($code = '')
    {
        $appid = $this->app_id;
        $appsecret = $this->app_secret;
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $appsecret . "&code=" . $code . "&grant_type=authorization_code";
        //echo $token_url;
        $token_data = $this->http($token_url);
        // var_dump( $token_data);
        if ($token_data[0] == 200) {
            $ar = json_decode($token_data[1], TRUE);
            return $ar;
        }
        return $token_data[1];
    }
    public function http($url, $method = '', $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }
}
?>

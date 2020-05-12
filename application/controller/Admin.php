<?php

/*
文件名：Index.php
描述：管理controller
作者：星辰后端 18级 廖武耀
*/

namespace app\controller;
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');
use think\Controller;
use think\Loader;
use think\facade\Session;
use app\model\Config;
use app\model\Problems;
use app\model\Record;
use app\model\User;

class Admin extends \think\Controller
{
	function initialize()
    {
        session_start();
		$this->ConfigModel = new Config();
		$this->ProblemsModel = new Problems();
		$this->RecordModel = new Record();
		$this->UserModel = new User();
    }
	//登录
	function login()
	{
		if(!empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/");
		}
		if(!empty($_POST['username']) && !empty($_POST['password']))
		{
			$login_username=$this->ConfigModel->where("configname","username")->find();
			$login_password=$this->ConfigModel->where("configname","password")->find();
			if($login_username->configvalue==$_POST['username'] && $login_password->configvalue==md5($_POST['password']))
			{
				$_SESSION['islogin']=$login_username;
				$this->success('登录成功', '/BiYe2/public/index.php/admin/');
			}
			else
			{
				$this->error('用户名或密码错误');
			}
		}
		else
		{
	        return $this->fetch();
		}
	}
	//问题列表
	function problemlist()
	{
		if(empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/login");
		}
		$problem_list=$this->ProblemsModel->select();
		$this->assign("problem_list",$problem_list);
		return $this->fetch();
	}
	//增加问题
	function add()
	{
		if(empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/login");
		}
		if(!empty($_POST['submit']))
		{
			if(empty($_POST['title']))
			{
				$this->error('标题为空');
			}
			if(empty($_POST['option1']) || empty($_POST['option2']) || empty($_POST['option3']) || empty($_POST['option4']) )
			{
				$this->error('某一选项为空');
			}
			$new_problem = new Problems;
			$new_problem->question=$_POST['title'];
			$new_problem->options=$_POST['option1'] . "," . $_POST['option2'] . "," . $_POST['option3'] . "," . $_POST['option4'];
			switch($_POST['answer'])
			{
				case 1:
					//$new_problem->answer=$_POST['option1'];
					$new_problem->answer=1;
					break;
				case 2:
					//$new_problem->answer=$_POST['option2'];
					$new_problem->answer=2;
					break;
				case 3:
					//$new_problem->answer=$_POST['option3'];
					$new_problem->answer=3;
					break;
				case 4:
					//$new_problem->answer=$_POST['option4'];
					$new_problem->answer=4;
					break;
			}
			switch($_POST['kind'])
			{
				case 1:
					$new_problem->type="第一幕";
					break;
				case 2:
					$new_problem->type="第二幕";
					break;
				case 3:
					$new_problem->type="第三幕";
					break;
				case 4:
					$new_problem->type="第四幕";
					break;
				case 5:
					$new_problem->type="第五幕";
					break;
			}
			$new_problem->show_time=0;
			$new_problem->pass_time=0;
			$new_problem->save();
			$this->success('增加成功', "/BiYe2/public/index.php/admin/problemlist");
		}
		else
		{
	        return $this->fetch();
		}
	}
	//删除问题
	function remove($remove_id=0)
	{
		if(empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/login");
		}
		if($remove_id>0)
		{
			$this->ProblemsModel->where("id",$remove_id)->delete();
			$this->success('删除成功', "/BiYe2/public/index.php/admin/problemlist");
		}
	}
	//统计数据
	function datalist()
	{
		if(empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/login");
		}
		$data_list['allman']=$this->UserModel->count();
		$data_list['allexam']=$this->RecordModel->count();
		$data_list['1man']=$this->UserModel->where('try',1)->count();
		$data_list['2man']=$this->UserModel->where('try',2)->count();
		$data_list['score100']=$this->RecordModel->where("score",100)->count();
		$data_list['score90']=$this->RecordModel->where("score",90)->count();
		$data_list['score80']=$this->RecordModel->where("score",80)->count();
		$data_list['score70']=$this->RecordModel->where("score",70)->count();
		$data_list['score60']=$this->RecordModel->where("score",60)->count();
		$data_list['score50']=$this->RecordModel->where("score",50)->count();
		$data_list['score40']=$this->RecordModel->where("score",40)->count();
		$data_list['score30']=$this->RecordModel->where("score",30)->count();
		$data_list['score20']=$this->RecordModel->where("score",20)->count();
		$data_list['score10']=$this->RecordModel->where("score",10)->count();
		$data_list['score0']=$this->RecordModel->where("score",0)->count();
		$this->assign("data_list",$data_list);
		return $this->fetch();
	}
    function index()
    {
		if(empty($_SESSION['islogin']))
		{
			$this->redirect("/BiYe2/public/index.php/admin/login");
		}
		return $this->fetch();
    }
}

?>

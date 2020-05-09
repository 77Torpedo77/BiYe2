<?php

/*
文件名：Index.php
描述：默认controller
作者：星辰后端 18级 廖武耀
*/

namespace app\controller;

use think\Controller;
use think\Loader;


class Index extends Controller
{
	function initialize()
    {
        session_start();
    }
    function index()
    {
        return "Hello,world!";
    }
}

?>

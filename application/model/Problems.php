<?php

/*
文件名：Problems.php
描述：model
作者：星辰后端 18级 廖武耀
*/

namespace app\model;

use think\Model;

/*
7个字段，分别为
id          int，问题的id
question    text，问题的标题
options     text,问题的选项
answer      text，答案
type        text，分类
show_time   int，答题数
pass_time   int，答对数
*/
class Problems extends Model
{
}

?>

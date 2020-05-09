# 成电毕业生项目后端代码仓库  
##### 作者：18级星辰后端廖武耀   

README.MD分为三部分，接口文档与代码部署与后台管理  

-------     

## 接口文档   

以下接口皆为json格式  

### 获取问题   

URL: /users/getproblem   

HTTP METHOD:POST   

Request：  
```  
{    
    "openid":"qwerrt"
}
```  
Return：  
```  
{
    "examid":12,//这次考试的id
    "data":[
        {
            "id":6,//题目id
            "question":"示例题目",//题目
            "options":"选项一,选项二,选项三,选项四",//用逗号分隔的选项
            "answer":"选项一",//答案
            "type":"基础知识",//类型
            "show_time":0,//展示次数
            "pass_time":0//正确次数
        },
        {
            //后面还有九个
        },
    ]
}
```  

### 获取用户信息  

URL:/users/getuserinfo

HTTP METHOD:POST  

Request:  
```
{
    "openid":"qwerrt"
}
```
Return:  
```
{
    "try":1
    //已考试次数
    //这个值可以为-1,0,1,2
    //如果是-1则表示openid为空
}
```


### 提交问卷

URL:/users/submit

HTTP METHOD:POST

Request:  
```
{
    "openid":"qwerrt",
    "examid":12,
    "correct":[1,3,5,7]//这个是从0-9
}
```
Return:  
```
{
    "try":1
    //已考试次数
    //这个值可以为-1,0,1,2,3
    //如果是-1则表示openid为空或者有误
    //如果是3则表示用户已经考了两次，刚才提交那次并没有存入数据库
}
```


### 生成证书

URL:/users/getusercla

HTTP METHOD:POST

Request:
```
{
    "openid":"ssss",
    "iconurl":"aaaa",//头像的url
    "nickname":"啊啊啊"//微信昵称
}
```

Return:  
```
{
    "imageurl":"http://biye.stuhome.com/users/getimage?imageid=777"
    //错误返回"imageurl":"error"
}
```  

------

## 代码部署    

### 访问目录  

建议是将public文件夹设置为访问目录，然后给予访问上级目录的权限，并将runtime文件设为777权限。  

当然也可以不那么做，那么所有接口前面都要加public，比如public/users/getuserinfo  

### URL重写

假设上面将访问目录设为public，默认的接口URL其实是index.php/users/getuserinfo  

这个index.php是可以隐藏的，而且也比较简单  

[thinkphp的开发文档](https://www.kancloud.cn/manual/thinkphp5_1/353955)中，针对Apache和Nginx的重写方法，已经写得非常详细了，我这里也不复述了。  

如果这两者都没做，那么URL就是public/index.php/users/getuserinfo  

### 数据库

数据库的用户名、密码等，在config/database.php下修改。  

目录下提供了一个uestcbiye2019.sql，用来生成数据库结构。  

导入后，后台的默认用户名是star，密码是7777777   

--------

## 后台管理  

访问/admin/即可  

默认用户名star，密码7777777  

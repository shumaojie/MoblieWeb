<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php $this->display('inc_skin_lr.php',0,'用户登录'); ?>
<link href="/skin/main/login.css" rel="stylesheet" />
</head>
<body style=" background:#f4f4f4; font-size:1.4rem;">
<div class="acc_inner_bg" style=" "></div>
<div class="acc_logo"></div>
    <div style="position:relative; z-index:1;">
     <form action="/index.php/user/loginedto" method="post" onajax="userBeforeLoginto"  enter="true" call="userLoginto" target="ajax">
		      <div class="bgwhite mar15">
                <div class="login-box line-bottom">
                   <div class="login-input">
                    <input type="text" maxlength="50" placeholder="用户名" value="" name="username" id="username" autocomplete="on" style="width:18rem;"/>
				   </div>
                </div>
                <div class="login-box">
                 <div class="login-input">
                    <input type="password" placeholder="密码" value="" name="password" id="password" autocomplete="off" style="width:18rem;"/>
                  </div>
                </div>
				<div class="login-box">
                 <div class="login-input yzm">
                    <input type="text" placeholder="验证码" value="" name="vcode" id="vcode" autocomplete="off" style="width:18rem;"/>
					<img style="cursor:pointer;" src="/index.php/user/vcode/<?=$this->time?>" class="ml10" title="看不清楚，换一张图片" onclick="this.src='/index.php/user/vcode/'+(new Date()).getTime()" align="absmiddle" border="0" height="24" width="82">
                  </div>
                </div>
           </div>
           <div class="zzc_btn denglu mar15"><a id="form_sub" class="login-btn"  href="javascript:void(0);" onclick="$(this).closest('form').submit()">登 录</a></div>          
    </form>
 </div>
 <?php $this->display('inc_footer.php') ?>
</body>
</html>

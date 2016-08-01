<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php $this->display('inc_skin_lr.php',0,'新用户注册'); ?>
<link href="/skin/main/login.css" rel="stylesheet" />
</head>

<body style=" background:#f4f4f4; font-size:1.4rem;">
<div class="acc_inner_bg" style=" "></div>
<div class="acc_logo"></div>
    <div style="position:relative; z-index:1;">
     <?php if($args[0] && $args[1]){
        
		$sql="select * from {$this->prename}links where lid=?";
		$linkData=$this->getRow($sql, $args[1]);
		$sql="select * from {$this->prename}members where uid=?";
		$userData=$this->getRow($sql, $args[0]);
	
		?>

		<form action="/index.php/user/registered" method="post" onajax="registerBeforSubmit" enter="true" call="registerSubmit" target="ajax">
        	<input type="hidden" name="parentId" value="<?=$args[0]?>" />
            <input type="hidden" name="lid" value="<?=$linkData['lid']?>"  />
		      <div class="bgwhite mar15">
                <div class="login-box line-bottom">
                   <div class="login-input">
                    <input type="text" maxlength="50" placeholder="用户名" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" value="" name="username" id="username" autocomplete="on" style="width:18rem;"/>
				   </div>
                </div>
                <div class="login-box">
                 <div class="login-input">
                    <input type="password" placeholder="密码" value="" name="password" id="password" autocomplete="off" style="width:18rem;"/>
                  </div>
                </div>
                <div class="login-box">
                 <div class="login-input">
                    <input type="password" placeholder="确认密码" value="" name="cpasswd" id="cpasswd" autocomplete="off" style="width:18rem;"/>
                  </div>
                </div>
				<div class="login-box">
                 <div class="login-input">
                    <input type="text" placeholder="QQ号码" value="" name="qq" id="qq" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" autocomplete="off" style="width:18rem;"/>
                  </div>
                </div>
				<div class="login-box">
                 <div class="login-input yzm">
                    <input type="text" placeholder="验证码" value="" name="vcode" id="vcode" autocomplete="off" style="width:18rem;"/>
					<img style="cursor:pointer;" src="/index.php/user/vcode/<?=$this->time?>" class="ml10" title="看不清楚，换一张图片" onclick="this.src='/index.php/user/vcode/'+(new Date()).getTime()" align="absmiddle" border="0" height="24" width="72">
                  </div>
                </div>
           </div>
           <div class="zzc_btn denglu mar15"><a id="form_sub" class="login-btn"  href="javascript:void(0);" onclick="$(this).closest('form').submit()">立即注册</a></div>          
          </form>
           <?php }else{?>
           <div style="text-align:center; line-height:50px; color:#FF0; font-size:20px; font-weight:bold;">链接失效！</div>
           <?php }?>
 </div>
 <?php $this->display('inc_footer.php') ?>
</body>
</html>

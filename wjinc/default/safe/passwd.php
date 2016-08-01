<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php $this->display('inc_skin.php', 0 , '会员中心－密码管理'); ?>

</head> 
 
<body>
<div id="mainbody"> 
<?php $this->display('inc_header.php'); ?>
<div class="pagetop"></div>
<div class="pagemain">
    <div class="display biao-cont">
    	<form action="/index.php/safe/setPasswd" method="post" target="ajax" onajax="safeBeforSetPwd" call="safeSetPwd">
        <h3>登录密码管理</h3>
         <dl class="wjform">
        	<dt>原始密码：</dt>
            <dd><input type="password" name="oldpassword" /></dd>
        </dl>
         <dl class="wjform">
        	<dt>新密码：</dt>
            <dd><input type="password" name="newpassword" /></dd>
        </dl>
         <dl class="wjform">
        	<dt>确认新密码：</dt>
            <dd><input type="password"   class="confirm"/></dd>
        </dl>
        <dl class="wjform">
        	<dt>&nbsp;</dt>
            <dd><input type="button" id='put_button_pass' class="btn" value="修改密码" onclick="$(this).closest('form').submit()" > 
        <input type="reset" value="重置" onClick="this.form.reset()"  class="btn"/></dd>
        </dl>
</form> 

<?php if($args[0]){ ?>
		
<form action="/index.php/safe/setCoinPwd2" method="post" target="ajax" onajax="safeBeforSetCoinPwd2" call="safeSetPwd">
    <h3 class="mt10">资金密码管理</h3>
    <div class="tips">
        <dl>
            <dt>温馨提示：</dt>
            <dd>资金密码：提款、充值、还有积分兑换等都要求必须输入资金密码！</dd>
        </dl>
    </div>
		<dl class="wjform">
        <dt>原始密码：</dt>
        <dd><input type="password" name="oldpassword"  /></dd>
    </dl>
	<dl class="wjform">
        <dt>新密码：</dt>
        <dd><input type="password" name="newpassword"  /></dd>
    </dl>
    <dl class="wjform">
        <dt>确认密码：</dt>
        <dd><input type="password" class="confirm"  /></dd>
    </dl>
    <dl class="wjform">
        <dt>&nbsp;</dt>
        <dd><input type="button" id='put_button_pass' class="btn" value="设置密码"  onclick="$(this).closest('form').submit()"> 
    <input type="reset" value="重置" onClick="this.form.reset()"  class="btn"/></dd>
    </dl>
    </form>
	
<?php }else{?>
	<form action="/index.php/safe/setCoinPwd" method="post" target="ajax" onajax="safeBeforSetCoinPwd" call="safeSetPwd">
    <h3 class="mt10">设置资金密码</h3>
    <div class="tips">
        <dl>
            <dt>温馨提示：</dt>
            <dd>资金密码：提款、充值、还有积分兑换等都要求必须输入资金密码！</dd>
        </dl>
    </div>
	<dl class="wjform">
        <dt>密码：</dt>
        <dd><input type="password" name="newpassword"  /></dd>
    </dl>
    <dl class="wjform">
        <dt>确认密码：</dt>
        <dd><input type="password" class="confirm"  /></dd>
    </dl>
    <dl class="wjform">
        <dt>&nbsp;</dt>
        <dd><input type="button" id='put_button_pass' class="btn" value="设置密码"  onclick="$(this).closest('form').submit()"> 
    <input type="reset" value="重置" onClick="this.form.reset()"  class="btn"/></dd>
    </dl>
    </form>

<?php }?>
	
    </div>
<?php $this->display('inc_footer.php'); ?> 
</div>
<div class="pagebottom"></div>
</div>

</body>
</html>
  
   
 
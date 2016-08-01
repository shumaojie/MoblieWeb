<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" >
<head>
<?php $this->display('inc_skin.php', 0 , '会员中心－个人资料'); ?>
<style type="text/css">
		 .table_b_tr_b td input
        {
	        height:24px;
	        line-height:24px;
	        padding:2px;
	        border:1px #ddd solid
        }
        .table_b_tr_b td input:focus
        {
	        border:1px #e29898 solid;
	        background-color:#ffecec
        }
</style>
</head>
 
<body>
<div id="mainbody"> 
<?php $this->display('inc_header.php'); ?>
<div class="pagetop"></div>
<div class="pagemain">
	
	    <div class="display biao-cont">
    	<h3>个人基本信息</h3>
    	<dl class="wjform">
        	<dt>登陆账号：</dt>
            <dd><?=$this->user['username']?></dd>
        </dl>
        <dl class="wjform">
        	<dt>会员昵称：</dt>
            <dd><?=$this->user['nickname']?></dd>
        </dl>
        <dl class="wjform">
        	<dt>会员类型：</dt>
            <dd><?=$this->iff($this->user['type'], '代理', '会员')?></dd>
        </dl>
        <dl class="wjform">
        	<dt>上级代理：</dt>
            <dd><?=$this->iff($parent=$this->getValue("select username from {$this->prename}members where uid=?", $this->user['parentId']),$parent,'无')?></dd>
        </dl>
         <dl class="wjform">
        	<dt>返点：</dt>
            <dd><?=$this->user['fanDian']?>%</dd>
        </dl>
        <dl class="wjform">
        	<dt>可用资金：</dt>
            <dd><?=$this->user['coin']?> 元</dd>
        </dl>
        <dl class="wjform">
        	<dt>积分：</dt>
            <dd><?=$this->user['score']?></dd>
        </dl>
		<div class="clear"></div>
		
		
<?php if($this->user['coinPassword']){ ?>
<h3 class="mt10">个人银行信息</h3>
<form action="/index.php/safe/setCBAccount" method="post" target="ajax" onajax="safeBeforSetCBA" call="safeSetCBA" name="form1">
        <dl class="wjform">
        	<dt>银行类型：</dt>
            <dd>
		<?php
            $myBank=$this->getRow("select * from {$this->prename}member_bank where uid=?", $this->user['uid']);
            $banks=$this->getRows("select * from {$this->prename}bank_list where isDelete=0 and id!=12 order by sort");
            
            $flag=($myBank['editEnable']!=1)&&($myBank);
        ?>
        <select name="bankId" style="height:22px; width:140px;" <?=$this->iff($flag, 'disabled')?>>
        <?php foreach($banks as $bank){ ?>
            <option value="<?=$bank['id']?>" <?=$this->iff($myBank['bankId']==$bank['id'], 'selected')?>><?=$bank['name']?></option>
        <?php } ?>
        </select>
		</dd>
        </dl>
        <dl class="wjform">
        	<dt>银行账号：</dt>
            <dd><input type="text" name="account" value="<?=preg_replace('/^.*(\w{4})$/', '***\1', $myBank['account'])?>" <?=$this->iff($flag, 'readonly')?>/></dd>
        </dl>
        <dl class="wjform">
        	<dt>开户行：</dt>
            <dd><input type="text" name="countname" value="<?=preg_replace('/^(\w{4}).*(\w{4})$/','\1***\2',$myBank['countname'])?>"  <?=$this->iff($flag, 'readonly')?>/></dd>
        </dl>
        <dl class="wjform">
        	<dt>账户名：</dt>
            <dd><input type="text" name="username" value="<?=$this->iff($myBank['username'],mb_substr($myBank['username'],0,1,'utf-8').'**')?>" <?=$this->iff($flag, 'readonly')?> /></dd>
        </dl>
        <dl class="wjform">
        	<dt>资金密码：</dt>
            <dd><input type="password" name="coinPassword" <?=$this->iff($flag, 'readonly')?>/></dd>
        </dl>
        <dl class="wjform">
        	<dt>&nbsp;</dt>
            <dd><input type="submit" <?=$this->iff($flag, 'disabled')?> class="btn" value="设置银行" />
        <input type="reset" value="重置" onClick="this.form.reset()"  class="btn"/></dd>
        </dl>
        <div class="clear"></div>
        </form>
            
			
		<h3  class="mt10">个人银行信息</h3>
        <div class="tips">
        <dl>
            <dt>温馨提示：</dt>
            <dd>设置银行信息要用资金密码，您尚未设置资金密码！<a href="/index.php/safe/passwd" style="text-decoration:none; color:#f00">设置资金密码>></a></dd>
        </dl>
        </div>

<?php }?>
    </div>
	
	
        <div class="clear"></div>
<?php $this->display('inc_footer.php'); ?> 
</div>
<div class="pagebottom"></div>
</div>
</body>
</html>
  
   
 
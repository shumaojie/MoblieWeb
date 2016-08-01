<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 

if($this->type){
	$row=$this->getRow("select enable,title from {$this->prename}type where id={$this->type}");
	if(!$row['enable']){
		echo $row['title'].'已经关闭';
		exit;
	}
}else{
	$this->type=1;
	$this->groupId=2;
	$this->played=10;
}
?>
<?php $this->display('inc_skin.php',0,'首页'); ?>
<link href="/skin/main/skins.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/skin/main/game.js"></script>
<script type="text/javascript" src="/skin/js/jquery.simplemodal.src.js"></script>
</head> 
 
<body>
<div id="mainbody" type="<?=$this->type?>" ctype="<?=$types[$this->type]['type']?>" > 

<?php $this->display('index/inc_header.php'); ?>
<!--中奖公告-->
<div class="zjnotice">
	<marquee behavior="scroll" direction="left" height="30" style="line-height:30px;" loop="-1" scrollamount="2" scrolldelay="100" onMouseOut="this.start()" onMouseOver="this.stop()"><?php
                          $this->getSystemSettings();
                          $this->getTypes();
                          $types=array(1,3,5,6,9,10,12,14,15,16,20,7);
                          $name=explode('|',$this->settings['paihangsjnr']);
                          $name2=explode('|',$this->settings['paihangsjje']);
                          $gg=$this->getRows("select * from {$this->prename}bets where zjCount=1 and bonus>=? order by id desc limit 10",$this->settings['sbje']);
                          if($gg) foreach($gg as $var){
                          $gg=$this->getRows("select * from {$this->prename}bets where zjCount=1 and bonus>=? order by id desc limit 10",$this->settings['sbje']);
                          switch($var['type']){
                          case 1:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp重庆时时彩&nbsp ','<em>',$var['actionNo'],'</em>&nbsp期','&nbsp喜中&nbsp<em>',$var['bonus'],'</em>&nbsp元</span>
			             <span>恭喜&nbsp【<em>',$name[rand(0,count($name)-1)],'</em>】&nbsp',$this->types[$num=$types[rand(0,14)]]['title'],'&nbsp','<em>',$this->iff($sss=$this->getGameLastNo($num),$sss['actionNo'],'--'),'</em>&nbsp期','&nbsp喜中&nbsp<em>',$name2[rand(0,count($name2)-1)],'</em>&nbsp元</span>';
                          break;
                          case 3:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp江西时时彩&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 6:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp广东11选5&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 9:
						   echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp福彩3D&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 10:
						   echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp排列三&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 12:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp新疆时时彩&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 16:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp江西11选5&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
                          case 7:
                          echo '<span>恭喜&nbsp&nbsp【<em>',$var['nickname'],'</em>】&nbsp&nbsp山东11选5&nbsp&nbsp','<em>',$var['actionNo'],'</em>期','&nbsp喜中&nbsp<em>',$var['bonus'],'</b>&nbsp元</span>';
                          break;
	                          	}
                          	}
	                          ?>        
    </marquee>
</div>
<!--中奖公告结束-->
<div class="gamemain"> 
    <!-- 开奖信息 -->
    <?php $this->display('index/inc_data_current.php'); ?>
    <!-- 开奖信息 end -->
    <div class="game">
    <!--游戏body-->
    <?php $this->display('index/inc_game.php'); ?>
    <!--游戏body  end-->
    </div>
	<?php if($this->settings['switchDLBuy']==0 || ($this->settings['switchZDLBuy']==0 && ($this->user['parents']==$this->user['uid']))){ //代理和总代不能买单?>
    <input name="wjdl" type="hidden" value="<?=$this->ifs($this->user['type'],1)?>" id="wjdl" />
    <?php } ?>
    <?php $this->display('inc_footer.php'); ?>
</div>

</div> 
<script type="text/javascript">
var game={
	type:<?=json_encode($this->type)?>,
	played:<?=json_encode($this->played)?>,
	groupId:<?=json_encode($this->groupId)?>
},
user="<?=$this->user['username']?>",
aflag=<?=json_encode($this->user['admin']==1)?>;
</script>
</body>
</html>
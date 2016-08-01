<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $this->display('inc_skin.php',0,'开奖号码'); $args[0]?>
<link href="/skin/main/home.css" rel="stylesheet" type="text/css">
</head>
<body ondrag="return false;">
<div id="mainbody"> 
<?php $this->display('inc_header.php'); ?>
<div class="pagetop"></div>
<div class="pagemain">
       <div class="homelist" >
            <ul class="clist" style="text-align:center">
                <li><a href="/index.php/index/kjdata?t=1">重庆时时彩</a></li>  
                <li><a href="/index.php/index/kjdata?t=3">江西时时彩</a></li>
                <li><a href="/index.php/index/kjdata?t=12">新疆时时彩</a></li>                       
                <li><a href="/index.php/index/kjdata?t=6">广东11选5</a></li>
                <li><a href="/index.php/index/kjdata?t=7">山东11选5</a></li>
                <li><a href="/index.php/index/kjdata?t=16">江西多乐彩</a></li>
                <li><a href="/index.php/index/kjdata?t=9">福彩3D</a></li>
                <li><a href="/index.php/index/kjdata?t=10">排列3</a></li>
                <li><a href="/index.php/index/kjdata?t=20">北京PK10</a></li>
                <li style="display:none;"><a href="/index.php/index/kjdata?t=18">重庆幸运农场</a></li>
                <li><a href="/index.php/index/kjdata?t=14">五分彩</a></li>  
				<li><a href="/index.php/index/kjdata?t=26">两分彩</a></li>  
                <li><a href="/index.php/index/kjdata?t=5">分分彩</a></li>  
            </ul>
            <div class="clear"></div>
        </div>
  <div class="display biao-cont">
     <table class="table_b">
        <thead>
            <thead>
            <tr class="table_b_th">
                <td>彩种</td>
                <td>期号</td>
                <td>开奖号码</td>
                <td>时间</td>
            </tr>
            </thead>
            <tbody class="table_b_tr">
			<?php
			$sql="select type, time, number, data from lottery_data where type={$_GET['t']}";
			$sql=$sql." order by number desc  limit 50";
			$data=$this->getPage($sql, $this->page, $this->pageSize);
			$typename=$this->getValue("select title from lottery_type where id=?",$_GET['t']);
			   if($data['data']) foreach($data['data'] as $var){
			?>
              <tr>
			    <td><?=$typename ?></td>
			    <td><?=$var['number']?></td>
			    <td><?=$var['data']?></td>
			    <td><?=date('H:i', $var['time'])?></td>
              </tr>
			<?php } ?>
            
            </tbody>
        </table>
    </div>
 </div>
    <?php $this->display('inc_footer.php'); ?> 
	
    </div>
<div class="pagebottom"></div>
</body>
</html>
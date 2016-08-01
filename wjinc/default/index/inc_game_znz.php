<?php
	//$action=$this->getGameNo($this->type);
	$sql="select u.username, u.fanDian userFanDian, b.id, b.type, b.playedId, b.mode, b.beiShu, b.actionNo, b.actionNum, b.actionData, b.qz_uid, b.fanDian, b.bonusProp from {$this->prename}bets b, {$this->prename}members u where b.uid=u.uid and kjTime>{$this->time} and b.isDelete=0 and qzEnable=1";
	
	$this->getGameFanDian();
	
	$pageSize=20;
	if(!$page=$args[0]) $page=1;
	$data=$this->getPage($sql, $page, $pageSize);
	$modeName=array('2.00'=>'元', '0.20'=>'角', '0.02'=>'分');
?>
<div class="biao-top">
	<!--<p style="margin:5px;color:#fff;font-weight:bold;font-size:14px;"><?=$this->types[$this->type]['title']?>第<?=$action['actionNo']?>期庄内庄</p>-->
	<p style="margin:5px;color:#fff;font-weight:bold;font-size:14px; float:left">庄内庄</p><a onclick="sxznz()" style="float:right; display:block; cursor:pointer; color:#FFF; margin:5px; background:#099; border:#9FF solid 1px; border-bottom:#066 solid 1px; border-right:#066 solid 1px; line-height:18px; text-decoration:none; text-align:center; width:40px;">刷新</a>
</div>
<div class="biao-cont">
	<!--下注列表-->
	<table width="100%">
		<thead>
			<tr class="tr-top">
				<td>单号</td>
				<td>彩种</td>
				<td>期号</td>
				<td>用户名</td>
				<td>玩法</td>
				<td>号码</td>
				<td>状态</td>
				<td>注数</td>
				<td>金额</td>
				<td>奖-返</td>
				<td>模式</td>
				<td>倍数</td>
				<td>操作</td>
			</tr>
		</thead>
		<tbody id="znz-code-list">
		<?php if($data['data']) foreach($data['data'] as $var){
			$var['gameFanDian']=$this->gameFanDian;
			$var['fanDianMax']=$this->settings['fanDianMax'];
			$var['myFanDian']=$this->user['fanDian'];
			$var['setFanDian']=$var['fanDian'];
			$var['zjFun']=$this->playeds[$var['playedId']]['zjMax'];
		?>
			<tr class="tr-cont" data-code='<?=json_encode($var)?>'>
				<td><?=$var['id']?></td>
				<td><?=$this->ifs($this->types[$var['type']]['shortName'], $this->types[$var['type']]['shortName'])?></td>
				<td><?=$var['actionNo']?></td>
				<td><?=preg_replace('/^(\w).*(\w{3})$/', '\1***\2', $var['username'])?></td>
				<td><?=$this->playeds[$var['playedId']]['name']?></td>
				<td class="view-code">查看</td>
				<td><?=$this->iff($var['qz_uid'], '<font color="red">被抢</font>', '<font color="#33CC33">未抢</font>')?></td>
				<td><?=$var['actionNum']?></td>
				<td><?=number_format($var['actionNum'] * $var['mode'] * $var['beiShu'], 2)?></td>
				<td><?=$var['bonusProp'].'-'.$var['fanDian'].'%'?></td>
				<td><?=$modeName[$var['mode']]?></td>
				<td><?=$var['beiShu']?></td>
				<td>
					<?php if($var['qz_uid']){ ?>
					--
					<?php }else{ ?>
					<div class="qzbtn">抢庄</div>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<!--下注列表 end -->
</div>

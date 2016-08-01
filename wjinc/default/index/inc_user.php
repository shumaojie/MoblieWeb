<?php $this->freshSession();
		$ngrade=$this->getValue("select max(level) from {$this->prename}member_level where minScore <= {$this->user['scoreTotal']} limit 1");
		if($ngrade>$this->user['grade']){
			$sql="update lottery_members set grade={$ngrade} where uid=?";
			$this->update($sql, $this->user['uid']);
		}else{$ngrade=$this->user['grade'];}
		$date=strtotime('00:00:00');
?>
<span>用户：<em style="font-size:16px;"><?=$this->user['nickname']?></em></span>
<span class="ml10">余额：<strong style="font-size:16px;"><?=$this->user['coin']?><a href="#" onclick="reloadMemberInfo()"><img src="/images/common/ref.png" alt="刷新余额"></a></strong></span><span class="score"></span>
<span class="ml10"><a href="/index.php/cash/recharge">充值</a></span>
<span class="ml10"><a href="/index.php/cash/toCash">提款</a></span>
<span class="ml10"><a href="/index.php/user/logout">退出</a></span>
<span class="ml10"><a href="<?=$this->settings['kefuGG']?>" target="_blank">在线客服</a></span>
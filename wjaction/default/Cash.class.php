<?php
@session_start();
class Cash extends WebLoginBase{
	public $pageSize=20;
	private $vcodeSessionName='lottery_vcode_session_name';

	public final function toCash(){
		$this->display('cash/to-cash.php');
	}
	
	public final function toCashLog(){
		$this->display('cash/to-cash-log.php');
	}
	
	public final function toCashResult(){
		$this->display('cash/cash-result.php');
	}
	
	public final function recharge(){
		$this->display('cash/recharge.php');
	}
	
	public final function rechargeLog(){
		$this->display('cash/recharge-log.php');
	}
	
	/**
	 * 提现申请
	 */
	public final function ajaxToCash(){
		if(!$_POST) throw new Exception('参数出错');

		$para['amount']=$_POST['amount'];
		$para['coinpwd']=$_POST['coinpwd'];
		$bank=$this->getRow("select username,account,bankId from {$this->prename}member_bank where uid=? limit 1",$this->user['uid']);
		$para['username']=$bank['username'];
		$para['account']=$bank['account'];
		$para['bankId']=$bank['bankId'];
		if(!ctype_digit($para['amount'])) throw new Exception('提现金额包含非法字符');
		if($para['amount']<=0) throw new Exception("提现金额只能为正整数");
		if($para['amount']>$this->user['coin']) throw new Exception("提款金额大于可用余额，无法提款");
		if($this->user['coin']<=0) throw new Exception("可用余额为零，无法提款");
		
		//提示时间检查
		$baseTime=strtotime(date('Y-m-d ',$this->time).'06:00');
		$fromTime=strtotime(date('Y-m-d ',$this->time).$this->settings['cashFromTime'].':00');
		$toTime=strtotime(date('Y-m-d ',$this->time).$this->settings['cashToTime'].':00');
		if($toTime<$baseTime) $toTime.=24*3600;
		if($this->time < $fromTime || $this->time > $toTime ) throw new Exception("提现时间：从".$this->settings['cashFromTime']."到".$this->settings['cashToTime']);

		//消费判断
		$cashAmout=0;
		$rechargeAmount=0;
		$rechargeTime=strtotime('00:00');
		if($this->settings['cashMinAmount']){
			$cashMinAmount=$this->settings['cashMinAmount']/100;
			$gRs=$this->getRow("select sum(case when rechargeAmount>0 then rechargeAmount else amount end) as rechargeAmount from {$this->prename}member_recharge where  uid={$this->user['uid']} and state in (1,2,9) and isDelete=0 and rechargeTime>=".$rechargeTime);
			if($gRs){
				$rechargeAmount=$gRs["rechargeAmount"]*$cashMinAmount;
			}
			if($rechargeAmount){
				//消费总额
				$cashAmout=$this->getValue("select sum(mode*beiShu*actionNum) from {$this->prename}bets where actionTime>={$rechargeTime} and uid={$this->user['uid']} and isDelete=0");
				if(floatval($cashAmout)<floatval($rechargeAmount)) throw new Exception("消费满".$this->settings['cashMinAmount']."%才能提现");
			}
		}//消费判断结束
		$this->beginTransaction();
		try{
			$this->freshSession();
			if($this->user['coinPassword']!=md5($para['coinpwd'])) throw new Exception('资金密码不正确');
			unset($para['coinpwd']);
			
			if($this->user['coin']<$para['amount']) throw new Exception('你帐户资金不足');
		
			// 查询最大提现次数与已经提现次数
			$time=strtotime(date('Y-m-d', $this->time));
			if($times=$this->getValue("select count(*) from {$this->prename}member_cash where actionTime>=$time and uid=?", $this->user['uid'])){
				$cashTimes=$this->getValue("select maxToCashCount from {$this->prename}member_level where level=?", $this->user['grade']);
				if($times>=$cashTimes) throw new Exception('对不起，今天你提现次数已达到最大限额，请明天再来');
			}
			
			// 插入提现请求表
			$para['actionTime']=$this->time;
			$para['uid']=$this->user['uid'];
			if(!$this->insertRow($this->prename .'member_cash', $para)) throw new Exception('提交提现请求出错');
			$id=$this->lastInsertId();
			
			// 流动资金
			$this->addCoin(array(
				'coin'=>0-$para['amount'],
				'fcoin'=>$para['amount'],
				'uid'=>$para['uid'],
				'liqType'=>106,
				'info'=>"提现[$id]资金冻结",
				'extfield0'=>$id
			));

			$this->commit();
			  return '申请提现成功，提现将在10分钟内到帐，如未到账请联系在线客服。';
		}catch(Exception $e){
			$this->rollBack();
			//return 9999;
			throw $e;
		}
	}
	
	/**
	 * 确认提现到帐
	 */
	public final function toCashSure($id){
		if(!$id=intval($id)) throw new Exception('参数出错');
		
		$this->beginTransaction();
		try{
			
			// 查找提现请求信息
			if(!$cash=$this->getRow("select * from {$this->prename}member_cash where id=$id"))
			throw new Exception('参数出错');
			
			if($cash['uid']!=$this->user['uid']) throw new Exception('您不能代别人确认');
			switch($cash['state']){
				case 0:
					throw new Exception('提现已经确认过了');
				break;
				case 1:
					throw new Exception("提现请求正在处理中...");
				break;
				case 2:
					throw new Exception("该提现请求已经取消，冻结资金已经解除冻结\r\n如需要提现请重新申请");
				break;
				case 3:
					
				break;
				case 4:
					throw new Exception("该提现请求已经失败，冻结资金已经解除冻结\r\n如需要提现请重新申请");
				break;
				default:
					throw new Exception('系统出错');
				break;
			}
			
			if($this->update("update {$this->prename}member_cash set state=0 where id=$id"))
			$this->addCoin(array(
				'liqType'=>12,
				'uid'=>$this->user['uid'],
				'info'=>"提现[$id]资金确认",
				'extfield0'=>$id
			));
			
		}catch(Exception $e){
			$this->rollBack();
			throw $e;
		}
	}
	
	/* 进入充值，生产充值订单 */
	public final function inRecharge(){

		if(!$_POST) throw new Exception('参数出错');
		$para['mBankId']=intval($_POST['mBankId']);
		$para['amount']=floatval($_POST['amount']);

		if($para['amount']<=0) throw new Exception('充值金额错误，请重新操作');
		if($id=$this->getRow("select id from {$this->prename}bank_list where id=?",$para['mBankId'])){
			if($id['id']==2 || $id['id']==1){
				if($para['amount']<$this->settings['rechargeMin1'] || $para['amount']>$this->settings['rechargeMax1']) throw new Exception('支付宝/财付通充值最低'.$this->settings['rechargeMin1'].'元，最高'.$this->settings['rechargeMax1'].'元');
			}else{
				if($para['amount']<$this->settings['rechargeMin'] || $para['amount']>$this->settings['rechargeMax']) throw new Exception('银行卡充值最低'.$this->settings['rechargeMin1'].'元，最高'.$this->settings['rechargeMax1'].'元');
			}
		}else{
				throw new Exception('充值银行不存在，请重新选择');
			}

		if(strtolower($_POST['vcode'])!=$_SESSION[$this->vcodeSessionName]){
			throw new Exception('验证码不正确。');
		}else{
			// 插入提现请求表
			unset($para['coinpwd']);
			$para['rechargeId']=$this->getRechId();
			$para['actionTime']=$this->time;
			$para['uid']=$this->user['uid'];
			$para['username']=$this->user['username'];
			$para['actionIP']=$this->ip(true);
			$mBankId=$para['mBankId'];
			$para['info']='用户充值';
			
			if($this->insertRow($this->prename .'member_recharge', $para)){
				$this->display('cash/recharge-copy.php',0,$para);
			}else{
				throw new Exception('充值订单生产请求出错');
			}
		}
		//清空验证码session
	    unset($_SESSION[$this->vcodeSessionName]);
	}
	
	public final function getRechId(){
		$rechargeId=mt_rand(100000,999999);
		if($this->getRow("select id from {$this->prename}member_recharge where rechargeId=$rechargeId")){
			getRechId();
		}else{
			return $rechargeId;
		}
	}
	
	//充值提现详细信息弹出
	public final function rechargeModal($id){
		$this->getTypes();
		$this->getPlayeds();
		$this->display('cash/recharge-modal.php', 0 , $id);
	}
	public final function cashModal($id){
		$this->getTypes();
		$this->getPlayeds();
		$this->display('cash/cash-modal.php', 0 , $id);
	}
	
	//充值演示
	public final function paydemo($id){
		$this->display('cash/paydemo.php', 0 , $id);
	}

	// 获取支付实例*******************************************************************************************************************
	private function get_pay_instance() {
		static $instance;
		if (!$instance) {
			require('/pay.config.php');
			$pay_file = require('/pay/'.PAY_TYPE.'.pay.php');
			$pay_class = 'pay_'.PAY_TYPE;
			$instance = new $pay_class;
		}
		return $instance;
	}

	// 支付方法
	public final function pay() {

		//$this->user_check_func();
		//$this->check_post();

		if(!$_POST) throw new Exception('参数出错');
		$bankid = $para['mBankId']=intval($_POST['mBankId']);
		//$amount = $para['amount']=floatval($_POST['amount']);
		$amount = $para['amount']=$_POST['amount'];
		//$amount = number_format($amount, 2, '.', '');
		//echo $bankid;
		if($amount <= 0) throw new Exception('充值金额错误，请重新操作');
		//$bank = $this->db->query("SELECT `id` FROM `{$this->db_prefix}bank_list` WHERE `id`={$bankid} LIMIT 1", 2);
		$bank = $this->getRow("select id from lottery_bank_list where id={$bankid} limit 1");
		//print_r($bank);
		if (!$bank) throw new Exception('充值银行不存在，请重新选择');

		if($amount<$this->settings['rechargeMin'] || $amount>$this->settings['rechargeMax']) throw new Exception('银行卡充值最低'.$this->settings['rechargeMin1'].'元，最高'.$this->settings['rechargeMax1'].'元');

		if(strtolower($_POST['vcode'])!=$_SESSION[$this->vcodeSessionName]){
			throw new Exception('验证码不正确。');
		}else{
			// 插入提现请求表
			unset($para['coinpwd']);
			$orderid = $para['rechargeId']=$this->getRechId();
			$para['actionTime']=$this->time;
			$para['uid']=$this->user['uid'];
			$para['username']=$this->user['username'];
			$para['actionIP']=$this->ip(true);
			$mBankId=$para['mBankId'];
			$para['info']='用户充值';

			if($this->insertRow($this->prename .'member_recharge', $para)){
				if ($bankid === '1' || $bankid === '2') {
					$this->pay_online($bankid, $amount, $orderid);
				} else {
					// 生成回调地址及返回地址
					$url_callback = 'http://'.$_SERVER['SERVER_NAME'].'/cash/pay_callback';
					$url_return = 'http://'.$_SERVER['SERVER_NAME'].'/cash/pay_recharge';
					// 获取支付实例
					$instance = $this->get_pay_instance();
					$instance->user = $this->user;
					$instance->pay($bankid, $amount, $orderid, $url_callback, $url_return);
				}
			}else{
				throw new Exception('充值订单生产请求出错');
			}
		}
		//清空验证码session
		unset($_SESSION[$this->vcodeSessionName]);

	}
	public function pay_callback() {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL | E_STRICT);
		//$instance = $this->get_pay_instance();
		//$instance->callback();
		$data = array(
			'orderID'       => trim($_POST['orderID']),
			'resultCode'    => trim($_POST['resultCode']),
			'stateCode'     => trim($_POST['stateCode']),
			'orderAmount'   => trim($_POST['orderAmount']),
			'payAmount'     => trim($_POST['payAmount']),
			'acquiringTime' => trim($_POST['acquiringTime']),
			'completeTime'  => trim($_POST['acquiringTime']),
			'partnerID'     => trim($_POST['partnerID']),
			'remark'        => trim($_POST['remark']),
			'charset'       => trim($_POST['charset']),
			'signType'      => trim($_POST['signType']),
			'signMsg'      => trim($_POST['signMsg']),
		);

		//订单号为必须接收的参数，若没有该参数，则返回错误
		if(empty($data['orderid'])) {
			die("opstate=-1");		//签名不正确，则按照协议返回数据
		}

		$sign_text	= '';
		foreach ($data as $key => $value) {
			if ($key == 'signMsg') {
				continue;
			}

			if ($sign_text != '') {
				$sign_text .= '&';
			}

			$sign_text .= $key.'='.$value;
		}

		$sign_md5 = md5($sign_text);
		if($sign_md5 != $data['signMsg']) {
			die("opstate=-2");		//签名不正确，则按照协议返回数据
		} else {
			if ($data['stateCode'] == 2) {
				//require_once(SYSTEM."/core/pay.core.php");
				//$pay = new pay();
				//$pay->call($payamount, $orderid);
				//$sql = "UPDATE `{$this->db_prefix}member_recharge` SET `state`=1,`rechargeAmount`=$new_amount,`coin`=$old_coin WHERE `rechargeId`='$order_no' LIMIT 1";
				//$this->db->query($sql, 0);
				$this->update("update {$this->prename}member_recharge set state=1 where rechargeId={$data['orderid']}")
				echo "success";
			} else if ($data['stateCode'] == 0 || $data['stateCode'] == 1) {
				echo "处理中！";
			} else {
				echo "签名错误!";
			}
		}
		die("opstate=0");
	}

	public function pay_recharge() {
		// $this->user_check_func();
		// $this->check_coinPassword();
		if ($this->post) {
			$this->get_time();
			$page_current = $this->get_page();
			$search_log = $this->recharge_search_func($page_current);
			$page_max = $this->get_page_max($search_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->page_args();
			$container = '#recharge-log .body';
			if ($this->ispage) {
				$this->display('/user/recharge_body', array(
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/recharge?'.http_build_query($page_args),
					'page_container' => $container,
				));
			} else {
				$banks = $this->db->query("SELECT * FROM `{$this->db_prefix}bank_list` WHERE `isDelete`=0 ORDER BY `sort` DESC", 3);
				$admin_banks_temp = $this->db->query("SELECT * FROM `{$this->db_prefix}admin_bank`", 3);
				$admin_banks_data = array();
				foreach ($admin_banks_temp as $v) $admin_banks_data[$v['bankid']] = $v;
				foreach ($banks as $k => $bank) {
					if (
						array_key_exists($bank['id'], $admin_banks_data) &&
						(!$admin_banks_data[$bank['id']]['enable'] || empty($admin_banks_data[$bank['id']]['account']))
					) unset($banks[$k]);
				}
				$bank_default = reset($banks);
				$this->display('/user/recharge', array(
					'bank_default' => $bank_default,
					'banks' => $banks,
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/recharge?'.http_build_query($page_args),
					'page_container' => $container,
				));
			}
		} else {
			$this->ajax();
		}
	}

	// 在线支付支付方法
	private function pay_online($bankid, $amount, $orderid) {
		$bank = $this->db->query("SELECT * FROM `{$this->db_prefix}admin_bank` WHERE `bankid`={$bankid} LIMIT 1", 2);
		if (!$bank) core::error('系统错误');
		if (!$bank['enable']) core::error('该充值方式已停用');
		if ($bankid === '1') { // 支付宝
			$html  = '<form name="alipaypay" method="post" action="https://shenghuo.alipay.com/send/payment/fill.htm">';
			$html .= '<input type="hidden" name="optEmail" value="'.$bank['account'].'">';
			$html .= '<input type="hidden" name="payAmount" value="'.$amount.'">';
			$html .= '<input type="hidden" name="title" value="'.$this->user['username'].'">';
			$html .= '<input type="hidden" name="memo" value="'.$orderid.'">';
			$html .= '<input type="hidden" name="isSend" value="">';
			$html .= '<input type="hidden" name="smsNo" value="">';
			$html .= '</form>';
			$html .= '<script type="text/javascript">document.alipaypay.submit();</script>';
			echo $html;
		} else { // 财付通
			$html  = '<div class="detail"><table cellpadding="0" cellspacing="0" width="100%">';
			$html .= '<tr>';
			$html .= '<td>充值单号</td>';
			$html .= '<td>'.$orderid.'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>充值金额</td>';
			$html .= '<td>'.$amount.' 元</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<tr>';
			$html .= '<td>收款账号</td>';
			$html .= '<td>'.$bank['account'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>收款人姓名</td>';
			$html .= '<td>'.$bank['username'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td colspan="2"><a class="icon-link-ext" style="color:#35928f" href="https://www.tenpay.com/v2/account/pay/index.shtml" target="_blank">前往充值</a></td>';
			$html .= '</tr>';
			$html .= '</table></div>';
			$this->dialogue(array(
				'class' => 'mid',
				'body' => $html,
				'yes'  => array('text' => '确定'),
			));
		}
	}
}
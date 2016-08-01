<?php
class Pay extends WebBase{
	
	//充值演示
	public final function paydemo($id){
		$this->display('cash/paydemo.php', 0 , $id);
	}
    // 获取支付实例
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
        echo $bankid;
		if($amount <= 0) throw new Exception('充值金额错误，请重新操作');
		//$bank = $this->db->query("SELECT `id` FROM `{$this->db_prefix}bank_list` WHERE `id`={$bankid} LIMIT 1", 2);
		$bank = $this->getRow("select id from lottery_bank_list where id={$bankid} limit 1");
        print_r($bank);
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
					$url_callback = 'http://'.$_SERVER['SERVER_NAME'].'/pay/pay_callback';
					$url_return = 'http://'.$_SERVER['SERVER_NAME'].'/pay/recharge';
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
        $instance = $this->get_pay_instance();
        $instance->callback();
    }

    public function recharge() {
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
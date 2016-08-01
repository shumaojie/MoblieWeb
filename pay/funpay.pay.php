<?php 
// [乐盈支付]支付接口
class pay_funpay {

	// 银行代码，借记卡(普通银行卡)
	private $banks = array(
		'3'  => 'gdb',    // 广东发展银行
		'4'  => 'hxb',    // 华夏银行
		'5'  => 'comm',   // 交通银行
		'6'  => 'pingan', // 平安银行
		'7'  => 'spdb',   // 上海浦东发展银行
		'8'  => 'cib',    // 兴业银行
		'9'  => 'post',   // 中国邮政储蓄银行
		'10' => 'ceb',    // 中国光大银行
		'11' => 'icbc',   // 中国工商银行
		'12' => 'ccb',    // 中国建设银行
		'13' => 'cmbc',   // 中国民生银行
		'14' => 'abc',    // 中国农业银行
		'15' => 'boc',    // 中国银行
		'16' => 'cmb',    // 招商银行
		'17' => 'ecitic', // 中信银行
		'18' => 'bccb',   // 北京银行
		'23' => 'wx',     // 微信支付
		'24' => 'nb',     // 宁波银行
		'25' => 'bea',    // 东亚银行
		'26' => 'sdb'     // 深发展银行
	);

	private $payversion = '1.0'; //接口版本号

	/**
	 * @name 支付方法
	 * @param int bankid 银行ID
	 * @param int amount 充值金额
	 * @param string orderid 订单ID
	 * @param string url_callback 回调地址
	 * @param string url_return 充值完成后返回地址
	 * @param boolean $is_c 是否是信用卡支付，默认普通银行卡
	 */
	public function pay($bankid, $amount, $orderid, $url_callback, $url_return) {

		//$amount = $amount * 100;

		$data = array(
			'version'         => $this->payversion,
			'serialID'        => $this->getserialid(),
			'submitTime'      => date('Ymdhms'),
			'failureTime'     => '',
			'customerIP'      => ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"],
			'orderDetails'    => $orderid.','.$amount.',,充值,1',
			'totalAmount'     => $amount,
			'type'            => 1000,
			'buyerMarked'     => '',
			'payType'         => 'ALL',
			'orgCode'         => $this->banks[$bankid],
			'currencyCode'    => 1,
			'directFlag'      => 0,
			'borrowingMarked' => 0,
			'couponFlag'      => 1,
			'platformID'      => '',
			'returnUrl'       => $url_return,
			'noticeUrl'       => $url_callback,
			'partnerID'       => PAY_ID,
			'remark'          => 'xyz',
			'charset'         => 1,
			'signType'        => 2,
			'pkey'            => PAY_SECRET
			);

		$url = '';
		foreach ($data as $key => $value) {
			if ($url != '') {
				$url .= '&';
			}
			$url .= $key.'='.$value;
		}

		$bank_url        = "https://www.funpay.com/website/pay.htm";
		$signMsg         = md5($url);
		$data['signMsg'] = $signMsg;
		//print_r($data);
		$html  = '<!DOCTYPE HTML>';
		$html .= '<html>';
		$html .= '<head>';
		$html .= '<meta charset="utf-8">';
		$html .= '<title>乐盈支付收银台</title>';
		$html .= '</head>';
		$html .= '<body onload="document.form1.submit()">';
		//$html .= '<body >';
		$html .= '<form method="post" name="form1" action="'.$bank_url.'">';

		foreach ($data as $key => $value) {
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
		}

		$html .= '<input type="submit" style="display:none;"/>';
		$html .= '</form>';
		$html .= '</body>';
		$html .= '</html>';

		echo $html;
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
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
				require_once(SYSTEM."/core/pay.core.php");
				$pay = new pay();
				$pay->call($payamount, $orderid);
				echo "success";
			} else if ($data['stateCode'] == 0 || $data['stateCode'] == 1) {
				echo "处理中！";
			} else {
				echo "签名错误!";
			}
		}
		die("opstate=0");
	}

	private function getserialid() {
		return 'Fighter'.date('Ymdhms');
	}
}
?>
<?php
// [盈宝]支付接口
class pay_eypal {

	// 银行代码
	private $banks = array(
		'3'  => 'CGB', // 广东发展银行
		'4'  => 'HXB',  // 华夏银行
		'5'  => 'BOCM', // 交通银行
		'6'  => 'SDB', // 平安银行
		'7'  => 'SPDB', // 上海浦东发展银行
		'8'  => 'CIB', // 兴业银行
		'9'  => 'PSBC', // 中国邮政储蓄银行
		'10' => 'CEB', // 中国光大银行
		'11' => 'ICBC', // 中国工商银行
		'12' => 'CCB', // 中国建设银行
		'13' => 'CMBC', // 中国民生银行
		'14' => 'ABC', // 中国农业银行
		'15' => 'BOC', // 中国银行
		'16' => 'CMB', // 招商银行
		'17' => 'CTITC', // 中信银行
	);
	
	/**
	 * @name 支付方法
	 * @param int bankid 银行ID
	 * @param int amount 充值金额
	 * @param string orderid 订单ID
	 * @param string url_callback 回调地址
	 * @param string url_return 充值完成后返回地址
	 */
	public function pay($bankid, $amount, $orderid, $url_callback, $url_return) {
		//接口版本号
		$version = '1.0';
		// 支付类型
		$paytype = $this->banks[$bankid];
		// 组装跳转网址
		$bank_url = "https://gateway.eypal.com/Eypal/Gateway"; //网银支付接口URL
		$partner  = PAY_PARTNER; //商户id
		$key      = PAY_TOKENKEY;
		$url      = "version=".$version;
		$url      .= "&partner=". $partner;
		$url      .= "&orderid=". $orderid;
		$url      .= "&payamount=". $amount;
		$url      .= "&payip=";
		$url      .= "&notifyurl=". $url_callback;
		$url      .= "&returnurl=". $url_return;
		$url      .= "&paytype=". $paytype;
		$url      .= "&remark=". $remark;
		
		$sign	  = md5($url. $key); // 签名
		$url	  = $bank_url . "?" . $url . "&sign=" .$sign; // 最终url
		//页面跳转
		header("location:" .$url);
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
		$version = trim($_REQUEST['version']);
		$rpartner = trim($_REQUEST['partner']);
		$orderid = trim($_REQUEST['orderid']);
		$payamount = trim($_REQUEST['payamount']);
		$opstate = trim($_REQUEST['opstate']);
		$orderno = trim($_REQUEST['orderno']);
		$eypaltime = trim($_REQUEST['eypaltime']);
		$message = trim($_REQUEST['message']);
		$paytype = trim($_REQUEST['paytype']);
		$remark = trim($_REQUEST['remark']);
		$sign = trim($_REQUEST['sign']);
		
		//订单号为必须接收的参数，若没有该参数，则返回错误
		if(empty($orderid)) {
			die('订单不能为空');		//签名不正确，则按照协议返回数据
		}
		
		$signText = "version=".$version."&partner=".$rpartner."&orderid=".$orderid."&payamount=".$payamount."&opstate=".$opstate."&orderno=".$orderno."&eypaltime=".$eypaltime."&message=".$message."&paytype=".$paytype."&remark=".$remark."&key=".PAY_TOKENKEY;
		$signValue = strtolower(md5($signText));
		if($signValue != $sign){
			die('验签失败');		//签名不正确，则按照协议返回数据
		} else {
			if ($opstate==2) {
				require_once(SYSTEM."/core/pay.core.php");
				$pay = new pay();
				$pay->call($payamount, $orderid);
			} else {
				die('处理中');
			}
		}
		echo 'success';
	}

}
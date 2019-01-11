<?php

//error_reporting(E_ALL);

//require_once('lib/api.conf.php');



require_once('lib/api.conf.php');

require_once('lib/common_functions.php');

require_once('lib/OrderCore.php');

	$st 		= "yes";

	$itemno = $_GET['itemno'];

	// var_dump($itemno);

	$result = OrderCore::getOrder($itemno);

	if($result['code']!=200)

	{

		// 501 网络错误

		// 2001 订单号丢失

		// 2002 订单号不存在

		// 2003 支付方式不存在，需要买家重新选择

		// 3001,3002 支付公司未对接

		// 406 云平台网关接口 IP 限制

		// 其他Code 错误 参考： http://api-test.ymcart.com/

		// 当支付方式不匹配时候，跳转获取重新选择支付

		if($result['code']==2003)

		{

			Common::redirect('https://' . REDIRECT_DOMAIN . '/h-order-step2.html?itemno=' . $itemno );

		}

		echo 'Code Error:' . $result['code'];

		die();

	}

	


    //var_dump($result);die();
	$uri 		= PRODUCT_URL;

	$merchantId = MERCHANT_ID;

	$ckey 		= MERCHANT_ckey;//商户KEY









	// 参数数组

	session_start();

	$sId = session_id();



	$notifySite  = $_SERVER['HTTP_HOST'];

	$webSite	 = API_DOMAIN;



	$amount   = $result['data']['amount'];

	$currency = $result['data']['currency'];

	$remark	  = $result['data']['remark'];

	//$shipping_method 	= $result['data']['logistic_com_name'];
	//$shipping_method 	= $result['data']['logistic_com_no'];//无法获取到页面参数，因此注释掉

	$cost_shipping 		= $result['data']['post_fee'];

	$payment_method 	= 'yjf';



	$item = $result['data']['itemlist'];

	$ipAddress = get_real_ip();





$data = array (

		//公共参数

		  'version' 	=> '1.0',

		  'protocol' 	=> 'httpPost',

		  'service'	 	=> 'cardAcquiringCashierPay',

		  'orderNo' 	=> 'zh'.date('YmdHis').rand(0,888),

		  // 'merchOrderNo'=>date('YmdHis').rand(100,999), //新增参数，商户外部订单号

		  'merchOrderNo'=> $itemno, //新增参数，商户外部订单号

		  'partnerId' 	=> $merchantId,

		  'signType' 	=> 'MD5',

		  // 'notifyUrl'	=> 'http://192.168.16.104/demo/notify_url.php',

		  // 'returnUrl'	=> 'http://192.168.16.104/demo/return_url.php',



		  'notifyUrl'	=> 'https://' . $notifySite .'/yjf-pro/yjf_notify.php',

		  'returnUrl'	=> 'https://' . $notifySite . '/yjf-pro/yjf_return.php',



		  // 'notifyUrl'	=> 'http://' . $webSite .'/pay-api/yjf_notify.php',

		  // 'returnUrl'	=> 'http://' . $webSite . '/pay-api/yjf_return.php',



		//业务参数			

			'acquiringType' 		=> 'CRDIT',//收单类型,CRDIT：信用卡；YANDEX： 网银方式

			'goodsInfoList' 		=> array(), //商品列表

			'orderDetail' 			=> array(),//订单扩展信息

			'userId' 				=> $merchantId,	//

			'currency' 				=> $currency,//原始订单币种

			'orderAmount' 				=> $amount,

			'webSite' 				=> $webSite,//所属网站

			'deviceFingerprintId' 	=> $sId, //设备指纹

			'memo' 					=> '',//备注

			'orderExtends' 			=> '',//系统扩展字段，json存储

	);



for ($i=0; $i < count($item); $i++) { 

	$goodsList[$i] = array (

		'goodsNumber' 			=> $item[$i]['codeno'] ? $item[$i]['codeno']:$item[$i]['gcodeno'],	//货号

		'goodsName' 			=> $item[$i]['goods_name'],	//货物名称

		'goodsCount' 			=> $item[$i]['nums'],	//货物数量

		'itemSharpProductcode' 	=> $item[$i]['gcodeno'],	//商品分类

		'itemSharpUnitPrice' 	=> $item[$i]['price']	//商品单价

	);

}



//账单、收货等其它信息

$orderDetail = array (

	// 'ipAddress' 		=> '113.204.226.234',	//IP地址

	'ipAddress' 		=> $ipAddress,	//IP地址

	'billtoCountry'	 	=> $result['data']['country_code_2'],	//账单国家

	'billtoState' 		=> $result['data']['receiver_state'],	//账单州

	'billtoCity' 		=> $result['data']['receiver_city'],	//账单城市

	'billtoPostalcode' 	=> $result['data']['receiver_zip'],	//账单邮编

	'billtoEmail' 		=> $result['data']['receiver_email'],	//账单邮箱

	'billtoFirstname' 	=> $result['data']['receiver_firstname'],	//接收账单人员名

	'billtoLastname' 	=> $result['data']['receiver_lastname'],	//接收账单人员姓

	'billtoPhonenumber' => $result['data']['receiver_phone'],	//账单电话

	'billtoStreet' 		=> $result['data']['receiver_address'],	//账单街道



	'shiptoCity' 		=> $result['data']['receiver_city'],	//收货城市

	'shiptoCountry' 	=> $result['data']['country_code_2'],	//收货国家

	'shiptoFirstname' 	=> $result['data']['receiver_firstname'],	//收货人姓

	'shiptoLastname' 	=> $result['data']['receiver_lastname'],	//收货人名

	'shiptoEmail'		=> $result['data']['receiver_email'],	//收货邮箱

	'shiptoPhonenumber' => $result['data']['receiver_phone'],	//收货电话

	'shiptoPostalcode'	=> $result['data']['receiver_zip'],	//收货邮编

	'shiptoState' 		=> $result['data']['receiver_state'],	//收货州

	'shiptoStreet' 		=> $result['data']['receiver_address'],	//收货街道



	'logisticsFee' 		=> $cost_shipping,	//物流费

	'logisticsMode' 	=> 'Waiting for coverage',	//物流方式，无法获取到参数，因此填固定值，运单审核时会覆盖掉


	// 'cardType' 			=> $payment_method,	//卡类型

	'customerEmail' 	=> $result['data']['receiver_email'],	//购买者邮箱



	'customerPhonenumber' => $result['data']['receiver_phone'],	//购买者电话

	// 'merchantEmail' 	=> 'merchent@yiji.com',	//商户邮箱

	// 'merchantName' 		=> '测试公司',	//商户名

	// 'addressLine1' 		=> '',	//卡地址1

	// 'addressLine2' => ''	//卡地址2

);

// var_dump($orderDetail['ipAddress']);

$data['goodsInfoOrders']	 = json_encode($goodsList);

$data['attachDetails']	 = json_encode($orderDetail);

//$data['orderExtends']	 = json_encode($orderExtends,JSON_UNESCAPED_UNICODE);



//按参数名排序

//var_dump($data);die();

ksort($data);

// $signSrc = yjfpay_signature($data);

// $data['sign'] = $signSrc;



$signSrc="";

foreach($data as $k=>$v)

{

    if(empty($v)||$v==="")

        unset($data[$k]);

    else

        $signSrc.= $k.'='.$v.'&';

}



$signSrc = trim($signSrc, '&').$ckey;



if($data['signType']==="MD5")

    $data['sign'] = md5($signSrc);



	$html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body onLoad="document.dinpayForm.submit();"><form name="dinpayForm" id="dinpayForm" method="POST" action="'.$uri.'" >';

	foreach($data as $k => $v){

		$html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';

	}

	// $html .='<input type="submit" value="提交"/></form></body></html>';

	$html .='</form></body></html>';

	echo $html;

exit();

?>


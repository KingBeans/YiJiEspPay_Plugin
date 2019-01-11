<?php 

# require logic
require_once(ROOT_PATH . 'includes/lib_payment.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_clips.php');


require_once(ROOT_PATH . 'yjpay/inc/esp_api.php');		

# load lang files
$lang = dirname(__FILE__) . '/langs/' . $GLOBALS['_CFG']['lang'] . '/esp.php';
if (is_file($lang)) {
    include_once($lang);
}


#  check admin private
admin_priv('order_edit');

$act = empty($_REQUEST['act']) ?  'query' : trim($_REQUEST['act']);
// file_put_contents("E:/log/authorize/start.log", $act);
if ($act == 'query') {
	reload_page();
}

if ($act == 'refund') {
    # get yjpay order 
	$order = isset($_REQUEST['order_sn']) ? get_yjpay_order($_REQUEST['order_sn']) : false;
	
	if (!$order || $order['pay_code'] != 'yjpay') {
		sys_msg($_LANG['yjpay_errors']['order_empty']);
	}
	
	$payment = unserialize_config($order['pay_config']);

	// file_put_contents("E:/log/authorize/order.log", json_encode($order,true),FILE_APPEND);
	// file_put_contents("E:/log/authorize/payment.log","PAMENT:".json_encode($payment,true),FILE_APPEND );
	// exit();
	$esp_api = new esp_api (
		$payment['yjpay_cfg_partner_id'],
		$payment['yjpay_cfg_secret_key'],
		$payment['yjpay_cfg_debug']
	);
	
	// $info = $esp_api->order_query($order['order_sn']);

	# if exists order and amount loc > usableRefundMoney
	// if ($info && $info->orderStatus == 'PAY_SUCCESS' && ($info->amountLoc > $info->usableRefundMoney)) {
	if ($order['pay_status'] == 2) {
		$smarty->assign('pay_info',array(
			'userId'	=>	$payment['yjpay_cfg_partner_id'],
			'tradeNo'	=>	$order['order_sn'],
			'amountLoc' 	=> $order['order_amount'],//tax fee
			'tradeTime'		=> date('Y-m-d H:i:s',$order['pay_time']),
			'allowRefundMoney'	=> $order['order_amount'],

			// 'userId' 		=> $info->userId,
			// 'tradeNo' 		=> $info->tradeNo,
			// 'amountLoc' 	=> $info->amountLoc,
			// 'currencyCode'	=> $info->currencyCode,
			// 'charge'		=> $info->charge,
			// 'cardType'		=> $info->cardType,
			// 'tradeTime'		=> $info->tradeTime,
			// 'buyerEmail'	=> $info->buyerEmail,
			// 'orderStatus'	=> $info->orderStatus,
			// 'operationFlag' => $info->operationFlag,
			
			// 'usableRefundMoney' => $info->usableRefundMoney,
			// 'allowRefundMoney'	=> $info->amountLoc - $info->usableRefundMoney
		));
		

		if ($order['pay_time'] + 86400 > strtotime(date('Y-m-d H:i:s',time())) ){
			$smarty->assign('cancel_pay',true);
		} else {
			$smarty->assign('cancel_pay',false);
		}
				
		$smarty->assign('ur_here', $GLOBALS['_LANG']['yjpay_refund']['page_title']);
		$smarty->assign('page',$GLOBALS['_LANG']['yjpay_refund']);
		$smarty->assign('order',$order);
				
		$smarty->display('yjpay_refund.htm');
	} else {
		sys_msg($_LANG['yjpay_refund']['empty']);
	}
}

if ($act == 'doRefund') {
	$order = isset($_REQUEST['order_sn']) ? get_yjpay_order($_REQUEST['order_sn']) : false;
	
	if ($order['pay_code'] != 'yjpay' && $order['pay_status'] != PS_PAYED) {
		sys_msg($_LANG['yjpay_errors']['order_empty']);
	}

	$payment = unserialize_config($order['pay_config']);
	$esp_api = new esp_api (
		$payment['yjpay_cfg_partner_id'],
		$payment['yjpay_cfg_secret_key'],
		$payment['yjpay_cfg_debug']
	);
	
	$refundMoney = $_REQUEST['refund_money'];
	$refundNote  = $_REQUEST['note'];
	 
	if (!empty($_POST['cancel_button'])) {
		$msg = $esp_api->order_cancel($order['order_sn'],$refundNote);
	} else {
		$msg = $esp_api->order_refund($order['order_sn'],$refundMoney,$refundNote);
	}
		// echo $msg;
	if ($msg->status == 'success') {
	// if (true) {
		order_action($order['order_sn'], $order['order_status'], 
			$order['shipping_status'], $order['pay_status'], 
			sprintf($GLOBALS['_LANG']['yjpay_refund_success'],$refundMoney,$refundNote)
		);
		
		# if click order cancel button
		if (!empty($_POST['cancel_button'])) {
			yjpay_order_status($order['order_id'],OS_CANCELED);
		}
		
		ecs_header("Location: order.php?act=info&order_id=" . $order['order_id'] . "\n");
        exit;
	} else {
		sys_msg(sprintf($_LANG['yjpay_refund_fail'],$msg->description));
	}
}

if ($act == 'authorize') {
	$order = isset($_REQUEST['order_sn']) ? get_yjpay_order($_REQUEST['order_sn']) : false;
	
	if ($order['pay_code'] != 'yjpay' && $order['pay_status'] != PS_PAYED) {
		sys_msg($_LANG['yjpay_errors']['order_empty']);
	}
	
	$payment = unserialize_config($order['pay_config']);
	$esp_api = new esp_api (
		$payment['yjpay_cfg_partner_id'],
		$payment['yjpay_cfg_secret_key'],
		$payment['yjpay_cfg_debug']
	);
	
	// $info = $esp_api->order_query($order['order_sn']);
	# if exists order and order wait authorize
	// if ($info && $info->orderStatus == 'AUTHORIZ_APPLYING') {
	// if ($order['pay_status'] == '1') {
	if ($order['pay_status'] == 2) {

		$smarty->assign('pay_info',array(
			'userId'	=>	$payment['yjpay_cfg_partner_id'],
			'tradeNo'	=>	$order['order_sn'],

			'amountLoc' 	=> $order['order_amount'],//tax fee
			// 'currencyCode'	=> 2,
			// 'charge'		=> 3,
			// 'cardType'		=> 4,
			'tradeTime'		=> date('Y-m-d',$order['pay_time']),
			// 'buyerEmail'	=> 6,
			// 'orderStatus'	=> 7,
			// 'operationFlag' => 8,s
			
			// 'usableRefundMoney' => 9,
			'allowRefundMoney'	=> $order['order_amount'],
		));

		// $smarty->assign('pay_info',array(
		// 	'userId' 		=> $info->userId,
		// 	'tradeNo' 		=> $info->tradeNo,
		// 	'amountLoc' 	=> $info->amountLoc,
		// 	'currencyCode'	=> $info->currencyCode,
		// 	'charge'		=> $info->charge,
		// 	'cardType'		=> $info->cardType,
		// 	'tradeTime'		=> $info->tradeTime,
		// 	'buyerEmail'	=> $info->buyerEmail,
		// 	'orderStatus'	=> $info->orderStatus,
		// 	'operationFlag' => $info->operationFlag,
			
		// 	'usableRefundMoney' => $info->usableRefundMoney,
		// 	'allowRefundMoney'	=> $info->amountLoc - $info->usableRefundMoney
		// ));
		
		$smarty->assign('ur_here', $GLOBALS['_LANG']['yjpay_authorize']['page_title']);
		$smarty->assign('page',$GLOBALS['_LANG']['yjpay_authorize']);
		
		$smarty->assign('order', $order);
		
		$smarty->display('yjpay_authorize.htm');
	} else {
		sys_msg($_LANG['yjpay_authorize']['empty']);
	}
}

if ($act == 'doAuthorize') {
	$order = isset($_REQUEST['order_sn']) ? get_yjpay_order($_REQUEST['order_sn']) : false;
	
	if ($order['pay_code'] != 'yjpay' && $order['pay_status'] != PS_PAYED) {
	// if ($order['pay_code'] != 'yjpay' && $order['pay_status'] != PS_PAYING) {
		sys_msg($_LANG['yjpay_errors']['order_empty']);
	}

	$payment = unserialize_config($order['pay_config']);
	$esp_api = new esp_api (
		$payment['yjpay_cfg_partner_id'],
		$payment['yjpay_cfg_secret_key'],
		$payment['yjpay_cfg_debug']
	);
	
	
	$authorizeNote  = $_REQUEST['note'];
	$allowOrder 	= !empty($_POST['allow_button']);
	
	if ($allowOrder) {
		$msg = $esp_api->process_confirm($order['order_sn'],$authorizeNote);
	} else {
		$msg = $esp_api->process_cancel($order['order_sn'],$authorizeNote);
	}

	if ($msg->status == 'success') {
		// $pay_status   = $allowOrder ? PS_UNPAYED : PS_PAYED;
		$pay_status   = $allowOrder ? PS_PAYED : PS_UNPAYED;
		$order_status = $allowOrder ? $order['order_status'] : OS_CANCELED;
		
		order_action($order['order_sn'], $order_status, $order['shipping_status'], $pay_status, 
			sprintf($GLOBALS['_LANG']['yjpay_authorize_success'],$allowOrder ? 'YES' : 'NO',$authorizeNote)
		);
		
		// pay status/order status modify
		if (!$allowOrder) {
			yjpay_order_status($order['order_id'],OS_CANCELED);
			// yjpay_pay_status($order['order_id'],PS_UNPAYED);
		}	
		
		ecs_header("Location: order.php?act=info&order_id=" . $order['order_id'] . "\n");
        exit;
	} else {
		sys_msg(sprintf($_LANG['yjpay_authorize_fail'],$msg->resultMessage));
	}
}

function get_yjpay_order($order_sn) {
	$sql = <<<EOF
		SELECT 
			OrderInfo.order_id,OrderInfo.order_sn,OrderInfo.order_status,OrderInfo.shipping_status,
			OrderInfo.pay_status,Payment.pay_code,Payment.pay_config,OrderInfo.pay_time,OrderInfo.order_amount
		FROM 
			:prefixorder_info AS OrderInfo LEFT JOIN :prefixpayment AS Payment ON OrderInfo.pay_id = Payment.pay_id 
		WHERE 
			OrderInfo.order_sn = '{$order_sn}' AND Payment.pay_code = 'yjpay'
EOF;

	return $GLOBALS['db']->getRow(str_replace(':prefix',$GLOBALS['ecs']->prefix,$sql));
}

function reload_page() {
	echo 'window.location.href=window.location.href;';
}

function yjpay_order_status($order_id,$order_status = OS_CANCELED) {
	$sql = <<<EOF
		UPDATE :prefixorder_info SET order_status = {$order_status} WHERE order_id = $order_id
EOF;
	
	return $GLOBALS['db']->query(str_replace(':prefix', $GLOBALS['ecs']->prefix, $sql));
}

function yjpay_pay_status($order_id,$pay_status = PS_PAYED)  {
	$sql = <<<EOF
		UPDATE :prefixorder_info SET pay_status = {$pay_status} WHERE order_id = $order_id
EOF;
	
	return $GLOBALS['db']->query(str_replace(':prefix', $GLOBALS['ecs']->prefix, $sql));
}
<?php

require_once(ROOT_PATH . 'yjpay/inc/esp_api.php');
require_once(ROOT_PATH . 'includes/lib_order.php');

# load lang files
$lang = dirname(__FILE__) . '/langs/' . $GLOBALS['_CFG']['lang'] . '/esp.php';
if (is_file($lang)) {
    include_once($lang);
}

global $_LANG;

/*
$_POST = Array(
    'orderNo' => '20151025874081445761309',
    'notifyTime' => '2015-10-25 16:19:27',
    'resultCode' => 'EXECUTE_SUCCESS',
    'sign' => '4254a8eb7b5201d8134361ea00b41642',
    'resultMessage' => '成功',
    'outOrderNo' => '2015102587408',
    'version' => '1.0',
    'protocol' => 'httpPost',
    'pay_status' => 'success',
    'service' => 'espPciOrderPay',
    'success' => 'true',
    'signType' => 'MD5',
    'partnerId' => '20140526020000027815'
);

ob_start();

print_r($_POST);

file_put_contents(dirname(__FILE__) . '/easypay.log',ob_get_contents(),FILE_APPEND);

ob_end_clean();
*/
if ($_POST) {
	#print_r($_POST);
    // if($_POST['status']){

    // }
	$order = get_yjpay_order($_POST['merchOrderNo']);
	
    if ($order) {

		#print_r($order);
        require_once(ROOT_PATH . 'includes/lib_clips.php');
		require_once(ROOT_PATH . 'includes/lib_payment.php');
		
        $payment = unserialize_config($order['pay_config']);
		$esp_api = new esp_api (
			$payment['yjpay_cfg_partner_id'],
			$payment['yjpay_cfg_secret_key'],
			$payment['yjpay_cfg_debug']
		);
		
        # check security code 
        $sign = $_POST['sign'];
        unset($_POST['sign']);
        
        $currSign = $esp_api->signature($_POST);
        // file_put_contents("E:/log/sign.log", "currsign:".$currSign."sign:".$sign);		
        if ($currSign == $sign) {
			if($_POST['service'] == 'cardAcquiringCashierPay') {
                    // if ($_POST['pay_status'] == 'success')     {
                if ($_POST['status'] == 'success')     {
                    yjpay_pay_status($order, PS_PAYED,
                        sprintf($_LANG['yjpay_pay_success'],$order['order_sn'])
                    );
                // } else if ($_POST['pay_status'] == 'authorizing') {
                } else if ($_POST['status'] == 'authorizing') {
                    yjpay_pay_status($order,PS_PAYED,
                        sprintf($_LANG['yjpay_wait_authorize'], $_POST['description'],$order['order_sn'])
                    );
                }
            }

            // // yoko debug
            // if($_POST['service'] == 'cardAcquiringPresaleResult'){
            //     if($_POST['status'] == 'success') {
            //         yjpay_pay_status($order, PS_UNPAYED,
            //             sprintf($_LANG['yjpay_pay_success'],$order['order_sn'])
            //         );
            //     } elseif ($_POST['status'] == 'fail') {
            //         yjpay_pay_status($order, PS_PAYED,
            //             sprintf($_LANG['yjpay_pay_success'],$order['order_sn'])
            //         );
            //     }
            // }

            // if($_POST['service'] == 'cardAcquiringRefund'){
            //     if($_POST['status'] == 'success') {
            //         yjpay_pay_status($order, PS_UNPAYED,
            //             sprintf($_LANG['yjpay_pay_success'],$order['order_sn'])
            //         );
            //     }
            // } 
        }
    }
}

echo 'success';

// get order info by order_sn
function get_yjpay_order($order_sn) {
    // yoko debug
    $sql = <<<EOF
        SELECT
            OrderInfo.order_id,OrderInfo.order_sn,OrderInfo.order_status,OrderInfo.shipping_status,OrderInfo.pay_status,
            Payment.pay_code,Payment.pay_config
        FROM :prefixorder_info as OrderInfo
            LEFT JOIN :prefixpayment as Payment ON OrderInfo.pay_id = Payment.pay_id
        WHERE order_sn = '{$order_sn}' AND Payment.pay_code = 'yjpay' AND OrderInfo.order_status IN ('0','1','5') AND OrderInfo.pay_status = '0'
EOF;

    return $GLOBALS['db']->getRow(str_replace(':prefix',$GLOBALS['ecs']->prefix, $sql));
}

// update order status
function yjpay_pay_status($order,$pay_status,$note = '') {
	
    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
			" SET order_status = '" . OS_CONFIRMED . "', " .
            " pay_status = '$pay_status', " .
            " pay_time = '".gmtime()."'" .
            "WHERE order_id = '{$order['order_id']}'";
    $GLOBALS['db']->query($sql);
	
    order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);				
}

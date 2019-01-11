<?php

require_once(ROOT_PATH . 'yjpaycn/inc/esp_api.php');
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
    file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$_POST['merchOrderNo'].'] 1 :'.json_encode($_POST)."\n\n",FILE_APPEND);
    // }
	$order = get_yjpaycn_order($_POST['merchOrderNo']);
    file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$_POST['merchOrderNo'].'] 2 :'.json_encode($order)."\n\n",FILE_APPEND);

    if ($order) {

		#print_r($order);
        require_once(ROOT_PATH . 'includes/lib_clips.php');
		require_once(ROOT_PATH . 'includes/lib_payment.php');

        $payment = unserialize_config($order['pay_config']);
        file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$_POST['merchOrderNo'].'] 3 :'.json_encode($payment)."\n\n",FILE_APPEND);

        $esp_api = new esp_api (
			$payment['yjpaycn_cfg_partner_id'],
			$payment['yjpaycn_cfg_secret_key'],
			$payment['yjpaycn_cfg_debug']
		);
        file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$_POST['merchOrderNo'].'] 4 :'.json_encode($esp_api)."\n\n",FILE_APPEND);

        # check security code
        $sign = $_POST['sign'];
        unset($_POST['sign']);

        $currSign = $esp_api->signature($_POST);
        file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$_POST['merchOrderNo'].'] 5 : mysign = '.$currSign." __ sign = ".$sign."\n\n",FILE_APPEND);

        // file_put_contents("E:/log/sign.log", "currsign:".$currSign."sign:".$sign);
        if ($currSign == $sign) {
    			if($_POST['service'] == 'espOrderPay') {
                        // if ($_POST['pay_status'] == 'success')     {
                    if ($_POST['status'] == 'success')     {
                        yjpaycn_pay_status($order, PS_PAYED,
                            sprintf($_LANG['yjpaycn_pay_success'],$order['order_sn'])
                        );
                    // } else if ($_POST['pay_status'] == 'authorizing') {
                    } else if ($_POST['status'] == 'authorizing') {
                        yjpaycn_pay_status($order,PS_PAYED,
                            sprintf($_LANG['yjpaycn_wait_authorize'], $_POST['description'],$order['order_sn'])
                        );
                    }
                }
        }
    }
}

echo 'success';

// get order info by order_sn
function get_yjpaycn_order($order_sn) {
    // yoko debug
    $sql = <<<EOF
        SELECT
            OrderInfo.order_id,OrderInfo.order_sn,OrderInfo.order_status,OrderInfo.shipping_status,OrderInfo.pay_status,
            Payment.pay_code,Payment.pay_config
        FROM :prefixorder_info as OrderInfo
            LEFT JOIN :prefixpayment as Payment ON OrderInfo.pay_id = Payment.pay_id
        WHERE order_sn = '{$order_sn}' AND Payment.pay_code = 'yjpaycn' AND OrderInfo.order_status IN ('0','1','5') AND OrderInfo.pay_status = '0'
EOF;

    return $GLOBALS['db']->getRow(str_replace(':prefix',$GLOBALS['ecs']->prefix, $sql));
}

// update order status
function yjpaycn_pay_status($order,$pay_status,$note = '') {

    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
			" SET order_status = '" . OS_CONFIRMED . "', " .
            " pay_status = '$pay_status', " .
            " pay_time = '".gmtime()."'" .
            "WHERE order_id = '{$order['order_id']}'";
    file_put_contents(dirname(__FILE__).'/log/'.date('Y_m_d').'-log.txt','['.date('Y-m-d H:i:s').' '.$order.']:'.$sql."\n\n",FILE_APPEND);
    $GLOBALS['db']->query($sql);

    order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
}

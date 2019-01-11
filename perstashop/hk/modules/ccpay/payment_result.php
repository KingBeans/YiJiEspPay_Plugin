<?php
error_reporting(E_ERROR | E_PARSE);
chdir(dirname(__FILE__).'/../../');

/* SSL Management */
$useSSL = true;
include ( './config/config.inc.php');

include_once("./gcp/common.php");

$key = Configuration::get ('CCPAY_MERCHANTKEY');


include ('./status.config.php');
$conf = Configuration::getMultiple ( array (
				'CCPAY_SUCCES_ORDER_STATUS',
				'CCPAY_CLOSED_ORDER_STATUS',
				'CCPAY_PENDING_ORDER_STATUS',
                'CCPAY_AUTHORIZING_ORDER_STATUS',
		) );

include('./modules/ccpay/ccpay.php');

$ccpay = new ccpay();

$receiveStatus = $_POST['status'];

// get order id
$orderNo  = $_POST['orderNo'];
$pos = strpos($orderNo, '|');

if(isset($pos)) {
	$orderid = substr($orderNo, $pos+1);
} else {
	$orderid = $orderNo;
}

$order = new Order ( ( int )$orderid );
$history = new OrderHistory();

$sign = array_key_pop($_POST,'sign');
file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." NOTIFY-DATA]:".json_encode($_POST,1)."\n",FILE_APPEND);

if($sign == yjfpayc_signature($_POST)) {
// if(true) {
	if($receiveStatus=== 'success'){

	  $status = (int)$conf['CCPAY_SUCCES_ORDER_STATUS'];
		$history->id_order = $order->id;
		$history->changeIdOrderState($status, (int)$order->id);
		$extraVars = array();
		$TR = $history->addWithemail ( true, $extraVars );
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-DATA]: CCPAY_SUCCES_ORDER_STATUS \n\n",FILE_APPEND);
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-RESULT]:".json_encode($TR,1)."\n\n",FILE_APPEND);
	}
	else if($receiveStatus === 'authorizing')
	{
	    $history->id_order = $order->id;
	    $status = (int)$conf['CCPAY_AUTHORIZING_ORDER_STATUS'];
		$history->changeIdOrderState($status, (int)$order->id);
	    $extraVars = array();
		$TR = $history->add( true, $extraVars );
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-DATA]: CCPAY_AUTHORIZING_ORDER_STATUS \n\n",FILE_APPEND);
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-RESULT]:".json_encode($TR,1)."\n\n",FILE_APPEND);
	}
	else {
		$status = (int)$conf['CCPAY_CLOSED_ORDER_STATUS'];
		$history->id_order = $order->id;
		$history->changeIdOrderState($status, (int)$order->id);
		$extraVars = array();
		$TR = $history->add( true, $extraVars );
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-DATA]: CCPAY_CLOSED_ORDER_STATUS \n\n",FILE_APPEND);
		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',( int )$orderid." [ ".date('Y-m-d H:i:s')." SAVE-RESULT]:".json_encode($TR,1)."\n\n",FILE_APPEND);
	}
	echo "success";
} else {
	echo "Other Service";
}


    /**
     * get pay signature
     */
    function yjfpayc_signature(array $params) {
    	$merchantkey = Configuration::get ( 'CCPAY_MERCHANTKEY' );
        # sort for key
        ksort($params);

        $clientSignatureString = '';
        foreach ($params as $key => $value) {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }

        $clientSignatureString = substr($clientSignatureString, 0, -1);
        $clientSignatureString = trim($clientSignatureString) . $merchantkey;

        return md5($clientSignatureString);
    }

    // get post sign
    function array_key_pop(&$array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $default;
    }

?>

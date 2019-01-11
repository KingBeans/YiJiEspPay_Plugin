<?php 
# require logic files
require_once(ROOT_PATH . 'yjpay/inc/esp_api.php');
require_once(ROOT_PATH . 'includes/lib_order.php');

# load lang files
$lang = dirname(__FILE__) . '/langs/' . $GLOBALS['_CFG']['lang'] . '/esp.php';
if (is_file($lang)) {
    include_once($lang);
}

global $_LANG;

# check action name
$act = isset($_GET['act']) ? trim($_GET['act']) : 'default';
if (!in_array($act, array('default', 'submit'))) {
    die('Hacking attempt');
}


if ($act == 'default' || $act == 'submit')
{
	# check order info
	$order = empty($_REQUEST['order_sn']) ? false : get_yjpay_order(addslashes($_REQUEST['order_sn']));
	// yoko debug
	$order_json = json_encode($order,true);
	// file_put_contents("E:/log/order_info_deve.log", $order_json . "request time:/n/n" . time(),FILE_APPEND);

    if (empty($order))   {
        show_message($_LANG['yjpay_errors']['order_empty']);
    }
	
	# include lib_clips.php
	require_once(ROOT_PATH . 'includes/lib_clips.php');
	require_once(ROOT_PATH . 'includes/lib_payment.php');
			
	# unserialize configure 
	$payment = unserialize_config($order['pay_config']);
	
	$pament_json = json_encode($payment,true);	
	// file_put_contents("E:/log/payment_dev.log", "dasds:".$pament_json,FILE_APPEND);

	# declare template vars
	$card 	= isset($_POST['card']) ? $_POST['card'] : array (
		'state' 	=> $order['province_name'],
		'city'  	=> $order['city_name'],
		'address' 	=> $order['address'],
		
		'email' 		=> $order['customer_email'],
		'post_code' 	=> $order['zipcode'],
		'phone_number'  => $order['tel'],

	);
	
	# if check country code include code 
	if (empty($card['country']) && isset($_LANG['yjpay_ccode'][trim($order['country_name'])])) {
		$card['country'] = $_LANG['yjpay_ccode'][trim($order['country_name'])];
	}
	
	$errors = array();
	$errMsg = false;
    
	# if current submit information 
    if ($act == 'submit') {
        # check card basic information
  //       if (empty($card['number'])) 		$errors['number'] 			= $_LANG['yjpay_errors']['number_empty'];
    	
        if (empty($card['first_name'])) 	$errors['holder'] 			= $_LANG['yjpay_errors']['holder_empty'];
        if (empty($card['last_name'])) 		$errors['holder'] 			= $_LANG['yjpay_errors']['holder_empty'];
		// if (empty($card['security_code'])) 	$errors['security_code'] 	= $_LANG['yjpay_errors']['cvv_empty'];
		
  //       if (empty($card['expired_year']) || empty($card['expired_year'])) {
		// 	$errors['expired_year'] = $_LANG['yjpay_errors']['expired_empty'];
		// }
		
        
		// # check bill address information
        if (empty($card['country'])) 		$errors['country'] 		= $_LANG['yjpay_errors']['country_empty'];
        if (empty($card['state'])) 			$errors['state'] 		= $_LANG['yjpay_errors']['state_empty'];
        if (empty($card['city'])) 			$errors['city'] 		= $_LANG['yjpay_errors']['city_empty'];
        if (empty($card['address'])) 		$errors['address'] 		= $_LANG['yjpay_errors']['address_empty'];
		
        if (empty($card['post_code'])) 		$errors['post_code'] 	= $_LANG['yjpay_errors']['zipcode_empty'];
        if (empty($card['email'])) 			$errors['email'] 	 	= $_LANG['yjpay_errors']['email_empty'];
        if (empty($card['phone_number']))	$errors['phone_number'] = $_LANG['yjpay_errors']['phone_empty'];
	
		// # check card format information
  //       if (!empty($card['email]']) && !filter_var($card['email'], FILTER_VALIDATE_EMAIL)) {
  //           $errors['email'] = $_LANG['yjpay_errors']['email_format'];
  //       }
		
  //       if (!empty($card['security_code']) && !preg_match('/^\d{3}$/', $card['security_code'])) {
  //           $errors['security_code'] = $_LANG['yjpay_errors']['cvv_format'];
  //       }

		// if (!empty($card['number'])) {
		// 	if (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $card['number'])) {
		// 		$card['cardType'] = 'Visa';
		// 	} else if (preg_match('/^5[1-5][0-9]{14}$/', $card['number'])) {
		// 		$card['cardType'] = 'MasterCard';
		// 	} else if (preg_match('/^(35(28|29|[3-8][0-9])[0-9]{12}|2131[0-9]{11}|1800[0-9]{11})$/', $card['number'])) {
		// 		$card['cardType'] = 'JCB';
		// 	} else {
		// 		$errors['number'] = $_LANG['yjpay_errors']['number_invalid'];
		// 	}
		// }
		
		
        if (empty($errors)) {
			$log_id  = get_paylog_id($order['order_id'], PAY_ORDER);
			
			# create esp_order 
            $esp_order = new esp_order();
            #order_no

			$esp_order->outOrderNo 	= $order['order_sn']; 
			// file_put_contents("E:/log/billinfo.log", json_encode($card,true));
			# set bill information
            $esp_order->billToCountry 	= $card['country'];
            $esp_order->billToState 	= $card['state'];
			$esp_order->billToCity 		= $card['city'];
			$esp_order->billtoStreet 	= $card['address'];
			
			$esp_order->billToFirstName = $card['first_name'];
            $esp_order->billToLastName 	= $card['last_name'];
            $esp_order->billToEmail 	= $card['email'];
			#$esp_order->billToEmail 	= $order['email'];
			
			# customer information
            $esp_order->billToPhoneNumber = $card['phone_number'];
            $esp_order->billToPostalCode  = $card['post_code'];
			
			# set ship information
			// file_put_contents("E:/log/countrys.log", "country_name:".$order['country_name']."card_country:".$card['country']."lang_name:".json_encode($_LANG['yjpay_ccode'],true).'languge package:'.$GLOBALS['_CFG']['lang'],FILE_APPEND);
				// exit();
			$esp_order->shipToCountry 	= isset($_LANG['yjpay_ccode'][trim($order['country_name'])]) ? 
										  $_LANG['yjpay_ccode'][trim($order['country_name'])] : $card['country']; 
			// $esp_order->shipToCountry  = $order['country_name'];
			// $esp_order->shipToCountry  = 'CHN';
			$esp_order->shipToState 	= $order['province_name'];
			$esp_order->shipToCity 		= $order['city_name'];
            $esp_order->shipToStreet1 	= $order['address'];
			
            $esp_order->shipToFirstName = $card['first_name'];
            $esp_order->shipToLastName 	= $card['last_name'];
			
            $esp_order->shipToEmail 		= $order['email'];
            $esp_order->shipToPhoneNumber 	= $order['tel'];
            $esp_order->shipToPostalCode	= empty($order['zipcode']) ? $card['post_code'] : $order['zipcode'] ;
            
			# customer information
			if (!empty($order['customer_email'])) {
				$esp_order->customerEmail = $order['customer_email'];
			} else {
				$esp_order->customerEmail = $order['email'];
			}
			
            if (!empty($order['office_phone']))      {
                $esp_order->customerPhoneNumber = $order['office_phone'];
            } else if (!empty($order['home_phone']))        {
                $esp_order->customerPhoneNumber = $order['home_phone'];
            } else {
                $esp_order->customerPhoneNumber = $order['tel'];
            }
			
			# merchant information 
			$esp_order->merchantEmail	= $payment['yjpay_cfg_merchant_email'];
            $esp_order->merchantName	= $payment['yjpay_cfg_merchant_name'];
			$esp_order->webSite			= $GLOBALS['ecs']->get_domain();
			
			$esp_order->ipAddress 		= real_ip();
			
			#if ($payment['yjpay_cfg_debug']) {
				$esp_order->deviceFingerprintId =  $_COOKIE['ECS_ID'];
			#} else {
			#	$esp_order->deviceFingerprintId = 'yijifu' . $_COOKIE['ECS_ID'];
			#}
	
			# free information 
            $esp_order->logisticsFee 	= $order['shipping_fee'];
            $esp_order->logisticsMode 	= $order['shipping_name'];
            $esp_order->amountLoc 		= $order['order_amount'];
            $esp_order->currencyCode 	= $payment['yjpay_cfg_currency'];
            // TEST TO BE DONE
            // $esp_order->acquiringType   = "CRDIT";
            $esp_order->acquiringType   = $payment['yjpay_cfg_payment_type'];
			
			# credit card information
			// $esp_order->cardHolderFirstName = $card['first_name'];
            // $esp_order->cardHolderLastName = $card['last_name'];
            // $esp_order->expirationDate 		= $card['expired_year'] . $card['expired_month'];
			
			#card info
			#yoko debug
			// $esp_order->cardType 	= $card['cardType'];
			// $esp_order->cardNo 	 	= str_replace(' ', '', $card['number']);
			// $esp_order->cvv 	  	= $card['security_code'];
			
			# other information
            $esp_order->remark = $order['order_sn'] . '-' . $log_id;
			
			
			# fill order goods information
            $ecs_items = get_yjpay_goods($order['order_id']);
            $esp_items = array();
			
            foreach ($ecs_items as $goods) {
                $goods_item = new esp_goods();
                $goods_item->goodsName 		= $goods['goods_name'];
                $goods_item->goodsNumber	= $goods['goods_sn'];
                $goods_item->goodsCount 	= $goods['goods_number'];
				
                $goods_item->itemSharpProductCode 	= $goods['cat_name'];
                $goods_item->itemSharpUnitPrice 	= $goods['goods_price'];

                array_push($esp_items, $goods_item);
            }
			
			#  notify url 
			$ntf_url = $GLOBALS['ecs']->url() . 'yjpay_receive.php';
			#  return url
			$ret_url = $GLOBALS['ecs']->url() . 'yjpay_return.php';
			# 实例化类 取值
			$esp_api = new esp_api(
				$payment['yjpay_cfg_partner_id'],
				$payment['yjpay_cfg_secret_key'],
				$payment['yjpay_cfg_debug']
			);

			// yoko debug
			// file_put_contents("E:/log/esp_api.log", json_encode($esp_api,true));
			
			$msg = $esp_api->order_pay($esp_order, $esp_items, $ntf_url, $ret_url);
			// echo "string";
			echo $msg;
			die();
			
    //         if ($msg->code != 'APPLY_CARD_PAY_SUCCESS' && $msg->code != "ANALYZERESULT_REVIEW") {
				// #print_r($esp_order);
				// #print_r($esp_items);
				
    //             $errMsg = $msg->resultMessage;
    //         } else {
				// # write order action
				// order_paid($log_id, PS_PAYING,
				// 	sprintf($_LANG['yjpay_wait_notify'],$order['order_sn'])
				// );
				
    //             ecs_header("Location:" . return_url('yjpay') . "\n");
    //             exit();
    //         }
        }
    }
	
	// file_put_contents("E:/log/pament.log", $payment['yjpay_cfg_debug']);
	
	if ($payment['yjpay_cfg_debug']) {
        $sec_org     = '1snn5n9w';
        $sec_session = 'xxxyyyzzz' . $_COOKIE['ECS_ID'];
    } else {
        $sec_org     = 'k8vif92e';
        $sec_session = 'yijifu' . $_COOKIE['ECS_ID'];
    }
			
	$expires_month = get_yjpay_expires_month();
    $expires_year = get_yjpay_expires_year();

	# include pay page
    include(dirname(__FILE__) . '/templates/index.php');
}


// yoko degub
function get_yjpay_order($order_sn)
{
    $sql = <<<EOF
        SELECT
            OrderInfo.order_id,OrderInfo.order_sn,OrderInfo.order_status,OrderInfo.shipping_status,OrderInfo.pay_status,
            OrderInfo.consignee,OrderInfo.tel,OrderInfo.email,OrderInfo.address,OrderInfo.zipcode,
            OrderInfo.order_amount,OrderInfo.shipping_fee,
            OrderInfo.shipping_name,User.email as customer_email,User.office_phone,User.home_phone,
            Payment.pay_code,Payment.pay_config,
            City.region_name as city_name,Province.region_name as province_name,Country.region_name as country_name
        FROM :prefixorder_info as OrderInfo
            LEFT JOIN :prefixpayment as Payment ON OrderInfo.pay_id = Payment.pay_id
            LEFT JOIN :prefixregion as Country ON OrderInfo.country = Country.region_id
            LEFT JOIN :prefixregion as Province ON OrderInfo.province = Province.region_id
            LEFT JOIN :prefixregion as City ON OrderInfo.city = City.region_id
            LEFT JOIN :prefixusers as User ON OrderInfo.user_id = User.user_id
        WHERE Payment.pay_code = 'yjpay' AND order_sn = '{$order_sn}' AND OrderInfo.order_status IN ('0','1','5') AND 		
			OrderInfo.pay_status = '0'
EOF;
	
    return $GLOBALS['db']->getRow(str_replace(':prefix', $GLOBALS['ecs']->prefix, $sql));
}

function get_yjpay_goods($order_id)
{
    $sql = <<<EOF
		SELECT
			OrderGoods.goods_id,OrderGoods.goods_name,OrderGoods.goods_sn,OrderGoods.goods_number,OrderGoods.goods_price,
			Category.cat_name
		FROM
			:prefixorder_goods AS OrderGoods
			LEFT JOIN :prefixgoods AS Goods ON OrderGoods.goods_id = Goods.goods_id
			LEFT JOIN :prefixcategory AS Category ON Goods.cat_id = Category.cat_id
		WHERE OrderGoods.order_id = :order_id;
EOF;

    return $GLOBALS['db']->getAll(str_replace(array(':prefix', ':order_id'), array($GLOBALS['ecs']->prefix, $order_id), $sql));
}

function get_yjpay_expires_month() {
    $expires_month = array();
	
    for ($i = 1; $i < 13; $i++)   {
        $expires_month[sprintf('%02d', $i)] = strftime('%B - (%m)', mktime(0, 0, 0, $i, 1, 2000));
    }

    return $expires_month;
}

function get_yjpay_expires_year() {
    $expires_year = array();
    $today = getdate();
	
    for ($i = $today['year']; $i < $today['year'] + 10; $i++)  {
        $expires_year[strftime('%y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
    }
	
    return $expires_year;
}
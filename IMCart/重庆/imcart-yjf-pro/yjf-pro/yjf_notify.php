<?php

    # import common functions

    require_once('lib/api.conf.php');

    require_once('lib/common_functions.php');

	require_once('lib/OrderCore.php');

    $itemno 				= $_POST['merchOrderNo'];

    // $_POST['currency_code'] = $_POST['currency'];

    $sign = array_key_pop($_POST, 'sign');

    $result = OrderCore::getOrder($itemno,'yiji');

    if ($sign == yjfpay_signature($_POST,$result['data']['gateway_conf']['exp_yiji_merchant_ckey'])) {

        $status  = strtolower($_POST['status']);

            # process notify status

            if ($status == 'success') {

                // param 	:	实际参数必须提供

				// type 	:  	server 表示要更改订单状态并且跳转到支付成功页面，  client 表示仅界面跳转到成功页面,适合同步跟异步处理

				$_POST['trade_no'] = $_POST['orderNo'];   // 第三方流水号 , 必须提供
				$_POST['currency_code'] = '';
				$_POST['amount'] = '';
				OrderCore::orderSuccess($itemno,$_POST,'server',1) ;

            } else if ($status == 'authorizing') {

            	OrderCore::OrderChecking($itemno,$_POST,'server',1) ;

            } else if ($status == 'fail') {

                // $param['msg'] 失败的原因，最好提供

				$_POST['msg'] = $_POST['resultMessage'];

				OrderCore::OrderFailure($itemno,$_POST,'server',1) ;

            }

            echo "success";

    } else {

    	echo 'otherService';

    }






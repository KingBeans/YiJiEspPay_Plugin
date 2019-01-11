<?php



    # import common functions



    require_once('lib/api.conf.php');



    require_once('lib/common_functions.php');



	require_once('lib/OrderCore.php');



    $itemno 				= $_GET['merchOrderNo'];



    // $_GET['currency_code'] = $_GET['currency'];



    $sign = array_key_pop($_GET, 'sign');



    $result = OrderCore::getOrder($itemno);



    if ($sign == yjfpay_signature($_GET,MERCHANT_ckey)) {



        $status  = strtolower($_GET['status']);



            # process notify status



            if ($status == 'success') {



                // param 	:	实际参数必须提供



				// type 	:  	server 表示要更改订单状态并且跳转到支付成功页面，  client 表示仅界面跳转到成功页面,适合同步跟异步处理



				$_GET['trade_no'] = $_GET['orderNo'];   // 第三方流水号 , 必须提供

				$_GET['currency_code'] = '';

				$_GET['amount'] = '';

				OrderCore::orderSuccess($itemno,$_GET,'client',1) ;



            } else if ($status == 'authorizing') {



            	OrderCore::OrderChecking($itemno,$_GET,'client',1) ;



            } else if ($status == 'fail') {



                // $param['msg'] 失败的原因，最好提供



				$_GET['msg'] = $_GET['resultMessage'];



				OrderCore::OrderFailure($itemno,$_GET,'client',1) ;



            }



            echo "success";



    } else {



    	echo 'otherService';



    }












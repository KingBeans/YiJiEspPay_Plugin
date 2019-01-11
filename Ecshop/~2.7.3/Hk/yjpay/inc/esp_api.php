<?php
# declare easypay pay status
define('EASYAPY_PAY_STATUS_SUBMIT', 0);
define('EASYPAY_PAY_STATUS_AUTH', 1);
define('EASYPAY_PAY_STATUS_COMPLETE', 2);
define('EASYPAY_PAY_STATUS_CANCEL', 3);


class esp_api
{
    // const SERVICE_GATEWAY_URL     = 'https://openapiglobal.yiji.com/gateway.html';
    const SERVICE_GATEWAY_URL     = 'https://openapi.yjpay.hk/gateway.html';
    #const SERVICE_GATEWAY_URL = 'https://openapi.yiji.com/gateway.html';
    // const SERVICE_GATEWAY_DEV_URL = 'https://openapi.yijifu.net/gateway.html';
    const SERVICE_GATEWAY_DEV_URL = 'https://hkopenapitest.yiji.com/gateway.html';
    // const SERVICE_GATEWAY_DEV_URL = 'http://192.168.46.16:8630/gateway.html';

    // const SERVICE_ENCRYPT_URL = "http://cer.gpayonline.com/encrypt.action?action=doEncrypt"; //正式服务地址
    const SERVICE_ENCRYPT_URL = "http://113.31.83.140/encrypt.action?action=doEncrypt"; //正式服务地址

    
    // const SERVICE_ORDER_PAY            = 'espPciOrderPay';
    const SERVICE_ORDER_PAY_ORDER_TYPE = 'MOTO_EDC';

    // const SERVICE_ORDER_REFUND = 'espOrderRefund';
    // const SERVICE_cancel_order = 'espOrderCancel';
    // const SERVICE_ORDER_QUERY  = 'espOrderQuery';

    // const SERVICE_PROCESS_CONFIRM = 'espProcessConfirm';
    // const SERVICE_PROCESS_CANCEL  = 'espProcessCancel';
    const SERVICE_PRESALE_RESULT         = 'cardAcquiringPresaleResult';
    const SERVICE_ORDER_REFUND           = 'cardAcquiringRefund';
    const SERVICE_ORDER_PAY              = 'cardAcquiringCashierPay';
    // const SERVICE_cancel_order = 'cardAcquiringPresaleResult';

    const SERVICE_PROTOCOL  = 'httpPost';
    const SERVICE_VERSION   = '1.0';
    const SERVICE_SIGN_TYPE = 'MD5';

    # declare partner id and secret key
    var $partnerID, $secretKey, $debug;
	
    function esp_api($partnerId, $secretKey, $debug = true)
    {
        $this->partnerID = $partnerId;
        $this->secretKey = $secretKey;
        $this->debug = $debug;
    }

    #  notify url 
    // $ntf_url = $GLOBALS['ecs']->url() . 'yjpay_receive.php';
    #  return url
    // $ret_url = $GLOBALS['ecs']->url() . 'yjpay_return.php';

	// 订单信息 货物信息
    function order_pay($order, $goods, $notify_url, $return_url = '')
    {
        # get service parameters
        $init_data = $order->service_parameters();

        $basicData  = $init_data['basicData'];
        unset($init_data['basicData']);

        $orderDetails = $init_data;
        $submit['attachDetails']    = json_encode($orderDetails,true);

        foreach ($basicData as $k => $v) {
            $submit[$k] = $v;
        }

        // file_put_contents("E:/log/submit_data.log", json_encode($submit,true));
        // $submit['userId']       = $this->partnerID;
        // $submit['exVersion']    = 'Ecs-3.0.0'; //TO BE KNOWED
        // $submit['cardNo']        = $this->encrypt($submit['cardNo'], 'PTHZKJ');
        // $submit['cvv']           = $this->encrypt($submit['cvv'], 'PTHZKJ');               
        // $submit['wkGoodsInfoList']   = json_encode($goods);
        // $submit['goodsInfoOrders']  = json_encode($goods);
        // $submit['currency']     = $basicData['currencyCode'];
        // $submit['orderAmount']  = $basicData['amountLoc'];
        // $submit['exVersion']    = 'Ecs-3.0.0';//TO BE KNOWED
        // $submit['exVersion']    = self::SERVICE_VERSION;//TO BE KNOWED
        // $submit['cardNo']        = $this->encrypt($submit['cardNo'], 'PTHZKJ');
        // $submit['cvv']           = $this->encrypt($submit['cvv'], 'PTHZKJ');               
        // $submit['wkGoodsInfoList']   = json_encode($goods);      
        // $submit['orderNo']      = $this->orderNo;
        // $submit['orderNo']      = $basicData['orderNo'];

        $submit['userId']       = $this->partnerID;
        $submit['goodsInfoOrders']  = json_encode($goods,true);       
        // 公共参数
        $submit['protocol'] 	= self::SERVICE_PROTOCOL;
        $submit['service'] 		= self::SERVICE_ORDER_PAY;
        $submit['version'] 		= self::SERVICE_VERSION;
        $submit['signType'] 	= self::SERVICE_SIGN_TYPE;		
        $submit['returnUrl'] 	= $return_url;
        $submit['notifyUrl'] 	= $notify_url;        
		$submit['partnerId'] 	= $this->partnerID;
        $submit = array_merge($submit,$basicData);
		
        $submit['sign'] = $this->signature($submit);
        // $result = $this->_execute($submit);
        // 表单请求接口信息
        // file_put_contents("E:/log/submit_data2.log", json_encode($submit,true));
        // exit();
        $html = $this->submit_pay_form($submit);
        return $html;
		// return json_decode($result);
    }

	
    function order_cancel($order_sn, $reason = '')
    {
        $submit = array(
            'protected' 	=> self::SERVICE_PROTOCOL,
            'service' 		=> self::SERVICE_PRESALE_RESULT,
            'version' 		=> self::SERVICE_VERSION,
            'partnerId' 	=> $this->partnerID,

            'signType' 		=> self::SERVICE_SIGN_TYPE,
            'orderNo'       => $order_sn . time(),
            'returnUrl'    => 'http://hk.ecshop273.com/yjpay_return.php',
            'notifyUrl'    => 'http://hk.ecshop273.com/yjpay_receive.php',
            'originalMerchOrderNo' 	=> $order_sn,
            'resolveReason' 		=> $reason,
            'merchOrderNo' 	=> $order_sn . time(),
            'isAccept'      => 'false'
        );

        $submit['sign'] = $this->signature($submit);

        // file_put_contents("E:/log/authorize/cancel.log", json_encode($submit,true));
        $result = $this->_execute($submit);

        return json_decode($result);
    }

	
    function order_refund($order_sn, $refund_money, $reason = '')
    {
        // file_put_contents('E:/log/order_refund.log', "order_sn:".$order_sn."refund_money:".$refund_money."reason:".$reason."/n/n/n",FILE_APPEND);
        // exit();
        $submit = array(
            'protected' 	=> self::SERVICE_PROTOCOL,
            // 'service'        => self::SERVICE_ORDER_REFUND,
            'service' 		=> 'cardAcquiringRefund',
            'version'		=> self::SERVICE_VERSION,
            'partnerId' 	=> $this->partnerID,
            'signType' 		=> self::SERVICE_SIGN_TYPE,
            'orderNo' 	     => $order_sn . time(),
            'returnUrl'    => 'http://hk.ecshop273.com/yjpay_return.php',
            'notifyUrl'    => 'http://hk.ecshop273.com/yjpay_receive.php',
            'originalMerchOrderNo' => $order_sn,
            'refundAmount'  => $refund_money,
            'refundReason'  => $reason,
            'merchOrderNo' 		=> $order_sn .  time()	
        );

        $submit['sign'] = $this->signature($submit);
        // file_put_contents("E:/log/authorize/cancel.log", json_encode($submit,true));
        // $result = $this->submit_pay_form($submit);
        $result = $this->_execute($submit);
        // file_put_contents("E:/log/refund_ret.log", "result:".$result."/n/n/n"."info:".$submit['service']."/n".$submit['partnerId'].$submit['merchOrderNo'],FILE_APPEND);
        // return $result;
        return json_decode($result);
    }

	
    function order_query($order_sn)
    {
        $submit = array(
            'protected' 	=> self::SERVICE_PROTOCOL,
            'service' 		=> self::SERVICE_ORDER_QUERY,
            'version' 		=> self::SERVICE_VERSION,
            'partnerId' 	=> $this->partnerID,
            'orderNo'		=> $order_sn .  time(),
            'signType' 		=> self::SERVICE_SIGN_TYPE,
            'outOrderNo' 	=> $order_sn,
            'pageNo' 		=> 1
        ); 

        $submit['sign'] = $this->signature($submit);		
        $ordersJson = $this->_execute($submit);      
        $orders = json_decode($ordersJson);
		
        return ($orders->success && $orders->count > 0) ? $orders->espOrderInfoList[0] : false;
    }

    function process_confirm($order_sn,$reason)
    {
        $submit = array(
            'protected' 	=> self::SERVICE_PROTOCOL,
            'service' 		=> self::SERVICE_PRESALE_RESULT,
            'version' 		=> self::SERVICE_VERSION,
            'partnerId' 	=> $this->partnerID,
            'signType' 		=> self::SERVICE_SIGN_TYPE,

            'orderNo'       => $order_sn . time(),
            'returnUrl'    => 'http://hk.ecshop273.com/yjpay_return.php',
            'notifyUrl'    => 'http://hk.ecshop273.com/yjpay_receive.php',
            // 'notify_url'    => 'http://hk.ecshop273.com/yjpay_receive.php',
            'originalMerchOrderNo'  => $order_sn,
            'resolveReason'         => $reason,
            'merchOrderNo'  => $order_sn . time(),
            'isAccept'      => 'true'
        );

        $submit['sign'] = $this->signature($submit);
        // file_put_contents("E:/log/authorize/process_confirm.log", json_encode($submit,true));
        $result = $this->_execute($submit);
		
        return json_decode($result);
    }

    function process_cancel($order_sn, $cancel_reason = '', $operator_id = null,
                            $operator_name = null, $product_code = null, $merchant_order_biz_noe = null)
    {
        $submit = array(
            'protected' 	=> self::SERVICE_PROTOCOL,
            'service' 		=> self::SERVICE_PRESALE_RESULT,
            'version' 		=> self::SERVICE_VERSION,
            'partnerId' 	=> $this->partnerID,
            'signType' 		=> self::SERVICE_SIGN_TYPE,
            'orderNo'       => $order_sn . time(),
            'returnUrl'    => 'http://hk.ecshop273.com/yjpay_return.php',
            'notifyUrl'    => 'http://hk.ecshop273.com/yjpay_receive.php',

            'originalMerchOrderNo'  => $order_sn,
            'resolveReason'         => $cancel_reason,
            'merchOrderNo'  => $order_sn . time(),
            'isAccept'      => 'false'
        );

        if ($cancel_reason) $submit['cancelReason'] = $cancel_reason;
        if ($operator_id)   $submit['operatorId'] 	= $operator_id;
        if ($operator_name) $submit['operatorName'] = $operator_name;
        if ($product_code)  $submit['productCode'] 	= $product_code;
        
		if ($merchant_order_biz_noe) {
			$submit['merchantOrderBizNo'] = $merchant_order_biz_noe;
		}
		
        $submit['sign'] = $this->signature($submit);
        $result = $this->_execute($submit);

        return json_decode($result);
    }

	function signature(array $fields)
    {
        # sort for key
        ksort($fields);

        $clientSignatureString = '';
        foreach ($fields as $key => $value)  {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }

        $clientSignatureString = substr($clientSignatureString, 0, -1);
        $clientSignatureString = trim($clientSignatureString) . $this->secretKey;
		
        return md5($clientSignatureString);
    }
	 
	function encrypt($entity, $key)
    {
        $post = array(
			'json' => json_encode(array(
				'resoucesStr' => base64_encode($entity),
				'appName' => $key
			))
		);
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::SERVICE_ENCRYPT_URL);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $result = curl_exec($ch);
		$result = json_decode($result);
		
        curl_close($ch);
        
        return trim($result->encryptStr);
    }
	
    protected function _execute($submit)
    {
        $url = $this->debug ? self::SERVICE_GATEWAY_DEV_URL : self::SERVICE_GATEWAY_URL;
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $submit);
        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    // 表单提交订单信息
    protected function submit_pay_form($allOptions)
    {
        $uri = $this->debug ? self::SERVICE_GATEWAY_DEV_URL : self::SERVICE_GATEWAY_URL;

        $html .= '<html><head><meta http-equiv="Content-Type" content="textml; charset=UTF-8" /></head>
            <body onLoad="document.dinpayForm.submit();">You will be redirected to Yjpay in a few seconds.<form name="dinpayForm" id="dinpayForm" method="POST" action="'.$uri.'" >';
        foreach($allOptions as $k => $v)
        {
            $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
        }
        $html .= '</form></body><ml>';
        return $html;
    }

}

class esp_order
{
    var $outOrderNo, $ipAddress, $deviceFingerprintId, $webSite,$acquiringType;

    var $billToCity, $billToCountry, $billToState, $billToPostalCode, $billToEmail,
        $billToFirstName, $billToLastName, $billToPhoneNumber, $billtoStreet;

    var $shipToCity, $shipToCountry, $shipToFirstName, $shipToLastName,
        $shipToEmail, $shipToPhoneNumber, $shipToPostalCode, $shipToState, $shipToStreet1;

    var $logisticsFee, $logisticsMode, $amountLoc, $currencyCode;

    var $customerEmail, $customerPhoneNumber;

    var $merchantEmail, $merchantName;

    var $orderType, $cardType, $cardNo, $cvv,
        $cardHolderFirstName, $cardHolderLastName, $expirationDate;

    var $remark;

    function service_parameters()
    {
        return array(
            // orderno
            // 'outOrderNo'     => $this->outOrderNo,
            // OrderAttachedDetails json字符串
            'ipAddress'         => $this->ipAddress,
			'billToCountry' 	=> $this->billToCountry,
			'billToState' 		=> $this->billToState,
            'billToCity' 		=> $this->billToCity,
            'billtoStreet' 	=> $this->billtoStreet,
            'billToPostalCode' 	=> $this->billToPostalCode,
			
            'billToFirstName' 	=> $this->billToFirstName,
            'billToLastName' 	=> $this->billToLastName,
            'billToPhoneNumber' => $this->billToPhoneNumber,
            'billToEmail' 		=> $this->billToEmail,          
			
			
			'shipToCountry' 	=> $this->shipToCountry,
			'shipToState' 		=> $this->shipToState,
            'shipToCity' 		=> $this->shipToCity,
			'shipToStreet' 	    => $this->shipToStreet1,
			'shipToPostalCode' 	=> $this->shipToPostalCode,
			
            'shipToFirstName' 	=> $this->shipToFirstName,
            'shipToLastName' 	=> $this->shipToLastName,
            'shipToPhoneNumber' => $this->shipToPhoneNumber,
            'shipToEmail' 		=> $this->shipToEmail,            
			
            'customerPhoneNumber' => $this->customerPhoneNumber,
			'customerEmail' => $this->customerEmail,

            'logisticsFee' => $this->logisticsFee,
            'logisticsMode' => $this->logisticsMode,
            		
			'merchantEmail' => $this->merchantEmail,
            'merchantName' => $this->merchantName,

			// 基本参数
			'basicData'     =>array(
                                // 'currencyCode' => $this->currencyCode,
                                'currency' => $this->currencyCode,
                                'orderAmount' => $this->amountLoc,           
                                'webSite'               => $this->webSite,
                                // 'deviceFingerprintId'   => $this->deviceFingerprintId,
                                'merchOrderNo'  => $this->outOrderNo,
                                'orderNo'       => $this->outOrderNo . time(),
                                // 'orderType' => 'MOTO_EDC',
                                'remark' => $this->remark,
                                'acquiringType' => $this->acquiringType,
                            ),
            
            // 'exVersion' => 'Ecs-3.0.0',
			
			// 'cardHolderFirstName' => $this->cardHolderFirstName,
            // 'cardHolderLastName' => $this->cardHolderLastName,
			
			// 'cardType' => $this->cardType,
            // 'cardNo' => $this->cardNo,
            // 'cvv' => $this->cvv,           
            // 'expirationDate' => $this->expirationDate,
        );
    }


}


class esp_goods
{
    var $goodsNumber, $goodsName, $goodsCount;

    var $itemSharpProductCode, $itemSharpUnitPrice;
}


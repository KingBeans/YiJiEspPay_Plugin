<?php
class ModelPaymentMyespay extends Model {
	function submitForm()
    {
        //require_once(DIR_SYSTEM . 'vendor/espay_api.php');
        $orderID = $this->session->data['order_id'];
        $config  = $this->config;
        $this->load->model('checkout/order');
        $order    = $this->model_checkout_order->getOrder($orderID);              

         // var_dump($order);exit();  
        $host = 'https://'.$_SERVER['HTTP_HOST'];
        $partner_id = $this->config->get('espay_partner_id');
        $session_id = session_id();

        // $currency = $this->config->get('espay_currency');
        // 12/27 currency 修复
        $currency = $order['currency_code'];
        $merchantEmail=$this->config->get('espay_merchant_email');;//商户邮箱
	    $merchantName=$this->config->get('espay_merchant_name');;//商户名

        $data = array (
		//基本参数
		  // 'orderNo' =>'YJPay'.date('YmdHis').rand(0,9) ,
		  // 'merchOrderNo'=>$orderID,
          'orderNo' =>'Yjpay'.date('YmdHis').rand(0,9) ,
          'merchOrderNo'=>date('YmdHis').rand(10,99) . '-'.$orderID,
		  'version' => '1.0',
		  'protocol' => 'httpPost',
		  'service' => 'cardAcquiringCashierPay',
		  'notifyUrl'=> $this->url->link('payment/espay_notify'),
		  'returnUrl'=> $this->url->link('checkout/ccpay_success'),
		  'signType' => 'MD5',
		  'partnerId' => $partner_id,
		//业务参数
            // yoko hk modify
			'goodsInfoOrders' => array(), //商品列表
			'attachDetails' => array(),//订单扩展信息

			'userId' => $partner_id,	//
			'currency' => $currency,//原始订单币种
			'amount' => $order['total'],
			'webSite' => $_SERVER['HTTP_HOST'],//所属网站 阿法贝迪
		    'deviceFingerprintId' => $session_id, //设备指纹
			'memo' => '',//备注
			'orderAmount' => $order['total'],//原始订单金额
			'acquiringType' => 'CRDIT',//收单类型,CRDIT：信用卡；YANDEX： 网银方式
			// 'orderExtends' => '',//系统扩展字段，json存储
			// 'merchOrderNo'=>date('YmdHis').rand(100,999), //新增参数，商户外部订单号
    	);

        //货物信息
        $products = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$orderID . "'");
        $espItems = array();
        
        foreach ($products->rows as $row) {
            $goods = array();
            $goods['goodsNumber']          = $row['product_id'];
            $goods['goodsName']            = htmlspecialchars_decode($row['name']);
            $goods['goodsCount']           = $row['quantity'];
            $goods['itemSharpProductCode'] = $row['model'];
            $goods['itemSharpUnitPrice']   = $row['price'];          
            $goodsInfoList[] = $goods;         
        }                                 
        //账单、收货等其它信息
        // $order_json = json_encode($order,true);
        // file_put_contents('D:/phpworkspace/opencart/logs/opencart2.0/opencart2.0.log', 'order1:' . $order_json);

        // yoko debug version2.0 modify
        // if(!empty($order['firstname'])){
        //             $shipNameArr = explode(' ', $order['firstname']);
        //             $paymentNameArr = explode(' ', $order['firstname']);
        //             // $name_json = json_encode($paymentNameArr,true);                   
        //             // file_put_contents('D:/phpworkspace/opencart/logs/opencart_namedebug2.3.0.2.log', $name_json);

        //             empty($order['shipping_firstname']) ? $order['shipping_firstname']= $shipNameArr[0] : $order['shipping_firstname']= $shipNameArr[1];
        //             $order['shipping_lastname']  = $paymentNameArr[0];


        //             empty($order['payment_firstname']) ? $order['payment_firstname'] = $paymentNameArr[0] :  $order['payment_firstname'] = $paymentNameArr[1];   
        //             $order['payment_lastname']   = $paymentNameArr[0];
                    
        //         }

        if (isset($this->session->data['shipping_method'])) {
                $shippingMethod = $this->session->data['shipping_method'];

                $shipToFirstName  = $order['shipping_firstname'];
                $shipToLastName   = $order['shipping_lastname'];
                $shipToCountry    = $order['shipping_iso_code_3'];
                $shipToState      = $order['shipping_zone'];
                $shipToCity       = $order['shipping_city'];
                $shipToStreet1    = $order['shipping_address_1'];
                $shipToPostalCode = $order['shipping_postcode'];

            } else {
                $shippingMethod = array('cost' => 0, 'title' => 'None');

                $shipToFirstName  = $order['payment_firstname'];
                $shipToLastName   = $order['payment_lastname'];
                $shipToCountry    = $order['payment_iso_code_3'];
                $shipToState      = $order['payment_zone'];
                $shipToCity       = $order['payment_city'];
                $shipToStreet1    = $order['payment_address_1'];
                $shipToPostalCode = $order['payment_address_1'];
            }


        $orderDetail = array (
        	'ipAddress' => $order['ip'],	//IP地址
        	'billtoCountry' => $order['payment_iso_code_3'],	//账单国家
        	'billtoState' => $order['payment_zone'],	//账单州
        	'billtoCity' => $order['payment_city'],	//账单城市
        	'billtoPostalcode' => $order['payment_postcode'],	//账单邮编
        	'billtoEmail' => $order['email'],	//账单邮箱

        	'billtoFirstname' => $order['payment_firstname'],	//接收账单人员名
        	'billtoLastname' => $order['payment_lastname'],	//接收账单人员姓
        	'billtoPhonenumber' => $order['telephone'],	//账单电话
        	'billtoStreet' => $order['payment_address_1'],	//账单街道

        	'shiptoCity' => $shipToCity,	//收货城市
        	'shiptoCountry' => $shipToCountry,	//收货国家
        	'shiptoFirstname' =>$shipToFirstName,	//收货人姓
        	'shiptoLastname' => $shipToLastName,	//收货人名
        	'shiptoEmail' => $order['email'],	//收货邮箱
        	'shiptoPhonenumber' => $order['telephone'],	//收货电话
        	'shiptoPostalcode' => $shipToPostalCode,	//收货邮编
        	'shiptoState' => $shipToState ,	//收货州
        	'shiptoStreet' => $shipToStreet1,	//收货街道

        	'logisticsFee' => $shippingMethod['cost'],	//物流费
        	'logisticsMode' => $shippingMethod['title'],	//物流方式
        	'cardType' => 'Visa',	//卡类型
        	'customerEmail' => $order['email'],	//购买者邮箱
        	'customerPhonenumber' => $order['telephone'],	//购买者电话
        	'merchantEmail' => $merchantEmail,	//商户邮箱
        	'merchantName' => $merchantName,	//商户名
        	'addressLine1' => '',	//卡地址1
        	'addressLine2' => ''	//卡地址2
        );
        $data['goodsInfoOrders']	 =  json_encode($goodsInfoList);
        $data['attachDetails']	 = json_encode($orderDetail);
        $arr = $this->sign($data);
        $this->cache->set('espay_submit', $arr);
        $html = $this->rendor($arr);
        return $html;
    }

    private function sign($data)
    {
        $secret_key = $this->config->get('espay_secret_key');
        ksort($data);
        $signSrc="";
        foreach($data as $k=>$v)
        {
            if(empty($v)||$v==="")
                unset($data[$k]);
            else
                $signSrc.= $k.'='.$v.'&';
        }
        $signSrc = trim($signSrc, '&').$secret_key;

        if($data['signType']==="MD5")  $data['sign'] = md5($signSrc);
        return $data;
    }

    // form submit order
    private function rendor($data)
    {
        $debug = $this->config->get('espay_debug');
        $uri = 'http://openapi.yjpay.hk/gateway.html';
        $js= sprintf('<script>setTimeout(function(){%s},%s);</script>','document.dinpayForm.submit()',1200);

        if($debug=='debug')
        {
            $uri = 'https://hkopenapitest.yiji.com/gateway.html';
        }

        $html = $js.'<form name="dinpayForm" id="dinpayForm" method="POST" action="'.$uri.'" target="submitIframe">';

    	foreach($data as $k => $v){
    		$html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';

    	}

    	$html .='<input style="display:none; type="submit" value="提交"/></form><iframe src="" width="85%" height="498px"  name="submitIframe" style="margin-left:104px;" frameborder=0 scrolling="auto" />abc</iframe>';
    	return $html;
    }
}
<?php

class ModelPayment extends Model {

    public function getMethod($address, $total) {
        $this->load->language('payment/espay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('espay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('espay_geo_zone')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'espay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('espay_sort_order')
            );
        }

        return $method_data;
    }

    public function espay($address, $total) {
        $this->load->language('payment/espay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('espay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('espay_geo_zone')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'espay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('espay_sort_order')
            );
        }

        return $method_data;
    }

    # easy status
    const STATUS_NEW         = 1;
    const STATUS_AUTHORIZING = 2;
    const STATUS_AUTHORIZED  = 3;
    const STATUS_PAYED       = 4;
    const STATUS_REFUND      = 5;
    const STATUS_CANCEL      = 6;

    const NEED_AUTHORIZE = 1;

    const INSERT_NEW_SQL = <<<EOF
        INSERT INTO :prefixespay(`order_id`,`status`,`order_fee`,`currency`,`order_date`,`note`)
                    VALUES(:order_id,:status,:order_fee,':currency',:order_date,':note')
EOF;

    public function createNew($orderID, $orderFee, $currency, $date = false, $note = '') {
        if ($date && is_string($date)) {
            $date = strtotime($date);
        } else if ($date == false) {
            $date = time();
        }

        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':order_fee', ':order_date', ':note', ':currency'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_NEW, $orderFee, $date, $note, $currency
        ), self::INSERT_NEW_SQL));
    }


    function myespay()
    {
        //require_once(DIR_SYSTEM . 'vendor/espay_api.php');
        $orderID = $this->session->data['order_id'];
        $config  = $this->config;
        $this->load->model('checkout/order');
        $order    = $this->model_checkout_order->getOrder($orderID);  

        // $order_json = json_encode($order,true);
        // file_put_contents('D:/phpworkspace/opencart/logs/opencart2.3.0.2.log', $order_json);
        
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        $partner_id = $this->config->get('espay_partner_id');
        $session_id = session_id();

        $currency = $this->config->get('espay_currency');
        $merchantEmail=$this->config->get('espay_merchant_email');;//商户邮箱
        $merchantName=$this->config->get('espay_merchant_name');;//商户名

        $data = array (
        //基本参数
          
          'orderNo' =>'Yjpay'.date('YmdHis').rand(0,9) ,
          'merchOrderNo'=>date('YmdHis').rand(10,99) . '-'.$orderID,
          'version' => '1.0',
          'protocol' => 'httpPost',
          'service' => 'espOrderPay',
          'notifyUrl'=> $this->url->link('extension/payment/espay_notify'),
          // 'notifyUrl'=> $_SERVER['HTTP_HOST'].'/espay_notify.php',
          // 'notifyUrl'=> $host.'/espay_notify.php',
          'returnUrl'=> $this->url->link('checkout/ccpay_success'),
          'signType' => 'RSA',
          'partnerId' => $partner_id,
        //业务参数
            'goodsInfoList' => array(), //商品列表
            'orderDetail' => array(),//订单扩展信息
            'userId' => $partner_id,    //
            'currency' => $currency,//原始订单币种
            'amount' => $order['total'],
            'webSite' => $_SERVER['HTTP_HOST'],//所属网站
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
        // yoko debug
        if(!empty($order['payment_fullname'])){
                    $shipNameArr = explode(' ', $order['shipping_fullname']);
                    $paymentNameArr = explode(' ', $order['payment_fullname']);
                    $name_json = json_encode($paymentNameArr,true);                   
                    // file_put_contents('D:/phpworkspace/opencart/logs/opencart_namedebug2.3.0.2.log', $name_json);

                    empty($order['shipping_firstname']) ? $order['shipping_firstname']= $shipNameArr[0] : $order['shipping_firstname']= $shipNameArr[1];
                    $order['shipping_lastname']  = $paymentNameArr[0];


                    empty($order['payment_firstname']) ? $order['payment_firstname'] = $paymentNameArr[0] :  $order['payment_firstname'] = $paymentNameArr[1];   
                    $order['payment_lastname']   = $paymentNameArr[0];
                    
                }
        empty($order['payment_address_1']) ? $order['payment_address_1'] = $order['payment_address'] : $order['payment_address_1'] = 'test address 1';

        empty($order['shipping_address_1']) ? $order['shipping_address_1'] = $order['shipping_address'] : $order['shipping_address_1'] = 'test address 1';

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
            'ipAddress' => $order['ip'],    //IP地址
            'billtoCountry' => $order['payment_iso_code_3'],    //账单国家
            'billtoState' => $order['payment_zone'],    //账单州
            'billtoCity' => $order['payment_city'], //账单城市
            'billtoPostalcode' => $order['payment_postcode'],   //账单邮编
            'billtoEmail' => $order['email'],   //账单邮箱

            'billtoFirstname' => $order['payment_firstname'],   //接收账单人员名
            'billtoLastname' => $order['payment_lastname'], //接收账单人员姓
            'billtoPhonenumber' => $order['telephone'], //账单电话
            'billtoStreet' => $order['payment_address_1'],  //账单街道

            'shiptoCity' => $shipToCity,    //收货城市
            'shiptoCountry' => $shipToCountry,  //收货国家
            'shiptoFirstname' =>$shipToFirstName,   //收货人姓
            'shiptoLastname' => $shipToLastName,    //收货人名
            'shiptoEmail' => $order['email'],   //收货邮箱
            'shiptoPhonenumber' => $order['telephone'], //收货电话
            'shiptoPostalcode' => $shipToPostalCode,    //收货邮编
            'shiptoState' => $shipToState , //收货州
            'shiptoStreet' => $shipToStreet1,   //收货街道

            'logisticsFee' => $shippingMethod['cost'],  //物流费
            'logisticsMode' => $shippingMethod['title'],    //物流方式
            'cardType' => 'Visa',   //卡类型
            'customerEmail' => $order['email'], //购买者邮箱
            'customerPhonenumber' => $order['telephone'],   //购买者电话
            'merchantEmail' => $merchantEmail,  //商户邮箱
            'merchantName' => $merchantName,    //商户名
            'addressLine1' => '',   //卡地址1
            'addressLine2' => ''    //卡地址2
        );
        $data['goodsInfoList']   =  json_encode($goodsInfoList);
        $data['orderDetail']     = json_encode($orderDetail);
        $arr = $this->sign($data);
        $this->cache->set('espay_submit', $arr);
        $this->rendor($arr);
    }

    private function sign($data)
    {
        $certificate_cipher = $this->config->get('espay_certificate_cipher');
        ksort($data);
        $signSrc="";
        foreach($data as $k=>$v)
        {
            if(empty($v)||$v==="")
                unset($data[$k]);
            else
                $signSrc.= $k.'='.$v.'&';
        }

        if($data['signType']==="MD5") {
            $signSrc = trim($signSrc, '&').$certificate_cipher;
            $data['sign'] = md5($signSrc);
        }elseif ($data['signType']==="RSA"){
            $signSrc = trim(substr($signSrc, 0, -1));
            $pfxPath = DIR_SYSTEM.$this->config->get('espay_partner_id').'.pfx';
            $pkcs12 = file_get_contents($pfxPath);
            $keyPass = $certificate_cipher;
            if(!file_exists($pfxPath)){
                return false;
            }
            $signedMsg = "";
            if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
                $privateKey = $certs['pkey'];
                if (openssl_sign($signSrc, $signedMsg, $privateKey)) {
                    $data['sign'] = base64_encode($signedMsg);
                } else {
                    $data['sign'] = '0';
                }
            }
        }else{

        }
        return $data;
    }

    private function rendor($data)
    {
        $debug = $this->config->get('espay_debug');
        $uri = 'https://api.yiji.com/gateway.html';
        $js= sprintf('<script>setTimeout(function(){%s},%s);</script>','document.dinpayForm.submit()',1200);
        if($debug=='debug')
        {
            $uri = 'https://openapi.yijifu.net/gateway.html';
        }
        $html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'.$js.'</head><body><form name="dinpayForm" id="dinpayForm" method="POST" action="'.$uri.'" >';
        foreach($data as $k => $v){
            $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';

        }
        $html .= 'Please Waiting ...';
        $html .='<input style="display:none;" type="submit" value="提交"/></form></body></html>';
        echo $html;
    }

}
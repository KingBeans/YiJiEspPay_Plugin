<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/11/21
 * Time: 15:00
 */

class ControllerExtensionPaymentYjpay extends Controller
{
    public function index(){
        if($this->config->get('payment_yjpay_style') == 'embed'){
            $this->load->language('extension/payment/yjpay');
            $data['payment_url'] = $this->url->link('extension/payment/yjpay/payForOrder', '', 'SSL');
            if (!$this->session->data['order_id']) {
                return false;
            }

            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            // pass shipping info to paypal if set
            if ($this->cart->hasShipping()) {
                $data['customer_shipping_address'] = array(
                    'name'			=> addslashes($order_info['shipping_firstname']) . ' ' . addslashes($order_info['shipping_lastname']),
                    'line_1'		=> addslashes($order_info['shipping_address_1']),
                    'line_2'		=> addslashes($order_info['shipping_address_2']),
                    'city'			=> addslashes($order_info['shipping_city']),
                    'state'			=> addslashes($order_info['shipping_zone_code']),
                    'post_code'		=> addslashes($order_info['shipping_postcode']),
                    'country_code' 	=> addslashes($order_info['shipping_iso_code_2']),
                    'phone'			=> addslashes($order_info['telephone']),
                );
            }

            $data['form_styles'] = json_encode("{
		    'input': { 'font-size': '12px', 'font-family': 'Source Sans Pro, sans-serif', 'color': '#7A8494' },
		    'input.invalid': { 'color': 'red' },
		    'input.valid': { 'color': 'green' }
	  	    }");
            $today = getdate ();
            $expires_year = $expires_month = array();
            for($i = $today ['year']; $i < $today ['year'] + 20; $i ++) {
                $expires_year[] = strftime ( '%Y', mktime ( 0, 0, 0, 1, 1, $i )
                );
            }
            for($i = 1; $i < 13; $i ++) {
                $expires_month [] = strftime ( '%m', mktime ( 0, 0, 0, $i, 1, 2000 ));
            }
            $data['expires_year'] = $expires_year;
            $data['expires_month'] = $expires_month;
            return $this->load->view('extension/payment/yjpay_embed',$data);
        }else{
            $data['button_confirm'] = 'Continue';//$this->language->get('button_confirm');
            $data['action'] = $this->url->link('extension/payment/yjpay/submit');
            return $this->load->view('extension/payment/yjpay', $data);
        }
    }

    public function submit(){

        $gatewayUrl = $this->config->get('payment_yjpay_debug') ? 'http://openapi.yijifu.net/gateway.html' : 'https://api.yiji.com/gateway.html' ;

        $getStr = $gatewayUrl.'?';

        if($this->config->get('payment_yjpay_style') == 'embed'){
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['action'] = $this->url->link('extension/payment/yjpay/payForOrder', '', 'SSL');
            return $this->response->setOutput($this->load->view('extension/payment/yjpay_embed',$data));
        }
        //组装参数
        $requestData = $this->setParameters();
        $data['form_params'] = $this->model_extension_payment_yjpay->getSign($requestData,$this->config->get('payment_yjpay_secretKey'));

        foreach ($data['form_params'] as $key => $value){
            $getStr .= $key.'='.$value.'&';
        }

//        $getStr = trim($getStr,'&');
        $this->postData($gatewayUrl,$data['form_params']);

    }
    public function payForOrder(){

        if (!is_array($this->request->post) || is_null($this->request->post)){
            //报错
        }
        //组装参数
        $requestData = $this->setParameters();
        $cardExpireYear = $this->request->post['year'];
        $cardExpireMonth = $this->request->post['month'];
        $securityCode =  $this->request->post['securityCode'];
        $cardNumber = $this->request->post['cardNumber'];
        $this->load->model('extension/payment/yjpay');
        $gatewayUrl = $this->config->get('payment_yjpay_debug') ? 'http://openapi.yijifu.net/gateway.html' : 'https://api.yiji.com/gateway.html' ;
        $requestData['cardNo'] = $this->model_extension_payment_yjpay->signPublicKey($cardNumber.$requestData['orderNo']);
        $requestData['cvv'] = $this->model_extension_payment_yjpay->signPublicKey($securityCode.$requestData['orderNo']);
        $requestData['expirationDate'] = sprintf('%s%s', substr($cardExpireYear,-2), $cardExpireMonth);

        $postData = $this->model_extension_payment_yjpay->getSign($requestData,$this->config->get('payment_yjpay_secretKey'));
        $result = (array)$this->curlPost($gatewayUrl,$postData);
        $this->handleResult($result);
        exit;
    }
    public function notify(){

        $this->load->model('extension/payment/yjpay');

//        $notifyData = $this->request->post;
        if(!$this->verifySign($this->request->get)){
            exit('fail');
        }

        $this->load->model('checkout/order');
        $order_id = trim($this->request->post['merchOrderNo'],'Q');
        $order_status = trim($this->request->post['status']);

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $order_status_id = '';

        if($order_info){

            switch($order_status){
                case 'success':
                    $order_status_id = $this->config->get('payment_yjpay_success_status_id');
                    break;
                case 'fail':
                    $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
                    break;
                case 'authorizing':
                    $order_status_id = $this->config->get('payment_yjpay_authorizing_status_id');
                    break;
                case 'authorizingTrue':
                    $order_status_id = $this->config->get('payment_yjpay_success_status_id');
                    break;
                case 'authorizingFail':
                    $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
                    break;
            }

            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id , 'Yjpay: ' . trim($this->request->post['description']),true);
            echo 'success';
        }else{
            echo 'fail';
        }
    }

    public function returnUrl(){
        $this->load->model('extension/payment/yjpay');
        $order_status_id = $description = '';
        $message = false;
//        $returnData = $this->request->get;
        $order_id = trim($this->request->get['merchOrderNo'],'Q');
        if (($this->request->get['success'])){
//            if(!$this->verifySign($returnData)){
//                exit('fail');
//            }
            $this->load->model('checkout/order');
            $order_status = trim($this->request->get['status']);

            $order_info = $this->model_checkout_order->getOrder( $order_id);
            if($order_info){

                switch($order_status){
                    case 'success':
                        $order_status_id = $this->config->get('payment_yjpay_success_status_id');
                        $message = true;
                        $description = trim($this->request->get['description']);
                        break;
                    case 'fail':
                        $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
                        $message = false;
                        $description = trim($this->request->get['description']);
                        break;
                    case 'authorizing':
                        $order_status_id = $this->config->get('payment_yjpay_authorizing_status_id');
                        $message = true;
                        $description = trim($this->request->get['description']);
                        break;
                    case 'processing':
                        $order_status_id = $this->config->get('payment_yjpay_processing_status_id');
                        $message = true;
                        $description = trim($this->request->get['description']);
                        break;
                }
            }
        }else{
            $message = false;
            $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
            $description = $this->request->get['resultMessage'];
        }
        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id , 'Yjpay: ' . $description,false);
        if($message){
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }else{
            $this->session->data['order_id'] = $order_id;
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

    }

    public function postData($url,$post){
//
        $html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body onLoad="document.dinpayForm.submit();"><form name="dinpayForm" id="dinpayForm" method="POST" action="'.$url.'" >';
        foreach($post as $k => $v){
            $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
            $html .='<input type="hidden" name="'.$k.'" value="'.str_replace("+"," ",urlencode($v)).'"/>';
        }
        $html .='</form></body></html>';
//        file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"\r\n".date('Y-m-d H:i:s ')."HTML内容:".$html,FILE_APPEND);
        echo $html;
    }

    public function verifySign($pastData){
        if(!isset($pastData['sign'])){
            exit('fail');
        }
        ksort($pastData);

        $waitingForSign = '';
        foreach ($pastData as $key => $value) {
            if('sign'===$key){
                continue;
            }
            $waitingForSign .= ($key . '=' . $value . '&');
        }
        $waitingForSign = trim(substr($waitingForSign, 0, -1));
        $signValue = base64_decode($pastData['sign']);
        //验证
        if ($this->config->get('payment_yjpay_debug')){
            $cerPath = __DIR__.'/../../../../yiji.snet.cer';
        }else{
            $cerPath = __DIR__.'/../../../../yiji.online.cer';
        }

        $certificateCAcerContent = file_get_contents($cerPath);
        $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
        $pubkeyid = openssl_get_publickey($certificateCApemContent);
        $verifyResult = false;
        if($pubkeyid){
            // state whether signature is okay or not
            $verifyResult = openssl_verify($waitingForSign, $signValue, $pubkeyid);
            openssl_free_key($pubkeyid);
        }
        return $verifyResult;
    }

    public function setParameters(){
        if($this->session->data['payment_method']['code'] != 'yjpay' ){
            $this->response->redirect($this->url->link('checkout/checkout'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('checkout/order');
        $this->load->model('localisation/country');
        $this->load->model('extension/payment/yjpay');
        $this->load->language('extension/payment/yjpay');

        $order_id = $this->session->data['order_id'];

        $order_info = $this->model_checkout_order->getOrder($order_id);
        $partner_id = $this->config->get('payment_yjpay_partnerId');

        $country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

        $shipFree = array(
            'cost' => '0',
            'title' => 'free'
        );
        $shippingMethod = isset($this->session->data['shipping_method']) ? $this->session->data['shipping_method'] : $shipFree;

        $request =array();
        session_start();

        $request['orderNo'] = 'Q'.date('YmdHis').mt_rand(100000,999999);
        $request['merchOrderNo'] = 'Q'.trim($order_info['order_id']);
        $request['protocol'] = $this->config->get('payment_yjpay_style') == 'embed' ? 'httpPost' : 'httpGet' ;
        if($this->config->get('payment_yjpay_style') == 'embed'){
            $request['service'] = 'espPciOrderPay';
            $request['version'] = '3.0';
        }else{
            $request['service'] = 'espOrderPay';
            $request['version'] = '1.0';
        }

        $request['notifyUrl'] = $this->url->link('extension/payment/yjpay/notify', '', 'SSL');
        $request['returnUrl'] = $this->url->link('extension/payment/yjpay/returnUrl', '', 'SSL');
        $request['signType'] =  'RSA';
        $request['partnerId'] = $partner_id;

        $request['userId'] = $partner_id;
        $request['currency'] = $order_info['currency_code'];//原始订单币种
        $request['amount'] = round($order_info['total'] * $order_info['currency_value'],2);
//        $request['orderAmount'] = round($order_info['total'],2);
        $request['acquiringType'] = 'CRDIT';
        $request['webSite'] = $_SERVER['HTTP_HOST'];//所属网站
        $request['deviceFingerprintId'] = session_id(); //设备指纹
        if ($order_info['comment'] != null){
            $request['memo'] = $order_info['comment'];//备注
        }

        $request['language'] = $this->config->get('payment_yjpay_language');
        $orderDetailData = array(
            "ipAddress"=>$order_info['ip'],
            "billtoStreet" => $order_info['payment_address_1'] .' '.$order_info['payment_address_2'],
            "billtoPostalcode" => $order_info['payment_postcode'],
            "billtoCity" => $order_info['payment_city'],
            "billtoState" => $order_info['payment_zone_code'],
            "billtoCountry" => $country_info['iso_code_2'],
            "billtoEmail" => $order_info['email'],
            'billtoFirstname' => $order_info['payment_firstname'],	//接收账单人员名
            'billtoLastname' => $order_info['payment_lastname'],	//接收账单人员姓
            'billtoPhonenumber' => $order_info['telephone'],	//账单电话

            'shiptoCity' => $order_info['shipping_city'] ? $order_info['shipping_city'] : $order_info['payment_city'],	//收货城市
            'shiptoCountry' => $order_info['shipping_iso_code_2'] ? $order_info['shipping_iso_code_2'] : $country_info['iso_code_2'],	//收货国家
            'shiptoFirstname' =>$order_info['shipping_firstname'] ? $order_info['shipping_firstname'] : $order_info['payment_firstname'],	//收货人姓
            'shiptoLastname' => $order_info['shipping_lastname'] ? $order_info['shipping_lastname'] : $order_info['payment_lastname'],	//收货人名
            'shiptoEmail' => $order_info['email'],	//收货邮箱
            'shiptoPhonenumber' => $order_info['telephone'],	//收货电话
            'shiptoPostalcode' => $order_info['shipping_postcode'] ? $order_info['shipping_postcode'] : $order_info['payment_postcode'],	//收货邮编
            'shiptoState' => $order_info['shipping_zone_code'] ? $order_info['shipping_zone_code'] : $order_info['payment_zone_code'],	//收货州
            'shiptoStreet' => $order_info['shipping_address_1'] || $order_info['shipping_address_2'] ? $order_info['shipping_address_1'] .' '. $order_info['shipping_address_2'] : $order_info['payment_address_1'] .' '.$order_info['payment_address_2'],	//收货街道

            'logisticsFee' => round($shippingMethod['cost'] * $order_info['currency_value'],2),	//物流费
            'logisticsMode' => strtr($shippingMethod['title'],' ','-'),	//物流方式
            'customerEmail' => $order_info['email'],	//购买者邮箱
            'customerPhonenumber' => $order_info['telephone'],	//购买者电话
            'merchantEmail' => $this->config->get('payment_yjpay_email'),	//商户邮箱
            'merchantName' => $this->config->get('payment_yjpay_mname'),	//商户名
        );
        if ($this->config->get('payment_yjpay_style') == 'embed'){
            $request['cardHolderFirstName'] = $order_info['payment_firstname'];
            $request['cardHolderLastName'] = $order_info['payment_lastname'];
            $orderDetailData['cardType'] = $this->request->post['cardType'];
        }
        $request['orderDetail'] = json_encode($orderDetailData);

        $products = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_info['order_id'] . "'");
        $goodsInfoList = [];

        foreach ($products->rows as $row) {
            $goods = array();
            $goods['goodsNumber']          = $row['product_id'];
            $goods['goodsName']            = htmlspecialchars_decode($row['name']);
            $goods['goodsCount']           = $row['quantity'];
            $goods['itemSharpProductcode'] = $row['model'];
            $goods['itemSharpUnitPrice']   = $row['price'];
            $goodsInfoList[] = $goods;
        }

        $request['goodsInfoList'] = json_encode($goodsInfoList);

        return $request;
    }

    private function curlPost($requesturl,$getrequest){
        //curl模拟post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$requesturl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $getrequest);
        $result = curl_exec($ch);//运行curl
        curl_close($ch);
        $result = json_decode($result);
//        var_dump($result);
        return $result;
    }

    private function formPost($requesturl,$getrequest){
        //form表单模拟post请求
        $html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body onLoad="document.dinpayForm.submit();"><form name="dinpayForm" id="dinpayForm" method="POST" action="'.$requesturl.'" >';
        foreach($getrequest as $k => $v){
            $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
            $html .='<input type="hidden" name="'.$k.'" value="'.str_replace("+"," ",urlencode($v)).'"/>';

        }
        $html .='</form></body></html>';
        echo $html;
    }

    private function handleResult($resultData){
        $order_status_id = $description = $url = '';
        $message = false;
        $order_id = trim($resultData['merchOrderNo'],'Q');
        $this->load->model('checkout/order');
        if (($resultData['success'])){
//            if(!$this->verifySign($resultData)){
//                exit('fail');
//            }
            $this->load->model('checkout/order');
            $order_status = trim($resultData['status']);

            $order_info = $this->model_checkout_order->getOrder( $order_id);
            if($order_info){
                switch($order_status){
                    case 'success':
                        $order_status_id = $this->config->get('payment_yjpay_success_status_id');
                        $message = true;
                        $description = trim($resultData['description']);
                        break;
                    case 'fail':
                        $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
                        $message = false;
                        $description = trim($resultData['description']);
                        break;
                    case 'authorizing':
                        $order_status_id = $this->config->get('payment_yjpay_authorizing_status_id');
                        $message = true;
                        $description = trim($resultData['description']);
                        break;
                    case 'processing':
                        $order_status_id = $this->config->get('payment_yjpay_processing_status_id');
                        $message = true;
                        $description = trim($resultData['description']);
                        break;
                }
            }
        }else{
            $message = false;
            $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
            $description = $resultData['resultMessage'];
        }

        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id , 'Yjpay: ' . $description);
        if($message){
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }else{
            $this->session->data['order_id'] = $order_id;
            $this->session->data['error'] = $this->language->get('error_process_order');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }
}
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
        $data['button_confirm'] = 'Continue';//$this->language->get('button_confirm');

        $data['action'] = $this->url->link('extension/payment/yjpay/submit');

        return $this->load->view('extension/payment/yjpay', $data);
    }

    public function submit(){

        if($this->session->data['payment_method']['code'] != 'yjpay' ){
            $this->response->redirect($this->url->link('checkout/checkout'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('checkout/order');
        $this->load->model('localisation/country');
        $this->load->model('extension/payment/yjpay');

        $order_id = $this->session->data['order_id'];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $partner_id = $this->config->get('payment_yjpay_partnerId');

        $country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

        $shippingMethod = $this->session->data['shipping_method'];

        $request =array();

        $request['orderNo'] = 'Q'.date('YmdHis').mt_rand(100000,999999);
        $request['merchOrderNo'] = trim($order_info['order_id']);
        $request['version'] = '1.0';

        $request['protocol'] = $this->config->get('payment_yjpay_style') == 'embed' ? 'httpPost' : 'httpGet' ;

        $request['service'] = 'cardAcquiringCashierPay';
        $request['notifyUrl'] = $this->url->link('extension/payment/yjpay/notify', '', 'SSL');
        $request['returnUrl'] = $this->url->link('extension/payment/yjpay/returnUrl', '', 'SSL');
        $request['signType'] =  'MD5';
        $request['partnerId'] = $partner_id;

        $request['userId'] = $partner_id;
        $request['currency'] = $order_info['currency_code'];//原始订单币种
        $request['amount'] = round($order_info['total'],2);
        $request['orderAmount'] = round($order_info['total'],2);
        $request['acquiringType'] = 'CRDIT';
        $request['webSite'] = $_SERVER['HTTP_HOST'];//所属网站
        $request['deviceFingerprintId'] = session_id(); //设备指纹
        $request['memo'] = $order_info['comment'];//备注
        $request['language'] = $this->config->get('payment_yjpay_language');

        $request['attachDetails'] = json_encode(array(
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

            'shiptoCity' => $order_info['shipping_city'],	//收货城市
            'shiptoCountry' => $order_info['shipping_iso_code_2'],	//收货国家
            'shiptoFirstname' =>$order_info['shipping_firstname'],	//收货人姓
            'shiptoLastname' => $order_info['shipping_lastname'],	//收货人名
            'shiptoEmail' => $order_info['email'],	//收货邮箱
            'shiptoPhonenumber' => $order_info['telephone'],	//收货电话
            'shiptoPostalcode' => $order_info['shipping_postcode'],	//收货邮编
            'shiptoState' => $order_info['shipping_zone_code'],	//收货州
            'shiptoStreet' => $order_info['shipping_address_1'] .' '. $order_info['shipping_address_2'],	//收货街道

            'logisticsFee' => $shippingMethod['cost'],	//物流费
            'logisticsMode' => strtr($shippingMethod['title'],' ','-'),	//物流方式
            'customerEmail' => $order_info['email'],	//购买者邮箱
            'customerPhonenumber' => $order_info['telephone'],	//购买者电话
            'merchantEmail' => $this->config->get('payment_yjpay_email'),	//商户邮箱
            'merchantName' => $this->config->get('payment_yjpay_mname'),	//商户名
        ));

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

        $request['goodsInfoOrders'] = json_encode($goodsInfoList);

        $data = array();
        $data['form_params'] = $this->model_extension_payment_yjpay->getSign($request,$this->config->get('payment_yjpay_secretKey'));
        $gatewayUrl = $this->config->get('payment_yjpay_debug') ? 'https://hkopenapitest.yiji.com/gateway.html' : 'https://openapi.yjpay.hk/gateway.html' ;

        $getStr = $gatewayUrl.'?';



        if($this->config->get('payment_yjpay_style') == 'embed'){

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['action'] = $gatewayUrl;

            return $this->response->setOutput($this->load->view('extension/payment/yjpay_embed',$data));

        }

        foreach ($data['form_params'] as $key => $value){
            $getStr .= $key.'='.$value.'&';
        }

        $getStr = trim($getStr,'&');

        $this->response->redirect($getStr);

    }

    public function notify(){

        $this->load->model('extension/payment/yjpay');

        $notifyData = $this->request->post;

        if(!isset($notifyData['sign'])){
            exit('fail');
        }

        $sign = $notifyData['sign'];

        unset($notifyData['sign']);
        $data = $this->model_extension_payment_yjpay->getSign($notifyData,$this->config->get('payment_yjpay_secretKey'));
        $localSign = $data['sign'];

        if($localSign != $sign){
            exit('fail');
        }

        $this->load->model('checkout/order');
        $order_id = trim($this->request->post['merchOrderNo']);
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

        $notifyData = $this->request->get;

        if(!isset($notifyData['sign'])){
            exit('fail');
        }

        $sign = $notifyData['sign'];

        unset($notifyData['sign']);
        unset($notifyData['route']);

        $data = $this->model_extension_payment_yjpay->getSign($notifyData,$this->config->get('payment_yjpay_secretKey'));
        $localSign = $data['sign'];

        if($localSign != $sign){
            exit('fail');
        }

        $this->load->model('checkout/order');
        $order_id = trim($this->request->get['merchOrderNo']);
        $order_status = trim($this->request->get['status']);

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $order_status_id = '';
        $message = false;

        if($order_info){

            switch($order_status){
                case 'success':
                    $order_status_id = $this->config->get('payment_yjpay_success_status_id');
                    $message = true;
                    break;
                case 'fail':
                    $order_status_id = $this->config->get('payment_yjpay_fail_status_id');
                    $message = false;
                    break;
                case 'authorizing':
                    $order_status_id = $this->config->get('payment_yjpay_authorizing_status_id');
                    $message = true;
                    break;
                case 'processing':
                    $order_status_id = $this->config->get('payment_yjpay_processing_status_id');
                    $message = true;
                    break;
            }

            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id , 'Yjpay: ' . trim($this->request->get['description']),false);
        }

        if($message){
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }else{
            $this->session->data['order_id'] = $order_id;
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

    }

}
<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/31
 * Time: 15:14
 */
class ControllerPaymentYjpay extends Controller
{
    public function index(){
        //$this->load->language('payment/yjpay');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading']   = $this->language->get('text_loading');
        $data['action']         = $this->url->link('payment/yjpay/submit', '', 'SSL');
        return $this->load->view('default/template/payment/yjpay.tpl', $data);
    }

    public function submit(){
        if ($this->session->data['payment_method']['code'] != 'yjpay') {
            $this->response->redirect($this->url->link('checkout/checkout'));
        }
        $orderID = $this->session->data['order_id'];
        $config  = $this->config;
        $session_id = session_id();
        $this->load->model('checkout/order');
        $order    = $this->model_checkout_order->getOrder($orderID);
        $partner_id = $config->get('yjpay_partnerId');
        $currency = $order['currency_code'];
        $merchantEmail = $this->config->get('yjpay_email');//商户邮箱
        $merchantName = $this->config->get('yjpay_mname');//商户名

        $request_data = [
            'orderNo' =>'Q'.date('YmdHis').rand(10000,99999) ,
            'merchOrderNo'=>$orderID,
            'version' => '1.0',
            'protocol' => 'httpPost',
            'service' => 'cardAcquiringCashierPay',
            'notifyUrl'=> $this->url->link('payment/yjpay/notify', '', 'SSL'),
            'returnUrl'=> $this->url->link('checkout/yjpay', '', 'SSL'),
            'signType' => 'MD5',
            'partnerId' => $partner_id,

            'goodsInfoOrders' => array(), //商品列表
            'attachDetails' => array(),//订单扩展信息

            'userId' => $partner_id,	//
            'currency' => $currency,//原始订单币种
            'amount' => round($order['total'],2),
            'webSite' => $_SERVER['HTTP_HOST'],//所属网站
            'deviceFingerprintId' => $session_id, //设备指纹
            'memo' => $order['comment'],//备注
            'orderAmount' => round($order['total'],2),//原始订单金额
            'acquiringType' => 'CRDIT',//收单类型,CRDIT：信用卡；YANDEX： 网银方式
            'language' => $this->config->get('yjpay_language')
        ];

        $products = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$orderID . "'");
        $goodsInfoList = [];
        foreach ($products->rows as $row) {
            $goods = array();
            $goods['goodsNumber']          = $row['product_id'];
            $goods['goodsName']            = htmlspecialchars_decode($row['name']);
            $goods['goodsCount']           = $row['quantity'];
            $goods['itemSharpProductcode'] = $row['model'];
            $goods['itemSharpUnitPrice']   = round($row['price'],2);
            $goodsInfoList[] = $goods;
        }

        if (isset($this->session->data['shipping_method'])) {
            $shippingMethod = $this->session->data['shipping_method'];

            $shipToFirstName  = $order['shipping_firstname'];
            $shipToLastName   = $order['shipping_lastname'];
            $shipToCountry    = $order['shipping_iso_code_2'];
            $shipToState      = $order['shipping_zone'];
            $shipToCity       = $order['shipping_city'];
            $shipToStreet1    = $order['shipping_address_1'];
            $shipToPostalCode = $order['shipping_postcode'] ? $order['shipping_postcode'] : $order['payment_postcode'] ;

        } else {
            $shippingMethod = array('cost' => 0, 'title' => 'None');
            $shipToFirstName  = $order['payment_firstname'];
            $shipToLastName   = $order['payment_lastname'];
            $shipToCountry    = $order['payment_iso_code_2'];
            $shipToState      = $order['payment_zone_code'];
            $shipToCity       = $order['payment_city'];
            $shipToStreet1    = $order['payment_address_1'];
            $shipToPostalCode = $order['shipping_postcode'] ? $order['shipping_postcode'] : $order['payment_postcode'] ;
        }

        $orderDetail = array (
            'ipAddress' => $order['ip'],	//IP地址
            'billtoCountry' => $order['payment_iso_code_2'],	//账单国家
            'billtoState' => $order['payment_zone_code'],	//账单州
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
            'logisticsMode' => strtr($shippingMethod['title'],' ','-'),	//物流方式
            'customerEmail' => $order['email'],	//购买者邮箱
            'customerPhonenumber' => $order['telephone'],	//购买者电话
            'merchantEmail' => $merchantEmail,	//商户邮箱
            'merchantName' => $merchantName,	//商户名
        );

        $request_data['goodsInfoOrders'] = json_encode($goodsInfoList);
        $request_data['attachDetails'] = json_encode($orderDetail);
        $request_data = $this->getSign($request_data);
        $data['form'] = $this->Form($request_data);
        $db = $this->db;
        $this->yjpay_order_update($db,$orderID,$this->config->get('yjpay_start_status'));
        $this->yjpay_order_history($db,$orderID,$this->config->get('yjpay_start_status'),'YJPay pending');
        if($this->config->get('yjpay_style') == 'jump'){
            echo "<script src=\"/catalog/view/javascript/jquery/jquery-2.1.1.min.js\" type=\"text/javascript\"></script>".$data['form'];
        }

        if($this->config->get('yjpay_style') == 'embed'){
              $data['footer'] = $this->load->controller('common/footer');
              $data['header'] = $this->load->controller('common/header');
            $this->response->setOutput($this->load->view( 'default/template/payment/yjpay_embed.tpl', $data));
        }

    }


    public function return(){
        
    }

    public function notify(){
        $_post = $this->request->post;
        $sign = $_post['sign'];
        unset($_post['sign']);
        $data = $this->getSign($_post);
        if($data['sign'] == $sign){

            $db = $this->db;
            $order = $this->yjpay_order($db,$_post['merchOrderNo']);
            $dbStartResuelt = false;
            $dbHistoryResuelt = false;
            switch ($_post['status']){
                case 'success':
                    $dbStartResuelt = $this->yjpay_order_update($db,$_post['merchOrderNo'],$this->config->get('yjpay_success_status'));
                    $dbHistoryResuelt = $this->yjpay_order_history($db,$_post['merchOrderNo'],$this->config->get('yjpay_success_status'),'YJPay '.$_post['description']);
                    break;
                case 'fail':
                    $dbStartResuelt = $this->yjpay_order_update($db,$_post['merchOrderNo'],$this->config->get('yjpay_fail_status'));
                    $dbHistoryResuelt = $this->yjpay_order_history($db,$_post['merchOrderNo'],$this->config->get('yjpay_fail_status'),'YJPay '.$_post['description']);
                    break;
                case 'authorizing':
                    $dbStartResuelt = $this->yjpay_order_update($db,$_post['merchOrderNo'],$this->config->get('yjpay_authorizing_status'));
                    $dbHistoryResuelt = $this->yjpay_order_history($db,$_post['merchOrderNo'],$this->config->get('yjpay_authorizing_status'),'YJPay '.$_post['description']);
                    break;
                case 'authorizingTrue':
                    $dbStartResuelt = $this->yjpay_order_update($db,$_post['merchOrderNo'],$this->config->get('yjpay_success_status'));
                    $dbHistoryResuelt = $this->yjpay_order_history($db,$_post['merchOrderNo'],$this->config->get('yjpay_success_status'),'YJPay '.$_post['description']);
                    break;
                case 'authorizingFail':
                    $dbStartResuelt = $this->yjpay_order_update($db,$_post['merchOrderNo'],$this->config->get('yjpay_fail_status'));
                    $dbHistoryResuelt = $this->yjpay_order_history($db,$_post['merchOrderNo'],$this->config->get('yjpay_fail_status'),'YJPay '.$_post['description']);
                    break;
            }
            if($dbStartResuelt && $dbHistoryResuelt){
                echo "success";
            }
            echo "fail";
        }else{
            echo "get out!";
        }
    }


    private function yjpay_order($db, $orderID) {
        $result = $db->query('SELECT * FROM `' . DB_PREFIX . 'order` WHERE `payment_code` = "yjpay" AND `order_id`=' . intval($orderID));
        return $result->rows ? $result->rows[0] : false;
    }

    private function yjpay_order_update($db, $orderID, $status) {
        return $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . $status . " WHERE order_id = " . $orderID);
    }

    private function yjpay_order_history($db, $orderID, $statusID, $message) {
        $message = $db->escape($message);
        $date    = date('Y-m-d H:i:s', time());

        return $db->query("INSERT INTO `" . DB_PREFIX . "order_history`(`order_id`,`order_status_id`,`notify`,`comment`,`date_added`) VALUES({$orderID},{$statusID},0,'{$message}','{$date}')");
    }

    private function yjpay_setting($db) {
        $setting = array();
        $result  = $db->query("SELECT `key`,`value` FROM `" . DB_PREFIX . "setting` WHERE `key` like 'yjpay_%'");

        foreach ($result->rows as $row) {
            $setting[$row['key']] = $row['value'];
        }

        return $setting;
    }

    private function getSign($data){
        foreach ( $data as $k => $v ){
            if(empty($v)){
                unset($data[$k]);
            }
        }
        ksort($data);
        $waitSignString = '';
        foreach ( $data as $k => $v ) {
            $waitSignString .= '&'.$k.'='.$v;
        }
        $waitSignString = trim($waitSignString,'&').trim($this->config->get('yjpay_secretKey'));
        $data['sign'] = md5($waitSignString);
        return $data;
    }

    private function Form($data){

        $js= sprintf('<script>$(function(){%s});</script>','$("#YjpayForm").submit()');

        $debug = $this->config->get('yjpay_debug');

        $actionUrl = $debug ? 'https://hkopenapitest.yiji.com/gateway.html' : 'https://openapi.yjpay.hk/gateway.html' ;

        if($this->config->get('yjpay_style') == 'jump'){
            $target = '';
        }

        if($this->config->get('yjpay_style') == 'embed'){
            $target = 'target="iframe_a" ';
        }
        $form = '<form action=\''.$actionUrl.'\' method=\'post\' id=\'YjpayForm\' '.$target.' >';
        foreach ( $data as $k => $v ){
            $form .= '<input type=\'hidden\' name='.$k.' value=\''.$v.'\'>';
        }
        //$form .= '<button type="submit">submit</button>';
        $form .= '</form>'.$js;
        return $form;
    }

}
<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/3
 * Time: 11:32
 */
class CHECKOUT_YIJIESPPAY extends ISC_CHECKOUT_PROVIDER
{

    /**
     * 初始化支付控件的相关参数
     * CHECKOUT_YIJIESPPAY constructor.
     */
    public function __construct()
    {
        // Setup the required variables for the WorldPay checkout module
        parent::__construct();
        $this->_name = GetLang('YiJiPayName');
        $this->_image = "yijipay.jpg";
        $this->_description = GetLang('YiJiPayDesc');
        $this->_help = sprintf(GetLang('YiJiPayHelp'), $GLOBALS['ShopPathSSL']);
       $this->_height = 0;
    }

    /**
    * 初始化插件的设置项
    */
    public function SetCustomVars(){

        $this->_variables['displayname'] = array(
            "name" => "Display Name",
            "type" => "textbox",
            "help" => GetLang('DisplayNameHelp'),
            "default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['gateway'] = array(
            "name" => "Gateway Url",
            "type" => "textbox",
            "help" => GetLang('YiJiPayGatewayUrlHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['webSite'] = array(
            "name" => "Web Site",
            "type" => "textbox",
            "help" => "您的网站链接，请跟上主机名；列如：www.google.com；www为主机名，google.com为域名。",
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['partnerId'] = array(
            "name" => "Partner Id",
            "type" => "textbox",
            "help" => GetLang('YiJiPayPartnerIdHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['userId'] = array(
            "name" => "User Id",
            "type" => "textbox",
            "help" => GetLang('YiJiPayUserIdHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['secretKey'] = array(
            "name" => "Secret Key",
            "type" => "textbox",
            "help" => GetLang('YiJiPaySecretKeyHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['acquiringType'] = array(
            "name" => "Acquiring Type",
            "type" => "dropdown",
            "help" => "收单类型：信用卡方式、网银方式，例如：CRDIT：信用卡；YANDEX： 网银",
            "options"=>array(
                '信用卡 '=>'CRDIT',
                '网银'=>'YANDEX'
            ),
            "required" => true
        );


        $this->_variables['jumpMode'] = array(
            "name" => "Jump Mode",
            "type" => "dropdown",
            "help" => GetLang('YiJiPaySecretKeyHelp'),
            //"default" => $this->GetName(),
            "options"=>array(
                'Internal '=>0,
                'Jump'=>1
            ),
            "required" => false
        );

        $this->_variables['Language'] = array(
            "name" => "payment language",
            "type" => "dropdown",
            "help" => GetLang('payment language'),
                    //"default" => $this->GetName(),
            "options"=>array(
                'English'=>'en',
                'Japanese'=>'ja',
                'Deutsch'=>'de',
                'El español'=>'es',
                'Français'=>'fr'
            ),
            "required" => false
        );

        $this->_variables['Currency'] = array(
            "name" => "payment language",
            "type" => "dropdown",
            "help" => 'pelace select Currency',
                    //"default" => $this->GetName(),
            "options"=>array(
                'RMB'=>'CNY',
                'United States dollar'=>'USD',
                'Canadian dollar'=>'CAD',
                'Hong Kong dollars'=>'HKD',
                'Europe dollars'=>'EUR',
                'United Kingdom dollars'=>'GBP',
                'Japan\'s dollars'=>'JPY',
            ),
            "required" => false
        );

    }

    /**
    * 组织支付相关需要提交的参数，参数要求请阅读易极付相关的文档
    */
    public function TransferToProvider()
    {
        $requset_data = array();

        $requset_url = $this->GetValue('gateway');

        $requset_data['service'] = 'cardAcquiringCashierPay';
        $requset_data['version'] = '1.0';
        $requset_data['partnerId'] = $this->GetValue('partnerId');
        $requset_data['userId'] = $this->GetValue('userId');
        $requset_data['signType'] = 'MD5';
        $requset_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $requset_data['webSite'] = $this->GetValue('webSite');
        $requset_data['merchOrderNo'] = $this->GetCombinedOrderId();
        $requset_data['currency'] = $this->GetValue('Currency');
        $requset_data['orderAmount'] = $this->GetGatewayAmount();
        $requset_data['acquiringType'] = $this->GetValue('acquiringType');
        $requset_data['returnUrl'] = GetConfig('ShopPathSSL').'/yiji_return.php';
        $requset_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();

        $get_order_products = " SELECT `ordprodid`,`ordprodname`,`ordprodqty`,`base_price` FROM [|PREFIX|]order_products WHERE orderorderid = ".$this->GetCombinedOrderId();

        $result = $GLOBALS['ISC_CLASS_DB']->Query($get_order_products);
        $result_data = array();
        while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
            $result_data[] = $row;
        }
        $orderGoodsInfo = array();
        $logisticsFee = 0;
        foreach ($result_data as $k=>$v){
            $orderGoodsInfo[$k]['goodsNumber'] = $v['ordprodid'];
            $orderGoodsInfo[$k]['goodsName'] = $v['ordprodname'];
            $orderGoodsInfo[$k]['goodsCount'] = $v['ordprodqty'];
            $orderGoodsInfo[$k]['itemSharpProductcode'] = mt_rand(1,100);
            $orderGoodsInfo[$k]['itemSharpUnitPrice'] = $v['base_price'];
            //$logisticsFee = $v['shipping_cost_inc_tax'];
        }

        $requset_data['goodsInfoOrders'] = json_encode($orderGoodsInfo);

        /**
        * 获取订单物流信息
        */
        $get_order_logistics = 'SELECT `base_cost`,`method` FROM [|PREFIX|]order_shipping WHERE order_id = '.$this->GetCombinedOrderId();
        $order_logistics_result = $GLOBALS['ISC_CLASS_DB']->Query($get_order_logistics);
        $order_logistics_data = array();
        while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($order_logistics_result)){
            $order_logistics_data[] = $row;
        }

        $billingDetails = $this->GetBillingDetails();
        $ShippingAddress = $this->getShippingAddress();
        $attachDetails = array(
            //'ipAddress'=>$this->getIp(),
            'ipAddress'=>'113.204.226.234',
            'billtoCountry'=>$billingDetails['ordbillcountrycode'],
            'billtoState'=>$billingDetails['ordbillstate'],
            'billtoCity'=>$billingDetails['ordbillsuburb'],
            'billtoPostalcode'=>$billingDetails['ordbillzip'],
            'billtoEmail'=>$billingDetails['ordbillemail'],
            'billtoFirstname'=>$billingDetails['ordbillfirstname'],
            'billtoLastname'=>$billingDetails['ordbilllastname'],
            'billtoPhonenumber'=>$billingDetails['ordbillphone'],
            'billtoStreet'=>$billingDetails['ordbillstreet1'],
            'shiptoCity'=>$ShippingAddress['city'],
            'shiptoCountry'=>$ShippingAddress['country_iso2'],
            'shiptoFirstname'=>$ShippingAddress['first_name'],
            'shiptoLastname'=>$ShippingAddress['last_name'],
            'shiptoEmail'=>$ShippingAddress['email'],
            'shiptoPhonenumber'=>$ShippingAddress['phone'],
            'shiptoPostalcode'=>$ShippingAddress['zip'],
            'shiptoState'=>$ShippingAddress['state'],
            'shiptoStreet'=>$ShippingAddress['address_1'],
            #物流费
            'logisticsFee'=>$order_logistics_data[0]['base_cost'],
            #物流方式
            'logisticsMode'=>$order_logistics_data[0]['method'],
            'customerEmail'=>$ShippingAddress['email'],
            'customerPhonenumber'=>$ShippingAddress['phone'],
        );
        $requset_data['attachDetails'] = json_encode($attachDetails);
        $requset_data['language'] = $this->GetValue('Language');

        //var_dump($requset_data);
        //exit();
        $requset_data['sign'] = $this->getSignString($requset_data);
        $this->RedirectToProvider($requset_url, $requset_data);
    }

    /**
    * 更具参数获取sign加密值这里主要使用MD5算法加密
    */
    public function getSignString(array $items){
        ksort($items);
        $signString = '';
        foreach ($items as $k => $v){
            $signString .= '&'.$k.'='.$v;
        }

        $signString = trim($signString,'&');
        //file_put_contents(dirname(__FILE__) . "read.txt",$signString.$this->GetValue('secretKey'),FILE_APPEND);
        return md5($signString.$this->GetValue('secretKey'));
    }

    /**
     * 回调处理
     */
    public function ProcessGatewayPing(){
    try{
      # 1、检查签名是否一致
        if($this->checkNotifySign($_REQUEST)){

          $order_info = GetOrder($_REQUEST['merchOrderNo']);
          $extra  = array("tradeNo"=>$_REQUEST['merchOrderNo'],"orderNo"=>$_REQUEST['merchOrderNo'],"orderAmount"=>$order_info['total_inc_tax'],"orderCurrency"=>$this->GetValue('Currency'),"orderStatus"=>'');
          $newTransaction = array(
  			'providerid' => $this->GetId(),
  			'transactiondate' => time(),
  			'transactionid' => $this->GetCombinedOrderId(),
  			'orderid' => array_keys($this->GetOrders()),
  			'message' => '',
  			'status' => '',
  			'amount' => $order_info['total_inc_tax'],
  			'extrainfo' => array()
          );
          # 2、获取当前订单状态
          $transaction = GetClass('ISC_TRANSACTION');

            if($_REQUEST['resultCode'] == 'EXECUTE_SUCCESS'){
                # 3、查看订单回调的订单状态
                switch ($_REQUEST['status']){
                    case 'success':
                        $extra['orderStatus'] = ORDER_STATUS_AWAITING_SHIPMENT;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('YiJiPaySuccess'), $extra);
                        $newTransaction['status'] = TRANS_STATUS_COMPLETED;
                        $order_status = ORDER_STATUS_AWAITING_SHIPMENT;
                        break;
                    case 'fail':
                        $order_status = ORDER_STATUS_DECLINED;
                        $newTransaction['status'] = TRANS_STATUS_FAILED;
                        $extra['orderStatus'] = ORDER_STATUS_DECLINED;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemError(array('payment', $this->GetName()), GetLang('YiJiPaySuccess'), $extra);
                        break;
                    case 'authorizing':
                        $order_status = ORDER_STATUS_AWAITING_FULFILLMENT;
                        $extra['orderStatus'] = ORDER_STATUS_AWAITING_FULFILLMENT;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('YiJiPaySuccess'), $extra);
                        break;

                }

                $newTransaction['message'] = json_encode($_REQUEST);

                $transactionId = $transaction->Create($newTransaction);
                $orderInfo = array($_REQUEST['merchOrderNo']=>GetOrder($_REQUEST['merchOrderNo']));
                foreach ($orderInfo as $orderId => $order) {
                  UpdateOrderStatus($orderId, $order_status);

                }
                EmptyCartAndKillCheckout();
                exit('success');
            }else{
                exit('final');
            }

        }else{
          exit('final');
        }


        }catch (ErrorException $exception){
            file_put_contents(dirname(__FILE__) ."/logs/".$_REQUEST['merchOrderNo']."_read.txt","[".date('Y-m-d H:i:s')." OrderInfo ]   ".json_encode($exception)."\n\n\n",FILE_APPEND);
        }
    }

    public function VerifyOrderPayment()
    {
        file_put_contents(dirname(__FILE__) ."/logs/66668888_read.txt","[".date('Y-m-d H:i:s')." OrderInfo ]   ".json_encode($_REQUEST)."\n\n\n",FILE_APPEND);
        if(!empty($_COOKIE['SHOP_ORDER_TOKEN'])) {
            if($this->GetOrderStatus() == ORDER_STATUS_INCOMPLETE) {
                $this->SetPaymentStatus(PAYMENT_STATUS_PENDING);
            }
            return true;
        }else{
            $GLOBALS['ISC_CLASS_LOG']->LogSystemError(array('payment', $this->GetName()), GetLang('PayPalErrorInvalid'), __FUNCTION__);
            return false;
        }
    }

    /**
    * 检查回调的参数sign加密是否合法
    */
    public function checkNotifySign($items = array()){

        $sign = $items['sign'];

        unset($items['sign']);
        unset($items['action']);
        unset($items['provider']);
        unset($items['SHOP_SESSION_TOKEN']);
        ksort($items);
        $signString ='';
        foreach ($items as $k => $v){
            $signString .= '&'.$k.'='.$v;
        }

        $signString = trim($signString,'&');

        if($items['signType'] == 'MD5'){
            $tureSign = md5($signString.$this->GetValue('secretKey'));
            if($sign == $tureSign){
                return true;
            }
            return false;
        }

    }

    /**
    * 获取用户的IP地址
    */
    function getIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }else if(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }else{
            $cip = "127.0.0.1";
        }
        return $cip;
    }

    /**
    * 跳转到收银台
    */
    public function RedirectToProvider($url,$fields=array()){
        $formFields = '';
        foreach($fields as $name => $value) {
            $formFields .= "<input type=\"hidden\" name=\"".isc_html_escape($name)."\" value=\"".isc_html_escape($value)."\" />\n";
        }
// target="iframe_a" <iframe width="100%"  name="iframe_a" style="border-width:0px;height:700px;">
          //<script type="text/javascript">
            //  alert(window.location.href); target="_blank"
          //</script>
      //</iframe>
        $GLOBALS['PAYPAGE'] = '
        <iframe width="100%"  name="iframe_a" style="border-width:0px;height:700px;">
                  <script type="text/javascript">
                   alert(window.location.href); target="_blank"
                  </script>
      </iframe>
			<form id="form_pay" action="'.$url.'" method="post" style="margin-top: 50px; text-align: center;" target="iframe_a" >
				<noscript><input type="submit" value="'.GetLang('ClickIfNotRedirected').'" /></noscript>
				<div id="ContinueButton" style="display: none;">
					<!--input type="submit" value="'.GetLang('ClickIfNotRedirected').'" /-->
				</div>
				'.$formFields.'
			</form>
			<script type="text/javascript">
				$(function(){
				    $("#form_pay").submit();
				})
			</script>';
        $GLOBALS['ISC_CLASS_TEMPLATE']->SetPageTitle(sprintf("%s - %s",'pay','pay'));
        $GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate("checkout_paypage");
        $GLOBALS['ISC_CLASS_TEMPLATE']->ParseTemplate();
    }

    /**
    * 预授权操作
    */
    public function authorizingAction($orderNo,$resolveReason,$isAccept = 'true'){
        //$get_orderinfo = " SELECT `ordpayproviderid` FROM [|PREFIX|]orders WHERE orderid  = ".$orderNo;

        //$result = $GLOBALS['ISC_CLASS_DB']->Query($get_orderinfo);
        //$result_data = array();
        //while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
         //   $result_data[] = $row;
        //}
        //var_dump($result_data);exit;
        $request_data = array();
        $request_data['merchOrderNo'] = $orderNo;
        $request_data['originalMerchOrderNo'] = $orderNo;
        $request_data['isAccept'] = $isAccept;
        $request_data['resolveReason'] = $resolveReason;

        $request_data['service'] = 'cardAcquiringPresaleResult';
        $request_data['version'] = '1.0';
        $request_data['partnerId'] = $this->GetValue('partnerId');
        $request_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $request_data['signType'] = 'MD5' ;
        $request_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
        $request_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();
        $signType = $this->getSignString($request_data);
        $request_data['sign'] = $signType;
        //var_dump($request_data);

        $resource = $this->vpost($this->GetValue('gateway'),$request_data);
        $resource_data = json_decode($resource,1);

        if($resource_data['resultCode'] == 'EXECUTE_SUCCESS'){
            $updatedOrder = array();
            if($resource_data['status'] == 'success' && $isAccept == 'true'){
                $updatedOrder['ordpaymentstatus']  = 'success';
                $updatedOrder['ordstatus']  = 9;
            }

            if($resource_data['status'] == 'success' && $isAccept == 'false'){
                $updatedOrder['ordpaymentstatus']  = 'fail';
                $updatedOrder['ordstatus']  = 5;
            }

            $result = $GLOBALS['ISC_CLASS_DB']->UpdateQuery('orders', $updatedOrder, "orderid='".(int)$orderNo."'");
            return $result ? array('status'=>true) : array('status'=>false) ;
        }else{
            return array('status'=>false);
        }

    }

    /**
    * 退款操作
    *
    */
    public function refundAction($OrderNo,$refundAmount,$refundReason){
        $request_data['merchOrderNo'] = $OrderNo;
        $request_data['originalMerchOrderNo'] = $OrderNo;
        $request_data['refundAmount'] = $refundAmount;
        $request_data['refundReason'] = $refundReason;

        $request_data['service'] = 'cardAcquiringRefund';
        $request_data['version'] = '1.0';
        $request_data['partnerId'] = $this->GetValue('partnerId');
        $request_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $request_data['signType'] = 'MD5' ;
        $request_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
        $request_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();
        $signType = $this->getSignString($request_data);
        $request_data['sign'] = $signType;
        echo $this->vpost($this->GetValue('gateway'),$request_data);
    }

    /**
    * 使用curl发起请求操作
    *
    */
    function vpost($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }
}

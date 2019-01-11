<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/3
 * Time: 11:32
 */
class CHECKOUT_YIJIESPPAYCN extends ISC_CHECKOUT_PROVIDER
{

    /**
     * 初始化支付控件的相关参数
     * CHECKOUT_YIJIESPPAY constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_name = GetLang('YiJiPayName');
        $this->_image = "yijipay.jpg";
        $this->_description = GetLang('YiJiPayDesc');
        $this->_help = sprintf(GetLang('YiJiPayHelp'), $GLOBALS['ShopPathSSL']);
       $this->_height = 0;
    }

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
            "name" => "Jump Mode",
            "type" => "dropdown",
            "help" => GetLang('YiJiPayLanguageHelp'),
            "default" => 'en',
            "options"=>array(
                'English'=>'en',
                'Japanese'=>'ja',
                'Deutsch'=>'de',
                'El español'=>'es',
                'Français'=>'fr'
            ),
            "required" => false
        );

    }

    public function TransferToProvider()
    {
        $requset_data = array();

        $requset_url = $this->GetValue('gateway');

        $requset_data['protocol'] = 'httpGet';
        $requset_data['service'] = 'espOrderPay';
        $requset_data['version'] = '1.0';
        $requset_data['partnerId'] = $this->GetValue('partnerId');
        $requset_data['userId'] = $this->GetValue('userId');
        $requset_data['signType'] = 'MD5';
        $requset_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $requset_data['webSite'] = $this->GetValue('webSite');
        $requset_data['merchOrderNo'] = $this->GetCombinedOrderId();
        $requset_data['currency'] = 'USD';
        $requset_data['amount'] = $this->GetGatewayAmount();
        session_start();
        $requset_data['deviceFingerprintId'] = session_id();
        $requset_data['acquiringType'] = $this->GetValue('acquiringType');
        $requset_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
        $requset_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();

        $get_order_products = " SELECT `ordprodid`,`ordprodname`,`ordprodqty`,`base_price` FROM [|PREFIX|]order_products WHERE orderorderid = ".$this->GetCombinedOrderId();

        $result = $GLOBALS['ISC_CLASS_DB']->Query($get_order_products);
        $result_data = array();
        while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
            $result_data[] = $row;
        }
        $orderGoodsInfo = array();
        foreach ($result_data as $k=>$v){
            $orderGoodsInfo[$k]['goodsNumber'] = $v['ordprodid'];
            $orderGoodsInfo[$k]['goodsName'] = $v['ordprodname'];
            $orderGoodsInfo[$k]['goodsCount'] = $v['ordprodqty'];
            $orderGoodsInfo[$k]['itemSharpProductcode'] = mt_rand(1,100);
            $orderGoodsInfo[$k]['itemSharpUnitPrice'] = $v['base_price'];
        }

        $requset_data['goodsInfoList'] = json_encode($orderGoodsInfo);

        $billingDetails = $this->GetBillingDetails();
        $ShippingAddress = $this->getShippingAddress();
        $attachDetails = array(
            'ipAddress'=>$this->getIp(),
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
            'logisticsFee'=>20.00,
            #物流方式
            'logisticsMode'=>'Flat Rate Per Order',
            'customerEmail'=>$ShippingAddress['email'],
            'customerPhonenumber'=>$ShippingAddress['phone'],
        );
        $requset_data['orderDetail'] = json_encode($attachDetails);
        $requset_data['language'] = $this->GetValue('Language');
        $requset_data['sign'] = $this->getSignString($requset_data);
        $this->RedirectToProvider($requset_url, $requset_data);
    }


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

        //file_put_contents(dirname(__FILE__) ."/".$_REQUEST['merchOrderNo']."_read.txt",json_encode($_REQUEST),FILE_APPEND);
        if($this->checkNotifySign($_REQUEST)){

            if($_REQUEST['resultCode'] == 'EXECUTE_SUCCESS'){
                $updatedOrder = array('ordpayproviderid'=>$_REQUEST['orderNo']);
                switch ($_REQUEST['status']){
                    case 'success':
                        $updatedOrder['ordpaymentstatus']  = 'success';
                        $updatedOrder['ordstatus']  = 9;
                        break;
                    case 'fail':
                        $updatedOrder['ordpaymentstatus']  = 'fail';
                        $updatedOrder['ordstatus']  = 7;
                        break;
                    case 'authorizing':
                        $updatedOrder['ordpaymentstatus']  = 'authorizing';
                        $updatedOrder['ordstatus']  = 11;
                        break;

                }
                $result = $GLOBALS['ISC_CLASS_DB']->UpdateQuery('orders', $updatedOrder, "orderid='".(int)$_REQUEST['merchOrderNo']."'");

                echo $result ? 'success' : 'final';
            }else{
                echo 'final';
            }

        }else{
            echo 'final';
        }



    }

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
    public function RedirectToProvider($url,$fields=array()){
        $formFields = '';
        foreach($fields as $name => $value) {
            $formFields .= "<input type=\"hidden\" name=\"".isc_html_escape($name)."\" value=\"".isc_html_escape($value)."\" />\n";
        }

        $GLOBALS['PAYPAGE'] = '
			<iframe width="100%"  name="iframe_a" style="border-width:0px;height:700px;">
                <script type="text/javascript">
                    alert(window.location.href);
                </script>
            </iframe>
			<form id="form_pay" action="'.$url.'" method="get" style="margin-top: 50px; text-align: center;" target="iframe_a">
				<noscript><input type="submit" value="'.GetLang('ClickIfNotRedirected').'" /></noscript>
				<div id="ContinueButton" style="display: none;">
					<!--input type="submit" value="'.GetLang('ClickIfNotRedirected').'" /-->
				</div>
				'.$formFields.'
			</form>
			<script type="text/javascript">
				/*window.onload = function() {
					document.forms[0].submit();
					setTimeout(function() {
						document.getElementById("ContinueButton").style.display = "";
					}, 1000);
				}*/
				$(function(){
				    $("#form_pay").submit();
				})
			</script>

		';

        $GLOBALS['ISC_CLASS_TEMPLATE']->SetPageTitle(sprintf("%s - %s",'pay','pay'));
        $GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate("checkout_paypage");
        $GLOBALS['ISC_CLASS_TEMPLATE']->ParseTemplate();
    }

    public function authorizingAction($orderNo,$resolveReason,$isAccept = 'true'){
        //$get_orderinfo = " SELECT `ordpayproviderid` FROM [|PREFIX|]orders WHERE orderid  = ".$orderNo;

        //$result = $GLOBALS['ISC_CLASS_DB']->Query($get_orderinfo);
        //$result_data = array();
        //while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
         //   $result_data[] = $row;
        //}
        //var_dump($result_data);exit;
        $request_data = array();
        //$request_data['merchOrderNo'] = $orderNo;
        //$request_data['originalMerchOrderNo'] = $orderNo;
        $request_data['isAccept'] = $isAccept;
        $request_data['resolveReason'] = $resolveReason;

        $request_data['service'] = 'espOrderJudgment';
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

//    public function refundAction($OrderNo,$refundAmount,$refundReason){
//        $request_data['merchOrderNo'] = $OrderNo;
//        $request_data['originalMerchOrderNo'] = $OrderNo;
//        $request_data['refundAmount'] = $refundAmount;
//        $request_data['refundReason'] = $refundReason;
//
//        $request_data['service'] = 'espRefund';
//        $request_data['version'] = '1.0';
//        $request_data['partnerId'] = $this->GetValue('partnerId');
//        $request_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
//        $request_data['signType'] = 'MD5' ;
//        $request_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
//        $request_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();
//        $signType = $this->getSignString($request_data);
//        $request_data['sign'] = $signType;
//        echo $this->vpost($this->GetValue('gateway'),$request_data);
//    }

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

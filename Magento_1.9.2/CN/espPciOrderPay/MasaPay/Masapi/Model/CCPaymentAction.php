<?php
/**
 * Masapay Magento Plugin.
 * v1.1.8 - November 8th, 2013
 *
 *
 * Copyright (c) 2013 Masapay
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     - Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     - Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 *     - Neither the name of the Masapay nor the names of its
 *       contributors may be used to endorse or promote products
 *       derived from this software without specific prior written
 *       permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Mage
 * @package     MasaPay_Masapi_Model_CCPaymentAction
 * @copyright   Copyright (c) 2013 Masapay  (www.masapay.com)
 * @license     http://opensource.org/licenses/bsd-license.php  BSD License
 */
class MasaPay_Masapi_Model_CCPaymentAction extends Mage_Payment_Model_Method_Cc {
	protected $_code = 'masapi';
	protected $_formBlockType = 'masapi/form';
	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;
	protected $_canSaveCc = false;
	protected $_authMode = 'auto';
	public function authorize(Varien_Object $payment, $amount) {

        $order = $payment->getOrder ();
        $billing = $order->getBillingAddress ();
        $shipping = $order->getShippingAddress ();
        $sales_order = Mage::getModel('sales/order')->load($order->getEntityId());		//获得更详细信息
        $requestData = [];

        $requestData['orderNo'] = time().mt_rand(100000,999999);
        $requestData['protocol'] = 'HTTP-FORM-JOSN';
        $requestData['service'] = 'espPciOrderPay';
        $requestData['version'] = '2.0';
        $requestData['partnerId'] = $this->getConfigData ( 'merchant_partnerId' );
        $requestData['signType'] = 'MD5';
        $requestData['merchOrderNo'] =  $order->getIncrementId();
        //$requestData['merchOrderNo'] =  $order->getIncrementId();
        $requestData['notifyUrl'] =  Mage::getUrl('masapayapi/masapay/ty', array('_secure' => true));



        $requestData['userId'] = $this->getConfigData ( 'merchant_partnerId' );
        $requestData['currency'] =  $order->getOrderCurrencyCode();
        $requestData['amount'] = $amount;
        $requestData['cardNo'] = $payment->getCcNumber();
        $requestData['cvv'] = $payment->getCcCid();
        $requestData['cardHolderFirstName'] = $billing->getFirstname();
        $requestData['cardHolderLastName'] = $billing->getLastname();
        $requestData['expirationDate'] = $payment->getCcExpMonth() . substr ( $payment->getCcExpYear(), 2, 2 );
        $requestData['webSite'] =  $_SERVER['HTTP_HOST'];
        $requestData['deviceFingerprintId'] =  $payment->getCcSsIssue();
        $requestData['acquiringType'] =  'CRDIT';
        $requestData['language'] =  'en';


            $orderDetail = [];
            $orderDetail['ipAddress'] =  $this->getIP();
            $orderDetail['billtoCountry'] =  $billing->getCountry();
            $orderDetail['billtoState'] =  $billing->getRegion();
            $orderDetail['billtoCity'] =  $billing->getCity();
            $orderDetail['billtoCity'] =  $billing->getCity();
            $orderDetail['billtoPostalcode'] =  $billing->getPostcode();
            $orderDetail['billtoEmail'] =  $billing->getEmail();
            $orderDetail['billtoFirstname'] =  $billing->getFirstname();
            $orderDetail['billtoLastname'] =  $billing->getLastname();
            $orderDetail['billtoPhonenumber'] =  $billing->getTelephone();
            $orderDetail['billtoStreet'] =  $billing->getStreet(1) . ' ' . $shipping->getStreet(2);

            $orderDetail['shiptoCity'] =  $shipping->getData('city');
            $orderDetail['shiptoCountry'] =  $shipping->getData('country_id');
            $orderDetail['shiptoFirstname'] =  $shipping->getData('firstname');
            $orderDetail['shiptoLastname'] =  $shipping->getData('lastname');
            $orderDetail['shiptoEmail'] =  $shipping->getData('email');
            $orderDetail['shiptoPhonenumber'] =  $billing->getData('telephone');
            $orderDetail['shiptoPostalcode'] =  $shipping->getData('postcode');
            $orderDetail['shiptoState'] =  $shipping->getData('region');
            $orderDetail['shiptoStreet'] = $shipping->getData('street');

            $orderDetail['logisticsFee'] =  $this->chageBaseConvert2SessionConvert($payment->getBaseShippingAmount());
            $orderDetail['logisticsMode'] =  $sales_order->getShippingDescription();

            switch ($payment->getCcType()){
                case "VI";
                    $cardType = 'Visa';
                    break;
                case "MC";
                    $cardType = 'MasterCard';
                    break;
                case "JCB";
                    $cardType = 'JCB';
                    break;
            }

            $orderDetail['cardType'] =  $cardType;
            $orderDetail['customerEmail'] =  $billing->getEmail();
            $orderDetail['merchantEmail'] =  $this->getConfigData ( 'merchant_email' );
            $orderDetail['merchantName'] =  $this->getConfigData ( 'merchant_name' );
        $requestData['orderDetail']  = json_encode($orderDetail);
        $requestData['goodsInfoList'] = json_encode($this->getWkGoodsInfoListByOrderList($sales_order->getAllItems()));
        $requestData['sign'] = $this->getSign($requestData);

        file_put_contents(__DIR__.'/orders.log',$order->getIncrementId()." [ ".date('Y-m-d H:i:s')." DATA]:".json_encode($requestData)."\n",FILE_APPEND);

        if($this->getConfigData ( 'sandbox' )){
            $url = 'https://openapi.yijifu.net/gateway.html';
        }else{
            $url = 'https://openapiglobal.yiji.com/gateway.html';
        }

        $response = $this->sendPost($url,$requestData);

				file_put_contents(__DIR__.'/orders.log',$order->getIncrementId()." [ ".date('Y-m-d H:i:s')." RESPONESE]:".$response."\n\n",FILE_APPEND);

        $response = json_decode($response,1);
        if($response['success']){

            switch($response['status']){
                case 'success':
                    $sqlStatus =  $this->getConfigData ( 'success_order_status');
                    break;
                case 'fail':
                    $sqlStatus =  $this->getConfigData ( 'fail_order_status');
                    break;
                case 'authorizing':
                    $sqlStatus =  $this->getConfigData ( 'authorizing_order_status');
                    break;
                case 'processing':
                    $sqlStatus =  $this->getConfigData ( 'processing_order_status');
                    break;
            }

						$orderT = Mage::getModel('sales/order');
						$orderT->loadByIncrementId($response['merchOrderNo']);
						$orderT->addStatusToHistory($sqlStatus,Mage::helper('paygate')->__('Customer got successful notification  from Yijifu,trading '.$sqlStatus.'!'),true);
						$orderT->setState($sqlStatus, true);
						$orderT->save();

        }

	}

	protected function getSign($data){
	    ksort($data);

	    $str = '';

	    foreach ($data as $k => $v){
            $str .= $k.'='.$v.'&';
        }

        $str = trim($str,'&');

	    return md5($str.$this->getConfigData( 'secretKey' ));
    }

	/**
	 * Setup the Masapay transaction api class.
	 *
	 * Much of this code is common to all commands
	 *
	 * @param Mage_Sales_Model_Document $pament
	 * @return MasaPay_Masapi_Model_TranApi
	 */
//	protected function _initTransaction(Varien_Object $payment) {
//		$tran = Mage::getModel ( 'masapi/TranApi' );
//
//		if ($this->getConfigData ( 'sandbox' ))
//			$tran->usesandbox = true;
//// 		$tran->software = 'MasaPay_Masapi 1.0';
//		return $tran;
//	}

    protected function encrypt($entity){

        $url = $this->getConfigData('secret_encode_gateway' );
        //'http://cer.gpayonline.com/encrypt.action?action=doEncrypt'
        $entity = base64_encode($entity);
        $post_data = array('json'=>json_encode(array('resoucesStr'=>$entity,'appName'=>"PTHZKJ")));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        curl_close($ch);
        $result =  json_decode($result);
        return trim($result->encryptStr);
    }

    protected function getIP() {
        if (array_key_exists("HTTP_X_FORWARDED_FOR",$_SERVER))
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else if (array_key_exists("HTTP_CLIENT_IP",$_SERVER))
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        else if (array_key_exists("REMOTE_ADDR",$_SERVER))
            $ip = $_SERVER["REMOTE_ADDR"];
        else if (@getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (@getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (@getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknown";
        return $ip;
    }

    public function chageBaseConvert2SessionConvert($price){
        $baseCurrencyCode = Mage::app()->getBaseCurrencyCode();//获得系统设置的基础币种
        $CurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();//获得当前订单币种
        $Result= Mage::helper('directory')->currencyConvert($price, $baseCurrencyCode, $CurrencyCode);
        //Mage::log($price."币转为:".$Result,null,"log.log");
        $Result = ($Result==0)?"0.00":$Result;
        return $Result;
    }

    public function getWkGoodsInfoListByOrderList($ordered_items){
        foreach ($ordered_items as $k=>$item) {
            $goods_array[$k]['goodsCount']      = intval($item->getQtyOrdered());
            $_category_name ="";
            //获得商品所有信息
            $_product_model = Mage::getModel('catalog/product') ;
            $_product = $_product_model->load($item->getProductId());
            if ( $_product instanceof  Mage_Catalog_Model_Product){

                $goods_array[$k]['goodsNumber']       		= $_product->getEntityId();
                $goods_array[$k]['goodsName']       		= $_product->getName();
                $goods_array[$k]['itemSharpUnitPrice']   = $this->chageBaseConvert2SessionConvert($_product->getPrice());

                //获得商品所属分类
                $categories = $_product->getCategoryCollection();
                foreach($categories as $_category_enty) {
                    $_category_model = Mage::getModel('catalog/category');
                    $_category = $_category_model->load($_category_enty->getEntityId());
                    if ($_category instanceof Mage_Catalog_Model_Category){
                        //$_category_name=$_category_name.$_category->getName();
                        $_category_name=$_category->getName();

                    }

                }
            }
            $goods_array[$k]['itemSharpProductCode']    = $_category_name;
            //Mage::log("获取分类：".json_encode($goods_array), null,'log.log');
        }
        return $goods_array;
    }

    function sendPost($url,$params=false)
    {
        //Mage::log("请求".$url."内容".$params,null,"log.log");
        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        $ch = curl_init();
        if ($ssl){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);
        //Mage::log("响应结果：".$result,null,"log.log");
        return $result;
    }

}

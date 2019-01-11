<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/10/8
 * Time: 16:27
 */

namespace Magento\Yjpay\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Yjpay\Helper\Helper;

//use Magento\Sales\Model\Order;

class Checkout extends Action
{

    protected $cartManagement;

    protected $eventManager;

    protected $onepageCheckout;

    protected $jsonHelper;

    protected $yjpayHelp;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CartManagementInterface $cartManagement,
        Onepage $onepageCheckout,
        JsonHelper $jsonHelper,
        Helper $yjpayHelp
    ){
        $this->eventManager = $context->getEventManager();
        $this->cartManagement = $cartManagement;
        $this->onepageCheckout = $onepageCheckout;
        $this->jsonHelper = $jsonHelper;
        $this->yjpayHelp = $yjpayHelp;
        parent::__construct($context);
    }

    public function execute()
    {
        session_start();
        $session_id = session_id();

        $session = $this->getOnepage()->getCheckout();
        $order_id =  $session->getLastOrderId();

        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log',"1: ".json_encode($order_id)."\n\n",FILE_APPEND);

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $yjpay = $this->getYjpayPayment();
        $billInfo= $order->getBillingAddress();
        $shipInfo= $order->getShippingAddress();

        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log',"2: ".json_encode($order)."\n\n",FILE_APPEND);
        $payment_data = [];

        $payment_data['protocol'] = 'httpGet';

        $payment_data['returnUrl'] = $this->yjpayHelp->getUrl('yjpay/notice/location');
        $payment_data['notifyUrl'] = $this->yjpayHelp->getUrl('yjpay/notice/notify');

        $payment_data['service'] = 'espOrderPay';
        $payment_data['version'] = '1.0';
        $payment_data['partnerId'] = $yjpay->getYjpayData('partnerId');
        $payment_data['orderNo'] = date('YmdHis').mt_rand(10000,99999);
        $payment_data['signType'] = 'RSA';



        $payment_data['userId'] = $yjpay->getYjpayData('partnerId');
        $payment_data['currency'] = $order->getOrderCurrencyCode();;
        $payment_data['amount'] = sprintf('%.2f', $order->getTotalDue());
        $payment_data['webSite'] = $_SERVER ['HTTP_HOST'];
        $payment_data['merchOrderNo'] = $order_id;
        $payment_data['acquiringType'] = 'CRDIT';
        $payment_data['goodsInfoOrders'] = $order->getItems();



        $payment_data['deviceFingerprintId'] = $session_id;
        $payment_data['language'] = 'en';
//        $payment_data['endReturnURL'] = '';
//        $payment_data['memo'] = '';


        $items = $order->getItems();
        $goodsInfoOrders = [];
        $itemsData = [];
        foreach ($items as $item){
            $itemsData['goodsName'] = $item->getName();
            $itemsData['goodsCount'] = (int)$item->getQtyOrdered();
            $itemsData['goodsNumber'] = $item->getSku();
            $itemsData['itemSharpProductcode'] = $item->getName();
            $itemsData['itemSharpUnitPrice'] = sprintf('%.2f',$item->getPrice());//$item->getPrice();//
            $goodsInfoOrders[] = $itemsData;
        }
        $payment_data['goodsInfoOrders'] = json_encode($goodsInfoOrders);

        $AttachedDetails = [];
        $AttachedDetails['ipAddress'] = $this->getIp();
        $AttachedDetails['billtoCountry'] = $billInfo->getCountryId();
        $AttachedDetails['billtoState'] = $billInfo->getRegion();
        $AttachedDetails['billtoCity'] = $billInfo->getCity();
        $AttachedDetails['billtoPostalcode'] = $billInfo->getPostcode();
        $AttachedDetails['billtoEmail'] = $order->getCustomerEmail();
        $AttachedDetails['billtoFirstname'] = $billInfo->getFirstname();
        $AttachedDetails['billtoLastname'] = $billInfo->getLastname();
        $AttachedDetails['billtoPhonenumber'] = $billInfo->getTelephone();
        $AttachedDetails['billtoStreet'] = $billInfo->getStreetLine(1).' '.$billInfo->getStreetLine(2);

        $AttachedDetails['shiptoCountry'] = $shipInfo->getCountryId();
        $AttachedDetails['shiptoCity'] = $shipInfo->getCity();
        $AttachedDetails['shiptoFirstname'] = $shipInfo->getFirstname();
        $AttachedDetails['shiptoLastname'] = $shipInfo->getLastname();
        $AttachedDetails['shiptoEmail'] = $order->getCustomerEmail();
        $AttachedDetails['shiptoPhonenumber'] = $shipInfo->getTelephone();
        $AttachedDetails['shiptoPostalcode'] = $shipInfo->getPostcode();
        $AttachedDetails['shiptoState'] = $shipInfo->getRegion();
        $AttachedDetails['shiptoStreet'] = $shipInfo->getStreetLine(1).' '.$shipInfo->getStreetLine(2);


        $AttachedDetails['logisticsFee'] = sprintf('%.2f',$order->getShippingAmount());
        $AttachedDetails['logisticsMode'] = $order->getShippingMethod();
        $AttachedDetails['customerEmail'] = $order->getCustomerEmail();
        $AttachedDetails['customerPhonenumber'] = $shipInfo->getTelephone();
        $AttachedDetails['merchantEmail'] = $yjpay->getYjpayData('merchantEmail');
        $AttachedDetails['merchantName'] = $yjpay->getYjpayData('merchantName');

        $payment_data['attachDetails'] = json_encode($AttachedDetails);

        $requestData = $this->getSign($payment_data);

        $requestString = '?';

        foreach ($requestData as $key => $value ){
            $requestString .= $key.'='.$value.'&';
        }

        $requestString = trim($requestString,'&');

        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log',"3: ".json_encode($payment_data)."\n\n",FILE_APPEND);

        $url = '';
        if(!$yjpay->getYjpayData('sandbox')){
            $url = 'https://api.yiji.com/gateway.html'.$requestString;
        }else{
            $url = 'https://openapi.yijifu.net/gateway.html'.$requestString;
        }

        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log',"4: ".$url."\n\n",FILE_APPEND);

        echo json_encode(['url'=>$url]);

    }

    protected function getOnepage(){
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Onepage');
    }

    protected function getYjpayPayment(){
        return $this->_objectManager->get('Magento\Yjpay\Model\Yjpay');
    }

    protected function getSign(array $datas){
        foreach ( $datas as $key => $data ){
            if(!isset($data)){
               unset($datas[$key]);
            }
        }
        ksort($datas);

        $string = '';

        foreach ( $datas as $key => $value ){
            $string .= $key.'='.$value.'&';
        }
        $yjpay = $this->getYjpayPayment();
        $sign = "";
        if ($datas['signType'] === 'MD5'){
            $string = trim($string,'&').$yjpay->getYjpayData('secretKey');
            $sign = md5($string);
        }elseif ($datas['signType'] === 'RSA'){
            $signSrc = trim(substr($string, 0, -1));
            if (!$yjpay->getYjpayData('sandbox')){//线上环境
                $pfxPath = __DIR__.'/../../'.$datas['partnerId'].'.pfx';
            }else {
                $pfxPath = __DIR__.'/../../snet_'.$datas['partnerId'].'.pfx';
            }
            $keyPass = $yjpay->getYjpayData('secretKey');
            $pkcs12 = file_get_contents($pfxPath);
            if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
                $privateKey = $certs['pkey'];
                $signedMsg = "";
                if (openssl_sign($signSrc, $signedMsg, $privateKey)) {
                    $sign = base64_encode($signedMsg);
                } else {
                    return '加密失败';
                }
            }
        }else{

        }

        $datas['sign'] = $sign;
        return $datas;
    }

    protected function getIp(){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $online_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
            $online_ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(isset($_SERVER['HTTP_X_REAL_IP'])){
            $online_ip = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $online_ip = $_SERVER['REMOTE_ADDR'];
        }
        $ips = explode(",",$online_ip);
        return $ips[0];
    }
}
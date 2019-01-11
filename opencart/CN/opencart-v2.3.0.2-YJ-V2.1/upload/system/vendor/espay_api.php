<?php

# declare easypay pay status
define('EASYAPY_PAY_STATUS_SUBMIT', 0);
define('EASYPAY_PAY_STATUS_AUTH', 1);
define('EASYPAY_PAY_STATUS_COMPLETE', 2);
define('EASYPAY_PAY_STATUS_CANCEL', 3);


class ESPayApi {
    #const SERVICE_GATEWAY_URL = 'https://openapi.yiji.com/gateway.html';
    const SERVICE_GATEWAY_URL = 'https://api.yiji.com/gateway.html';

    const SERVICE_GATEWAY_DEV_URL = 'http://openapi.yijifu.net/gateway.html';

    const SERVICE_ORDER_PAY            = 'espPciOrderPay';
    const SERVICE_ORDER_PAY_ORDER_TYPE = 'MOTO_EDC';

    const SERVICE_ORDER_REFUND = 'espOrderRefund';
    const SERVICE_cancel_order = 'espOrderCancel';
    const SERVICE_ORDER_QUERY  = 'espOrderQuery';
    const SERVICE_ORDER_INFO  = 'espOrderInfo';
    const SERVICE_PROCESS_CONFIRM = 'espProcessConfirm';
    const SERVICE_PROCESS_CANCEL  = 'espProcessCancel';

    const SERVICE_PROCESS_JUDGMENT = 'espOrderJudgment';

    const SERVICE_PROTOCOL  = 'httpPost';
    const SERVICE_VERSION   = '1.0';
    const SERVICE_VERSION_PCI   = '3.0';
    const SERVICE_SIGN_TYPE = 'RSA';

    # declare partner id and secret key
    var $partnerID, $certificateCipher, $debug ,$jump;

    function __construct($partnerId, $certificateCipher, $debug = true, $jump = 'jump') {
        $this->partnerID = $partnerId;
        $this->certificateCipher = $certificateCipher;
        $this->debug     = $debug;
        $this->jump      = $jump;
    }

    function submitOrderPay($order, $goods, $notify_url, $return_url = '') {
        # get service parameters
        $submit = $order->serviceParameters();

        $submit['protected'] = self::SERVICE_PROTOCOL;
        $submit['service']   = self::SERVICE_ORDER_PAY;
        if ($this->jump === 'embed'){
            $submit['version']    = self::SERVICE_VERSION_PCI;
        }else {
            $submit['version']    = self::SERVICE_VERSION;
        }

        $submit['signType']  = self::SERVICE_SIGN_TYPE;

        $submit['returnUrl'] = $return_url;
        $submit['notifyUrl'] = $notify_url;


        $submit['userId']    = $this->partnerID;
        $submit['partnerId'] = $this->partnerID;
        $submit['cardNo']    = $this->encrypt($submit['cardNo'].$submit['orderNo']);
        $submit['cvv']       = $this->encrypt($submit['cvv'].$submit['orderNo']);

        $submit['goodsInfoList'] = json_encode($goods);
        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }


    function cancelOrderPay($order_sn, $reason = '') {
        $submit = array(
            'protected' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_cancel_order,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'signType' => self::SERVICE_SIGN_TYPE,
            'outOrderNo' => $order_sn,
            'reason' => $reason,
            'orderNo' => $order_sn . time()
        );

        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }


    function refundOrderPay($order_sn, $refund_money, $reason = '') {
        $submit = array(
            'protected' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_ORDER_REFUND,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'signType' => self::SERVICE_SIGN_TYPE,
            'outOrderNo' => $order_sn,
            'refundMoney' => $refund_money,
            'reason' => $reason,
            'orderNo' => $order_sn . time()
        );

        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }


    function queryOrderPay($order_sn) {
        $submit = array(
            'protocol' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_ORDER_INFO,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'orderNo' => $order_sn . time(),
            'signType' => self::SERVICE_SIGN_TYPE,
            'merchOrderNo' => $order_sn,
            'userId' =>$this->partnerID
        );

        $submit['sign'] = $this->signature($submit);

        $ordersJson = $this->_execute($submit);
        $orders     = json_decode($ordersJson);


        return ($orders->success && $orders->orderInfos ) ? $orders->orderInfos[0] : false;
    }

    function confirmProcess($order_sn) {
        $submit = array(
            'protected' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_PROCESS_CONFIRM,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'signType' => self::SERVICE_SIGN_TYPE,
            'merchOrderNo' => $order_sn,
            'orderNo' => $order_sn . time(),
        );

        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }

    function cancelProcess($order_sn, $cancel_reason = '', $operator_id = null,
                           $operator_name = null, $product_code = null, $merchant_order_biz_noe = null) {
        $submit = array(
            'protected' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_PROCESS_CANCEL,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'signType' => self::SERVICE_SIGN_TYPE,
            'merchOrderNo' => $order_sn,
            'orderNo' => $order_sn . time()
        );

        if ($cancel_reason) $submit['cancelReason'] = $cancel_reason;
        if ($operator_id) $submit['operatorId'] = $operator_id;
        if ($operator_name) $submit['operatorName'] = $operator_name;
        if ($product_code) $submit['productCode'] = $product_code;

        if ($merchant_order_biz_noe) {
            $submit['merchantOrderBizNo'] = $merchant_order_biz_noe;
        }

        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }
    function judgment($order_sn,$isAccept = true){
        $submit = array(
            'orderNo' => $order_sn . time(),
            'merchOrderNo' => $order_sn,
            'protocol' => self::SERVICE_PROTOCOL,
            'service' => self::SERVICE_PROCESS_JUDGMENT,
            'version' => self::SERVICE_VERSION,
            'partnerId' => $this->partnerID,
            'signType' => self::SERVICE_SIGN_TYPE,
            'isAccept' => $isAccept
        );
        $submit['sign'] = $this->signature($submit);
        $result         = $this->_execute($submit);

        return json_decode($result);
    }

    function signature(array $fields) {
        # sort for key
        ksort($fields);

        $clientSignatureString = '';
        foreach ($fields as $key => $value) {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }

        if ($fields['signType'] == 'MD5'){
            $clientSignatureString = trim($clientSignatureString) . $this->certificateCipher;
            $signedMsg = md5($clientSignatureString);
        }elseif ($fields['signType'] == 'RSA'){
            $clientSignatureString = substr($clientSignatureString, 0, -1);
            $clientSignatureString = trim($clientSignatureString);
            $pfxPath = DIR_SYSTEM.$this->partnerID.'.pfx';
            $pkcs12 = file_get_contents($pfxPath);
            $keyPass = $this->certificateCipher;
            if(!file_exists($pfxPath)){
                return false;
            }
            $signedMsg = "";
            if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
                $privateKey = $certs['pkey'];
                if (openssl_sign($clientSignatureString, $signedMsg, $privateKey)) {
                    $signedMsg = base64_encode($signedMsg);
                } else {
                    $signedMsg = '0';
                }
            }
        }else {
            $signedMsg = '0';
        }

        return $signedMsg;
    }

    function encrypt($entity) {
        if ($this->debug){
            $public_key_path =  __DIR__.'/../../yjf-cert-2048.pem';
        }else{
            $public_key_path =  __DIR__.'/../../yjf-online-2048.pem';
        }
        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));
        openssl_public_encrypt(str_pad($entity, 256, "\0", STR_PAD_LEFT), $encryptedData, $public_key, OPENSSL_NO_PADDING);
        return base64_encode($encryptedData);
    }

    protected function _execute($submit) {
        $url  = $this->debug ? self::SERVICE_GATEWAY_DEV_URL : self::SERVICE_GATEWAY_URL;
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
}

class ESPayOrder {
    var $outOrderNo, $ipAddress, $deviceFingerprintId, $webSite;

    var $billToCity, $billToCountry, $billToState, $billToPostalCode, $billToEmail,
        $billToFirstName, $billToLastName, $billToPhoneNumber, $billToStreet1;

    var $shipToCity, $shipToCountry, $shipToFirstName, $shipToLastName,
        $shipToEmail, $shipToPhoneNumber, $shipToPostalCode, $shipToState, $shipToStreet1;

    var $logisticsFee, $logisticsMode, $amountLoc, $currencyCode;

    var $customerEmail, $customerPhoneNumber;

    var $merchantEmail, $merchantName;

    var $orderType, $cardType, $cardNo, $cvv,
        $cardHolderFirstName, $cardHolderLastName, $expirationDate;

    var $remark;

    function serviceParameters() {

        $orderDetail = array(
            'ipAddress' => $this->ipAddress,
            'merchantEmail' => $this->merchantEmail,
            'merchantName' => $this->merchantName,

            'billToCountry' => $this->billToCountry,
            'billToState' => $this->billToState,
            'billToCity' => $this->billToCity,
            'billToStreet' => $this->billToStreet1,
            'billToPostalCode' => $this->billToPostalCode,

            'billToFirstName' => $this->billToFirstName,
            'billToLastName' => $this->billToLastName,
            'billToPhoneNumber' => $this->billToPhoneNumber,
            'billToEmail' => $this->billToEmail,


            'shipToCountry' => $this->shipToCountry,
            'shipToState' => $this->shipToState,
            'shipToCity' => $this->shipToCity,
            'shipToStreet' => $this->shipToStreet1,
            'shipToPostalCode' => $this->shipToPostalCode,

            'shipToFirstName' => $this->shipToFirstName,
            'shipToLastName' => $this->shipToLastName,
            'shipToPhoneNumber' => $this->shipToPhoneNumber,
            'shipToEmail' => $this->shipToEmail,

            'customerPhoneNumber' => $this->customerPhoneNumber,
            'customerEmail' => $this->customerEmail,

            'logisticsFee' => $this->logisticsFee,
            'logisticsMode' => $this->logisticsMode,
            'cardType' => $this->cardType
        );
        return array(
            'merchOrderNo' => $this->outOrderNo,
            'orderNo' => $this->outOrderNo . time(),
            'currency' => $this->currencyCode,
            'amount' => $this->amountLoc,
            'webSite' => $this->webSite,
            'deviceFingerprintId' => $this->deviceFingerprintId,
            'cardHolderFirstName' => $this->cardHolderFirstName,
            'cardHolderLastName' => $this->cardHolderLastName,
            'cardType' => $this->cardType,
            'cardNo' => $this->cardNo,
            'cvv' => $this->cvv,
            'expirationDate' => $this->expirationDate,
            'remark' => $this->remark,

            'orderDetail'=>json_encode($orderDetail)
//            'orderType' => 'MOTO_EDC',
//			'exVersion' => 'Opc-2.0.1'
        );
    }
}

class ESPayGoods {
    var $goodsNumber, $goodsName, $goodsCount;

    var $itemSharpProductCode, $itemSharpUnitPrice;
}
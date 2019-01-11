<?php

    # define yjfpay table name
    define('TABLE_YJFPAYC_HISTORY', 'yjfpaycnew_history');

    /**
     * @const string debug url address
     */
    define('YJFPAYC_DEBUG_URL', 'https://openapi.yijifu.net/gateway.html');

    /**
     * @const string product url address
     */

    define('YJFPAYC_PRODUCT_URL', 'https://api.yiji.com/gateway.html');

    /**
     * get pay signature
     */
    function yjfpaycnew_signature(array $params) {
        //foreach ($params as $key => $value) {
        //  if($value == ''){
        //    unset($params[$key]);
        //  }
        //}
        # sort for key
        ksort($params);

        $clientSignatureString = '';
        foreach ($params as $key => $value) {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }
        $sign = '';
//        $clientSignatureString = trim($clientSignatureString,'&') . trim(MODULE_PAYMENT_YJFPAYCNEW_SECRET_KEY);
//        $sign = md5($clientSignatureString);

        $signSrc = trim(substr($clientSignatureString, 0, -1));
        $pfxPath = __DIR__.'/'.$params['partnerId'].'.pfx';
        $keyPass = MODULE_PAYMENT_YJFPAYCNEW_SECRET_KEY;
        $pkcs12 = file_get_contents($pfxPath);
        if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
            $privateKey = $certs['pkey'];
            $signedMsg = "";
            if (openssl_sign($signSrc, $signedMsg, $privateKey)) {
                $sign = base64_encode($signedMsg);
            }
        }
        //$clientSignatureString = substr($clientSignatureString, 0, -1);

        file_put_contents(__DIR__.'/xx.log',$clientSignatureString,FILE_APPEND);
        return $sign;
    }

    function array_key_pop_new(&$array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $default;
    }

    function verifySign($data){
        ksort($data);
        $waitingForSign = '';
        $verifyResult = false;
        foreach ($data as $key => $value) {
            if('sign'===$key){
                continue;
            }
            if('resultMessage' ==$key){
                $json = '{"str":"'.$value.'"}';
                $arr_v = json_decode($json,true);
                $waitingForSign .= $key.'='.($arr_v['str']).'&';
            }else{
                $waitingForSign .= ($key . '=' . $value . '&');
            }
        }
        $waitingForSign = trim(substr($waitingForSign, 0, -1));

        $cerPath = __DIR__.'/yijifu_online.cer';
        $certificateCAcerContent = file_get_contents($cerPath);
        $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
        $pubkeyid = openssl_get_publickey($certificateCApemContent);
        $signature = base64_decode($data['sign']);
        if($pubkeyid){
            // state whether signature is okay or not
            $verifyResult = openssl_verify($waitingForSign, $signature, $pubkeyid);
            openssl_free_key($pubkeyid);
        }
        file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".date('Y-m-d H:i:s')."_waitSignString ](verifySign):\n"."pubkeyid:".$pubkeyid."      verifyResult:".$verifyResult."\n\n".' waitingforsign:'.$waitingForSign.' signature:'.$signature.  "\n\n".json_encode($data)."\n\n",FILE_APPEND);
        return $verifyResult;
    }

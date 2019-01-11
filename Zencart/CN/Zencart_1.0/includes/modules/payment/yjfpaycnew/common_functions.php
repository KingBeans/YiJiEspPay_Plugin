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

    define('YJFPAYC_PRODUCT_URL', 'https://openapiglobal.yiji.com/gateway.html');

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

        //$clientSignatureString = substr($clientSignatureString, 0, -1);
        $clientSignatureString = trim($clientSignatureString,'&') . trim(MODULE_PAYMENT_YJFPAYCNEW_SECRET_KEY);
        file_put_contents(__DIR__.'/xx.log',$clientSignatureString,FILE_APPEND);
        return md5($clientSignatureString);
    }

    function array_key_pop_new(&$array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $default;
    }

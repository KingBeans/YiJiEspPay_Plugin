<?php

    # define yjfpay table name
    define('TABLE_YJFPAYC_HISTORY', 'yjfpayc_history');

    /**
     * @const string debug url address
     */
    // define('YJFPAYC_DEBUG_URL', 'http://openapi.yjpay.hk/gateway.html');
    define('YJFPAYC_DEBUG_URL', 'https://hkopenapitest.yiji.com/gateway.html');
    // define('YJFPAYC_DEBUG_URL', 'http://192.168.46.16:8630/gateway.html');

    /**
     * @const string product url address
     */
    define('YJFPAYC_PRODUCT_URL', 'https://openapi.yjpay.hk/gateway.html');

    /**
     * get pay signature
     */
    function yjfpayc_signature(array $params) {
        # sort for key
        ksort($params);

        $clientSignatureString = '';
        foreach ($params as $key => $value) {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }

        $clientSignatureString = substr($clientSignatureString, 0, -1);
        $clientSignatureString = trim($clientSignatureString) . MODULE_PAYMENT_YJFPAYC_SECRET_KEY;

        return md5($clientSignatureString);
    }

    function array_key_pop(&$array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $default;
    }
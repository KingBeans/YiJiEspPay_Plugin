<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/22
 * Time: 17:33
 */
include(dirname(__FILE__)."/init.php");
include(dirname(__FILE__)."/modules/checkout/yijiesppay/module.yijiesppay.php");
    $yijiespObj = new CHECKOUT_YIJIESPPAY;
    $get = $_GET;
    $sign = $get['sign'];
    unset($get['sign']);
    $mysign = $yijiespObj->getSignString($get);
    $queryString = '';
    foreach ($get as $k=>$v){
        $queryString .= "&".$k.'='.$v;
    }
    $queryString = trim($queryString,'&');

    if( $mysign == $sign ){
        //
        if($get['status'] == 'processing'){
            UpdateOrderStatus($get['merchOrderNo'], 11);
        }

        echo '<script>location.href="'.GetConfig('ShopPathSSL').'/finishorder.php?'.$queryString.'"</script>';
    }



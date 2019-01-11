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
    unset($get['context']);
    $mysign = $yijiespObj->getSignString($get);
    $queryString = '';
    foreach ($get as $k=>$v){
        $queryString .= "&".$k.'='.$v;
    }
    $queryString = trim($queryString,'&');
//
//    if( $mysign == $sign ){
        //
        if ($get){
            if($get['status'] == 'processing'){
                UpdateOrderStatus($get['merchOrderNo'], 11);
            }elseif ($get['status'] == 'fail'){
                UpdateOrderStatus($get['merchOrderNo'], 6);
            }
            echo '<script>location.href="'.GetConfig('ShopPathSSL').'/finishorder.php?'.$queryString.'"</script>';
        }elseif ($_POST){
//            echo 'success';
            file_put_contents(dirname(__FILE__).'/'.date('Ymd').'log.txt',"\r\n".date('Y-m-d H:i:s ')."POST内容:".json_encode($_POST),FILE_APPEND);
            $yijiespObj->vform($_POST);
//            $context = (array)json_decode($_POST['context']);
//            $urlString = '';
//            foreach ($context as $k => $v){
//                $urlString .= '&'.$k.'='.$v;
//            }
//            $requesturl = GetConfig('ShopPathSSL').'/checkout.php?'.trim($urlString,'&');
//            $html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body onLoad="document.dinpayForm.submit();"><form name="dinpayForm" id="dinpayForm" method="POST" action="'.$requesturl.'" >';
//            foreach($_POST as $k => $v){
//                $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
////                $html .='<input type="hidden" name="'.$k.'" value="'.str_replace("+"," ",($v)).'"/>';
//            }
//            $html .='</form></body></html>';

//            echo $html;

        }
//    }



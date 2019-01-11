<?php

require_once (realpath(__DIR__ . '/../../../'.'languages/english/modules/payment/yjfpaycnew.php'));
// $breadcrumb->add(NAVBAR_TITLE);
define('NAVBAR_TITLE_3', 'PayResult');
//if (isset ( $zco_notifie ))
//    $zco_notifier->notify ( 'NOTIFY_HEADER_START_PAYMENT_RESULT' );
$messageStack->reset ();
$breadcrumb->add ( NAVBAR_TITLE_1 );
$breadcrumb->add ( NAVBAR_TITLE_3 );


// 如果订单为空,直接跳转到网店首页
if (! isset ( $_GET ["merchOrderNo"] ) || empty ( $_GET ["merchOrderNo"] ) || ! isset ( $_GET ["orderNo"] ) || empty ( $_GET ["orderNo"] )) {
    zen_redirect ( zen_href_link ( FILENAME_DEFAULT ) );
}

// 获取返回信息
$orderNo = $_GET ['merchOrderNo'];
$orderCurrency = $_GET ['orderCurrency'];
$orderAmount = $_GET ['orderAmount'];
$orderResult = $_GET ['resultCode'].'  '.$_GET ['resultMessage'];
$orderStatus = $_GET ['status'];
$success = $_GET['success'];
$description = (isset ( $_GET ["description"] ) && ! empty ( $_GET ["description"] )) ? $_GET ["description"] : ' ';
// 判断订单状态,并修改数据库

    if($orderStatus === 'processing') {
        $messageStack->add_session ( 'payment_result', YJFPAY_PAYRESULT_PROCESSING, 'success' );
    } else if (!$success || $orderStatus === 'fail') {
        $messageStack->add_session ( 'payment_result', YJFPAY_PAYRESULT_FAIL, 'error' );
    } else if ($orderStatus === 'success') {
        $messageStack->add_session ( 'payment_result', YJFPAY_PAYRESULT_SUCCESS, 'success' );
    } else if ($orderStatus === 'authorizing') {
        $messageStack->add_session ( 'payment_result', YJFPAY_PAYRESULT_APPROVED, 'success' );
    }

<?php

error_reporting(E_ERROR | E_PARSE);
chdir(dirname(__FILE__).'/../../');

include_once("./gcp/common.php");

$errorMsg = false;

if($errorMsg){
	exit('<script>alert("'.$errorMsg.'"); history.go(-1);</script>');
}

$useSSL = true;
include('./config/config.inc.php');
include('./header.php');
if (!$cookie->isLogged(true))
    Tools::redirect($link->getPageLink('my-account', true));
if(empty($cart->id)){
	Tools::redirect('/');
}


$url = Configuration::get ( 'CCPAY_GATEWAY_DOMAIN' );

include('./modules/ccpay/ccpay.php');
 
$payment = new ccpay();

$query = $payment->commitPayment($cart);

$html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
	<body onLoad="document.dinpayForm.submit();"><meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport"><meta content="yes" name="apple-mobile-web-app-capable"><meta content="black" name="apple-mobile-web-app-status-bar-style"><meta content="telephone=no" name="format-detection"><form name="dinpayForm" method="post" action="'.$url.'" >';
	foreach($query as $k => $v){
		$html .='<input type=hidden name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
	}
	$html .= '</form><div class="container"></div>';
	$html .='</body></html>';
	echo $html;

include ('./footer.php');
?>
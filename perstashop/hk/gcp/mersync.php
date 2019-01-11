<?php 

error_reporting(E_ERROR | E_PARSE);
header("Content-type: application/json");
chdir(dirname(__FILE__).'/../');

include ("./gcp/config.php");
include ("./gcp/common.php");

include ('./config/config.inc.php');

$config_merchantkey = Configuration::get ( 'CCPAY_MERCHANTKEY' );


if($_GET['sign'] !="" && $config_merchantkey!="") {

	if(!gcp_checkSign($_SERVER['QUERY_STRING'], $config_merchantkey)){
		$hresult['status']="false";
		gcp_result ($hresult);
	}

	$v= explode(" v", CCPAY_VERSION);
	$hresult['plugin']=$v[0];
	$hresult['version']=$v[1];

	if(!empty($_GET['merchantkey'])){
		$merchantkey=$_GET['merchantkey'];
		Configuration::updateValue ( 'CCPAY_MERCHANTKEY',$merchantkey );
		$hresult['status']="true";
		gcp_result ($hresult);

	}
	if($_GET['gatewayDomain']!=""){
		$gatewaydomain=$_GET['gatewayDomain'];
		$redirecttype=$_GET['redirectType'];
		Configuration::updateValue ( 'CCPAY_GATEWAY_DOMAIN',$gatewaydomain );
		Configuration::updateValue ( 'CCPAY_REDIRECT_TYPE',$redirecttype );
		$hresult['status']="true";
		gcp_result ($hresult); 
	}
	 
	if(!empty($_GET['imgpath'])){
		$imgpath=$_GET['imgpath'];
		Configuration::updateValue ( 'CCPAY_MARK_BUTTON_IMG',$imgpath );
		$hresult['status']="true";
		gcp_result ($hresult);
	}
		
	if($_GET['timeTick']!=""){
		$hresult['status']="true";
		gcp_result ($hresult);
	}
}

$hresult['status']="false";
gcp_result ($hresult);

?>
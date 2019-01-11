<?php
error_reporting(E_ERROR | E_PARSE);
chdir(dirname(__FILE__).'/../../');

$html = '<script type="text/javascript">';
$html .= 'if (window != top){ top.location.href = location.href }';
$html .= '</script>';
echo $html;

include_once("./gcp/common.php");
$response = $_GET;
if($response=="")
  echo("<h2 class='page-heading'>Payment Error ! Response Error.</h2>");
  
$merchOrderNo 	 = $response['merchOrderNo'];
$webSite 		 = $_SERVER['HTTP_HOST'];
$orderAmount	 = $response["amountLoc"];
$webSitehistory  = $webSite.'/order-history';
$webSitesupport  = $webSite.'/contact-us';
$orderMessage 	 = $result['description']?$result['description']:$result['resultCode'];

if($response['status'] == 'success' || $response['status'] == 'authorizing') {

	echo ('<h2 class="page-heading" style="
    margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">Order payment is successfull!</h2><span style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">You have chosen the Credit Card Payment method.<br>Your order will be sent very soon.<br>For any questions or for further information.please contact our customer support.<br>Your order ID is: '.$merchOrderNo.'</span>');

} elseif ($response['status'] == 'processing') {

	echo ('<h2 class="page-heading"  style="
    margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">Your order payment is successfull!</h2><span style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">You have chosen the Credit Card Payment method.<br>We are processing your order.<br>For any questions or for further information.please contact our customer support.<br>Your order ID is: '.$merchOrderNo.'</span>');

} elseif ($response['status'] == 'fail') {
	echo '<h2 class="page-heading"  style="
    margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">An error occurred in the process of payment. </h2><span style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">You have chosen the Credit Card Payment method.<br>Your order payment fail.reason:<font style="color: #ED1717; font-weight:bold; ">'.$orderMessage.'</font><br>For any questions or for further information.please contact our customer support.<br>Your order ID is: '.$merchOrderNo.'</span>';
} else {
	echo "Payment Error!";
}

?>
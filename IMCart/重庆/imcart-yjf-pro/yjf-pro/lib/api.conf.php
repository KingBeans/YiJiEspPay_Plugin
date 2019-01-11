<?php


// 只能收取某一个域名的款项


define('API_DOMAIN','t0000.newdemo.zhcart.com');


// xxx.域名.com     www m


if($_GET['redirect_domain'])
{
	define('REDIRECT_DOMAIN',$_GET['redirect_domain']);
}
else
{
	define('REDIRECT_DOMAIN',API_DOMAIN);	
}





$_SESSION['redirect_domain']=REDIRECT_DOMAIN;






define('API_KEY','123456');


define('API_SECRET','20140418');


define('PRODUCT_URL','https://openapi.yijifu.net/gateway.html');
define('MERCHANT_ID','20160825020000752433');
define('MERCHANT_ckey','05a29a66557ad2f3634534a940d3577c');



?>
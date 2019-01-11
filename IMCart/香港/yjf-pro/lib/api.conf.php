<?php


// 只能收取某一个域名的款项


define('API_DOMAIN','www.baidu.com');


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


define('PRODUCT_URL','https://hkopenapitest.yiji.com/gateway.html');
define('MERCHANT_ID','20180118010000000101');
define('MERCHANT_ckey','520064f47138834045ac4df31a2c85e7');



?>
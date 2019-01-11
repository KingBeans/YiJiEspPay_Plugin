<?php
define('IN_ECS', true);
require_once(ROOT_PATH . '/includes/init.php');

// # load lang files
$lang = dirname(__FILE__) . '/langs/' . $GLOBALS['_CFG']['lang'] . '/esp.php';
if (is_file($lang)) {
    include_once($lang);
}
global $_LANG;
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Your order has been received</title>
		<link rel="stylesheet" type="text/css" href="yjpay/templates/css/style.css">
		<link rel="stylesheet" type="text/css" href="yjpay/templates/css/return.css">
	</head>
<body>
	<?php 
		$status = $_GET['status'];
		if($status == 'fail') {
			$retmsg = $_LANG['yjpay_pay_fail'];

		} elseif ($status == 'processing') {

			$retmsg = $_LANG['yjpay_wait_notify'];

		} elseif ($status == 'authorizing' || $status == 'success') {
			$retmsg = $_LANG['user_yjpay_success_title'];
		}
	 ?>

	<h4 class="title" style="font-size: 1.12em; margin: .4em 0; color:#f60; font-family:Arial;"><?php echo $_LANG['yjpay_pay_ret_title']; ?></h4>
	<p class="note" style="color:grey;font-size: 14px;"><?php echo $retmsg;?></p>	

	<form name=loading> 
	　<p align=center>
		<font color="#0066ff" size="2"></font>
		<font color="#0066ff" size="2" face="Arial">...</font>
	　　<input type=text name=chart size=46 style="font-family:Arial; font-weight:bolder; color:#0066ff; background-color:#fef4d9; padding:0px; border-style:none;"> 	　　
	　　<input type=text name=percent size=47 style="color:#0066ff; text-align:center; border-width:medium; border-style:none;"> 

	　　<script>　 
			var bar=0　 
			var line="||"　 
			var amount="||"　 
			count()　 
			function count(){　 
				bar=bar+5　 
				amount =amount + line　 
				document.loading.chart.value=amount　 
				document.loading.percent.value=bar+"%"　 
				if (bar<99)　 
				{setTimeout("count()",100);}　 
				else　 
				{window.location = "/";}　 
			}
		</script> 
	　</p> 
	</form> 
	<p align="center"> Back to shop,
		<a style="text-decoration: none" href="/">
			<font color="#FF0000">click here</font>
		</a>
	.</p>
</body>
</html>
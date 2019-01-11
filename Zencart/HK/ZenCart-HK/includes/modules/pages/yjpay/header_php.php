<?php
if (MODULE_PAYMENT_MYCHECKOUT_STATUS != 'True'
	|| !isset($_SESSION['order_number_created'])) {
	die('error1!');
}
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
require(DIR_WS_CLASSES . 'order.php');
$order = new order($_SESSION['order_number_created']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
	<title><?php echo HEADING_TITLE; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
	<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $page_directory; ?>/css/styles.css" />
	<script type="text/javascript" src="<?php echo $page_directory; ?>/js/jquery.min-1.9.0.js"></script>
	<script type="text/javascript" src="<?php echo $page_directory; ?>/js/jquery/validate.min-1.11.0.js"></script>
</head>
<body>
<div id="page">
	<div id="header">
		<h1><?php echo HEADING_TITLE; ?></h1>
	</div>
	<div id="main">
		<p><?php echo TEXT_MYCHECKOUT_ORDER_NUMBER; ?> <strong><?php echo MODULE_PAYMENT_MYCHECKOUT_TRANSNO . '-' . $_SESSION['order_number_created']; ?></strong> | <?php echo TEXT_MYCHECKOUT_CURRENCY; ?> <strong><?php echo $order->info['currency']; ?></strong> | <?php echo TEXT_MYCHECKOUT_AMOUNT; ?> <strong><?php echo number_format(($order->info['total']) * $currencies->get_value($order->info['currency']), 2, '.', ''); ?></strong></p>
		<form id="mycheckout" method="post" action="<?php echo zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'); ?>">
    	<table width="100%" cellspacing="5">
    	<tbody>
    		<tr>
    			<th align="right"><?php echo TEXT_MYCHECKOUT_CARD_TYPE; ?></th>
				<td>
					<img src="<?php echo $page_directory; ?>/images/V.jpg" title="Visa" /> <img src="<?php echo $page_directory; ?>/images/M.jpg" title="Master" /> <img src="<?php echo $page_directory; ?>/images/J.jpg" title="JCB" />
				</td>
    		</tr>
    		<tr>
    			<th align="right"><em>*</em><?php echo TEXT_MYCHECKOUT_CARD_NUMBER; ?></th>
				<td><input type="text" class="input-text required creditcard" name="card_number" maxlength="16" /></td>
    		</tr>
    		<tr>
    			<th align="right"><em>*</em><?php echo TEXT_MYCHECKOUT_EXPIRY_DATE; ?></th>
				<td>
					<select class="s" name="card_month">
						<?php for ($i = 1; $i <= 12; $i++) { ?>
						<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
						<?php } ?>
					</select>
					 / 
					<select class="s" name="card_year">
						<?php $year = date('Y'); ?>
						<?php for ($i = 0; $i < 25; $i++) { ?>
						<option value="<?php echo substr($year + $i, -2, 2); ?>"><?php echo $year + $i; ?></option>
						<?php } ?>
					</select>
				</td>
    		</tr>
    		<tr>
    			<th align="right"><em>*</em><?php echo TEXT_MYCHECKOUT_CVV; ?></th>
				<td>
					<input type="password" class="input-text required digits" name="card_cvv" style="width:96px" maxlength="3" />
					<img onmouseout="$('#cvv').hide();" onmouseover="$('#cvv').show();" src="<?php echo $page_directory; ?>/images/cvv.gif" />
					<img id="cvv" src="<?php echo $page_directory; ?>/images/cvv.jpg" />
					<script type="text/javascript" src="http://risk.hdkhdkrisk.com/csid.js"></script>
				</td>
    		</tr>
    		<tr id="loading">
    			<th></th>
    			<td><img src="<?php echo $page_directory; ?>/images/loader.gif" /> <?php echo TEXT_MYCHECKOUT_PROCESSING; ?></td>
    		</tr>
    		<tr>
    			<th></th>
    			<td><button type="submit" id="submit"><?php echo TEXT_MYCHECKOUT_SUBMIT; ?></button></td>
 			</tr>
    	</tbody>
    	</table>
	    </form>
	</div>
	<div id="footer">
		<address></address>
	</div>
</div>
<script type="text/javascript">
$('#mycheckout').validate({
	submitHandler: function(form) {
		$('#loading').show();
		$('#submit').attr('disabled','disabled');
		form.submit();
	},
	errorPlacement:function(error,element){},
});
</script>
</body>
</html>
<?php die; ?>
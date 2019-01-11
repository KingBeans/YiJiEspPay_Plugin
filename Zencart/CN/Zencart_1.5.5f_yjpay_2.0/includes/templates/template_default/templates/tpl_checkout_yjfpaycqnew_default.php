<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_success.<br />
 * Displays confirmation details after order has been successfully processed.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_checkout_success_default.php 16435 2010-05-28 09:34:32Z drbyte $
 */
  // var_dump($_SESSION['yjhk_payment_method_messages']);
if(!isset($_SESSION['cqnew_payment_method_messages'])) {
	echo 'Net Work Error!';
} else {
	echo $_SESSION['cqnew_payment_method_messages'];
}
  
  // unset($_SESSION['yjhk_payment_method_messages']);
  // var_dump($order->info);
  // var_dump($_SESSION['payment']);
  // die();

?>

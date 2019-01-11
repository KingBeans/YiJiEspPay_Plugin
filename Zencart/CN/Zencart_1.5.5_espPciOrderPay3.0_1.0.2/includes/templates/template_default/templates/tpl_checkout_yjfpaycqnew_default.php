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
//if(!isset($_SESSION['cqnew_payment_method_messages'])) {
//	echo 'Net Work Error!';
//} else {
//	echo $_SESSION['cqnew_payment_method_messages'];
//}
  
  // unset($_SESSION['yjhk_payment_method_messages']);
  // var_dump($order->info);
  // var_dump($_SESSION['payment']);
  // die();

?>
<div class="centerColumn" id="checkoutSuccess">

    <h1 id="checkoutSuccessHeading">
        <?php echo $messageStack->output('payment_result'); ?>
    </h1>
    <div id="checkoutresultreason"><?php echo TEXT_YOUR_ORDER_PAYRESULT . $orderResult ?></div>
    <div id="checkoutSuccessOrderNumber"><?php echo TEXT_YOUR_ORDER_NUMBER . $orderNo; ?></div>
    <div id="checkoutSuccessOrderAmount"><?php echo TEXT_YOUR_ORDER_AMOUNT . $orderAmount. " " . $orderCurrency; ?></div>
    <?php if($orderStatus === 'fail') {?>
        <div id="checkoutSuccessOrderAmount"><?php echo TEXT_PLEASE_TRY_AGIAN ; ?></div>
    <?php }elseif($orderStatus === 'authorizing'){?>
        <div id="checkoutSuccessOrderAmount"><?php echo TEXT_PLEASE_AUTHORIZING ; ?></div>
    <?php }elseif($orderStatus === 'processing'){?>
        <div id="checkoutSuccessOrderAmount"><?php echo TEXT_PLEASE_PROCESSING ; ?></div>
    <?php }else{?>
        <div id="checkoutSuccessOrderAmount"><?php echo TEXT_PLEASE_SUCCESS ; ?></div>
    <?php }?>

</div>

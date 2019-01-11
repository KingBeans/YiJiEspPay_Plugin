<?php
    # import application top
    // require('includes/application_top.php');
    require('includes/modules/payment/yjfpayc/common_functions.php');
    require('includes/modules/payment/yjfpayc/asynchronous_notification.php');
// echo "123";
// die();
    # read history
    // unset($_SESSION['cq_payment_method_messages']);
    // file_put_contents('E:/log/hkzencart/getdata.log', 'getdata:'.json_encode($_GET,true) . 'order_id:' . $orderID);
    $press = filter_input_array(INPUT_GET);
    $sign = $press['sign'];
    unset($press['sign']);
    if(yjfpayc_signature($press) == $sign ){

      if(filter_input(INPUT_GET,'status') == 'success' || filter_input(INPUT_GET,'status') == 'authorizing' || filter_input(INPUT_GET,'status') == 'processing') {
          $showTitle = 'your order has been received.';
          $showSubTitle = 'thank you for your purchase!';
          $showMesaage = 'We are processing your order and you will soon receive an email with details of the order. Once the order has shipped you will receive another email with a link to track its progress.';

          $html = '<div style="text-align: center;">';

          $html .= '<div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
          <h1 data-role="page-title">'.$showTitle.'</h1>
          </div>
          <h2 style="
          margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">'.$showSubTitle.'</h2>
          <p style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">'.$showMesaage.'</p>
          </div>';
          echo $html;
          // echo "pay success.";
          return;
      } elseif (filter_input(INPUT_GET,'status') == 'fail') {
          $orderNo        = filter_input(INPUT_GET,'merchOrderNo');
          $description    = filter_input(INPUT_GET,'description');
          $$showTitle = '';
          $showSubTitle = 'an error occurred in the process of payment';
          $showMesaage ='you order # '.$orderNo.' ,<br>description:'.$description.'<br>Click <a href="/">here</a> to continue shopping.';

          $html = '<div style="text-align: center;">';

          $html .= '<div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
              <h1 data-role="page-title">'.$showTitle.'</h1>
          </div>
          <h2 style="
          margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">'.$showSubTitle.'</h2>
          <p style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">'.$showMesaage.'</p>
          </div>';
          echo $html;
          // echo "Order pay success";
          return;
      }
    }

<?php
    # import application top
    if($_GET['status'] == 'success' || $_GET['status'] == 'authorizing' || $_GET['status'] == 'processing') {

        //只有支付成功或者待处理才清除购物车
//        $_SESSION ['cart']->reset ( true );
//        unset ( $_SESSION ['sendto'] );
//        unset ( $_SESSION ['billto'] );
//        unset ( $_SESSION ['shipping'] );
//        unset ( $_SESSION ['payment'] );
//        unset ( $_SESSION ['comments'] );
//        unset ( $_SESSION ['yjfpay_order_id'] );

        zen_redirect (zen_href_link ( FILENAME_CHECKOUT_SUCCESS, '', 'SSL' ));
        return;
    } elseif ($_GET['status'] == 'fail') {
        $orderNo        = $_GET['merchOrderNo'];
        $description    = $_GET['description'];
        $showTitle = '';
        $showSubTitle = 'an error occurred in the process of payment';
        $showMesaage ='you order # '.$orderNo.' ,<br>description:'.$description.'<br>Click <a target=_top href="/">here</a> to continue shopping.';

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
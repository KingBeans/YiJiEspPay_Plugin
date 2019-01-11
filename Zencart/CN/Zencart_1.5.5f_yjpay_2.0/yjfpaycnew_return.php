<?php
    # import application top
    if($_GET['status'] == 'success' || $_GET['status'] == 'authorizing' || $_GET['status'] == 'processing') {
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
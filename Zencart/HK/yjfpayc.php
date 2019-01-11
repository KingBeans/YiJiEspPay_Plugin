<?php
    # import application top
    require('includes/application_top.php');
    require('includes/modules/payment/yjfpayc/common_functions.php');

    # read history
    unset($_SESSION['yjhk_payment_method_messages']);
    // file_put_contents('E:/log/hkzencart/getdata.log', 'getdata:'.json_encode($_GET,true) . 'order_id:' . $orderID);
    if($_GET['status'] == 'success' || $_GET['status'] == 'authorizing' || $_GET['status'] == 'processing') {
        $showTitle = 'your order has been received.';
        $showSubTitle = 'thank you for your purchase!';
        $showMesaage = 'We are processing your order and you will soon receive an email with details of the order. Once the order has shipped you will receive another email with a link to track its progress.';

        $html .= '<div style="text-align: center;">
                     <div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
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
        $$showTitle = '';
        $showSubTitle = 'an error occurred in the process of payment';
        $showMesaage ='you order # '.$orderNo.' ,<br>description:'.$description.'<br>Click <a href="/">here</a> to continue shopping.';

        $html .= '<div style="text-align: center;">
                     <div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
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
    
    $orderID = intval($_GET['order_id']);
    // file_put_contents('E:/log/hkzencart/isdo.log', 'is do' . $orderID);
    
    $history = read_pay_history($orderID);

    if ($history->EOF) return;

    $options = json_decode(base64_decode($history->fields['add_data']), true);
    $orderNo = date('YmdHis') . rand(10000, 999999);
    
    $returnUrl = zen_href_link('yjfpayc.php', '', 'SSL', false, false, true);
    $notifyUrl = zen_href_link('yjfpayc_handler.php', '', 'SSL', false, false, true);
    $gateway = array(
        'service'   => 'cardAcquiringCashierPay',
        'partnerId' => MODULE_PAYMENT_YJFPAYC_PARTNER_ID,
        'orderNo'   => $orderNo,
        'returnUrl' => getModifyUrl($returnUrl),
        'notifyUrl' => getModifyUrl($notifyUrl),
        'language'     => MODULE_PAYMENT_YJFPAYC_PAYMENT_LANGUAGE
    );

    # update order no
    update_pay_history_order_no($orderID, $orderNo);

    /**
     *  modify return_url or notufy_url
     *  @return string url
     */
    function getModifyUrl($init_url)
    {
        $pos = strrpos($init_url,".html");
        if($pos) {
            $modify_url = substr($init_url,0,$pos);
            return $modify_url;
        } else {
            return $init_url;
        }    
    }


    function read_pay_history($order_id) {
        $sql
            = <<<EOF
            SELECT
                order_id,order_no,status,
                pay_total,pay_date,pay_status,pay_message,
                refund_date,refund_total,refund_reason,
                auth_date,auth_accept,auth_reason,auth_message,
                add_date,add_data
            FROM
                yjfpayc_history
            WHERE
                order_id = :order_id AND status = 0
EOF;
        global $db;

        # bind var and return value
        $sql = $db->bindVars($sql, ':order_id', $order_id, 'integer');
        return $db->Execute($sql);
    }

    function update_pay_history_order_no($orderID, $orderNo) {
        global $db;

        $update_array = array(
            array('fieldName' => 'order_no', 'type' => 'string', 'value' => $orderNo)
        );

        $db->perform(TABLE_YJFPAYC_HISTORY, $update_array, 'update', 'order_id=' . intval($orderID));
    }

    file_put_contents('E:/log/hkzencart/history_option.log', json_encode($options,true),FILE_APPEND);

    # set options
    $allOptions = array_merge($options, $gateway);

    $allOptions['sign'] = yjfpayc_signature($allOptions);

    if (MODULE_PAYMENT_YJFPAYC_GATEWAY_URL == 'True') {
        // $gatewayURL = YJFPAYC_PRODUCT_URL;
        $gatewayURL = YJFPAYC_DEBUG_URL;
    } else {
        $gatewayURL = YJFPAYC_PRODUCT_URL;
        // $gatewayURL = YJFPAYC_DEBUG_URL;
    }
?>
<html>
<body>
<form id="submitPayForm" action="<?php echo $gatewayURL; ?>" method="POST">
    <?php foreach ($allOptions as $name => $value) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value='<?php echo $value; ?>'/>
    <?php } ?>
</form>
<script type="text/javascript">
    var submitPayForm = document.getElementById('submitPayForm');
    submitPayForm.submit();
</script>
</body>
</html>
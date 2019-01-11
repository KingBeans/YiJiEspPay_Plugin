<?php
    # import application top
    require('includes/application_top.php');
    require('includes/modules/payment/yjfpaycnew/common_functions.php');

    # read history
    unset($_SESSION['cqnew_payment_method_messages']);

    # read history
    $orderID = intval($_GET['order_id']);
    $history = read_pay_history($orderID);

    if ($history->EOF) return;

    $options = json_decode(base64_decode($history->fields['add_data']), true);

    $orderNo = date('YmdHis') . rand(10000, 999999);


    $returnUrl = zen_href_link('yjfpaycnew_return.php', '', 'SSL', false, false, true);
    // $notifyUrl = 'http://cq.zencarttest.new.com/yjfpaycnew_handler.php.html';
    $notifyUrl = zen_href_link('yjfpaycnew_handler.php', '', 'SSL', false, false, true);

    $gateway = array(
        // 0905
        'service'   => 'espOrderPay',
        'partnerId' => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
        'orderNo'   => $orderNo,
        // 外部订单号
        'merchOrderNo' => $orderID,
        'returnUrl' => getModifyUrl($returnUrl),
        'notifyUrl' => getModifyUrl($notifyUrl),
        'language'     => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE
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
                yjfpaycnew_history
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

    # set options
    $allOptions = array_merge($options,$gateway);

    $json_option = json_encode($allOptions,true);

    $allOptions['sign'] = yjfpaycnew_signature($allOptions);

    if (MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL == 'True') {
        $gatewayURL = YJFPAYC_DEBUG_URL;
    } else {
        $gatewayURL = YJFPAYC_PRODUCT_URL;
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

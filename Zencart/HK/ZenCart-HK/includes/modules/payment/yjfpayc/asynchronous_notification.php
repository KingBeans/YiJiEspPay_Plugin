<?php

    # import common functions
    require(DIR_WS_MODULES . '/payment/yjfpayc/common_functions.php');

    /* notify data */
   // filter_input_array(INPUT_POST) = array(
   //              'orderNo' => '20151025874081445761309',
   //              'merchOrderNo' => '38',
   //              'notifyTime' => '2016-10-17 16:19:27',
   //              'resultCode' => 'EXECUTE_SUCCESS',
   //              'sign' => '4254a8eb7b5201d8134361ea00b41642',
   //              'resultMessage' => '成功',
   //              'outOrderNo' => '2015102587408',
   //              'version' => '1.0',
   //              'protocol' => 'httpPost',
   //              'service' => 'cardAcquiringCashierPay',
   //              'status' => 'authorizing',
   //              // 'status' => 'success',
   //              'signType' => 'MD5',
   //              'partnerId' => '20140526020000027815',
   //              'description' => 'authoriziing order infos',
   //          );

   //  file_put_contents('E:/log/hkzencart/post_auth.log', json_encode(filter_input_array(INPUT_POST),true));

    global $db;

    $sign = array_key_pop(filter_input_array(INPUT_POST), 'sign');

    # check sign security
    if ($sign == yjfpayc_signature(filter_input_array(INPUT_POST))) {
    // if (true) {
        # read order status
        $orderID = filter_input(INPUT_POST,'merchOrderNo');

        // file_put_contents('E:/log/hkzencart/merorder', filter_input(INPUT_POST,'merchOrderNo'));
        // file_put_contents('E:/log/hkzencart/notify.log', json_encode(filter_input_array(INPUT_POST),true));

        $status  = strtolower(filter_input(INPUT_POST,'status'));

        if ($orderHistory = read_pay_history($db, $orderID)) {
            # process notify status
            if ($status == 'success') {
                do_pay_success($db, $orderHistory);
            } else if ($status == 'authorizing') {
                do_pay_authorizing($db, $orderHistory);
            } else if ($status == 'fail') {
                do_pay_fail($db, $orderHistory);
            }
        }
    }

    /**
     * @param       $db
     * @param mixed $orderHistory
     */
    function do_pay_success($db, $orderHistory) {
        $order_id      = $orderHistory->fields['order_id'];
        $statusHistory = array(
            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id),
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_PAYMENT_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            // array('fieldName' => 'comments', 'type' => 'string', 'value' => filter_input(INPUT_POST,'resultMessage') . '(' . filter_input(INPUT_POST,'resultCode') . ')')
            array('fieldName' => 'comments', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description') . '(' . filter_input(INPUT_POST,'resultCode') . ')')
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_PAYMENT_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 3),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'success'),
            // array('fieldName' => 'pay_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'resultMessage') . '(' . filter_input(INPUT_POST,'resultCode') . ')')
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description') . '(' . filter_input(INPUT_POST,'resultCode') . ')')
        );

        $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory);
        $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $order_id);
        $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $order_id);
    }

    function do_pay_authorizing($db, $orderHistory) {
        # order id
        $orderID       = $orderHistory->fields['order_id'];
        $statusHistory = array(
            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $orderID),
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_AUTHORIZE_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            // array('fieldName' => 'comments', 'type' => 'string', 'value' => filter_input(INPUT_POST,'authorizingInfo'))
            array('fieldName' => 'comments', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description'))
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_AUTHORIZE_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 2),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'authorizing'),
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'resultMessage') . '(' . filter_input(INPUT_POST,'resultCode') . ')'),
            // array('fieldName' => 'pay_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description')),
            // array('fieldName' => 'auth_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'authorizingInfo'))
            array('fieldName' => 'auth_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description'))
        );

        file_put_contents('E:/log/hkzencart/auth.log', 'historydata:' . json_encode($historyUpdate,true));

        $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory);
        $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $orderID);
        $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $orderID);

    }

    function do_pay_fail($db, $orderHistory) {
        $order_id      = $orderHistory->fields['order_id'];
        $statusHistory = array(
            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id),
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_FAIL_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'comments', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description'))
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYC_FAIL_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 5),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'fail'),
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => filter_input(INPUT_POST,'description'))
        );

        $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory);
        $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $order_id);
        $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $order_id);
    }

    function read_pay_history($db, $orderID) {
        $sql
                 = <<<EOF
            SELECT
                order_no,order_id,`status`,`pay_total`,pay_date,pay_status,pay_message
            FROM
                yjfpayc_history
            WHERE
                order_id = :order_id AND `status` < 3
EOF;
        $cmdSQL  = $db->bindVars($sql, ':order_id', $orderID, 'string');
        $history = $db->Execute($cmdSQL);
        # check history value and status
        return ($history->EOF) ? false : $history;
    }

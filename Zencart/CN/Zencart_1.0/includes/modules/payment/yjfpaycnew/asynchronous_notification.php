<?php

    # import common functions
    require(DIR_WS_MODULES . '/payment/yjfpaycnew/common_functions.php');

    $post_json = json_encode($_POST,true);
    global $db;
    // echo "post arrar";
    // var_dump($_POST);
    $sign = array_key_pop_new($_POST, 'sign');

    # check sign security
    if ($sign == yjfpaycnew_signature($_POST)) {
        # read order status
        $orderID = $_POST['merchOrderNo'];
        $status  = strtolower($_POST['status']);

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
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $_POST['resultCode'] . ')')
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 3),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'success'),
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $_POST['resultCode'] . ')')
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
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'comments', 'type' => 'string', 'value' => $_POST['authorizingInfo'])
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 2),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'authorizing'),
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $_POST['resultCode'] . ')'),
            array('fieldName' => 'auth_message', 'type' => 'string', 'value' => $_POST['authorizingInfo'])
        );

        $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory);
        $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $orderID);
        $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $orderID);

    }

    function do_pay_fail($db, $orderHistory) {
        $order_id      = $orderHistory->fields['order_id'];
        $statusHistory = array(
            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id),
            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID),
            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $_POST['resultCode'] . ')')
        );

        $orderUpdate = array(
            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID)
        );

        $historyUpdate = array(
            array('fieldName' => 'status', 'type' => 'integer', 'value' => 5),
            array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
            array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'fail'),
            array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $_POST['resultCode'] . ')')
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
                yjfpaycnew_history
            WHERE
                order_id = :order_id AND `status` < 3
EOF;
        $cmdSQL  = $db->bindVars($sql, ':order_id', $orderID, 'string');
        $history = $db->Execute($cmdSQL);

        # check history value and status
        return ($history->EOF) ? false : $history;
    }
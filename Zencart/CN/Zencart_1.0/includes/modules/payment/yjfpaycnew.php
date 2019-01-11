<?php

    # declare yjfpaycnew submit page
    define('FILENAME_YJFPAYC_SUBMIT', 'yjfpaycnew.php');
    define('FILENAME_CHECKOUT_YJFPAY_CQ', 'checkout_yjfpaycqnew');
    # import common functions
    require(DIR_FS_CATALOG . DIR_WS_MODULES . '/payment/yjfpaycnew/common_functions.php');

    /**
     * YJF Pay plugins
     */
    class yjfpaycnew extends base {
        /**
         * @var string define var
         */
        var $code,
            $title,
            $description,
            $enabled,
            $payment,
            $sort_order,
            $params;

        var $submitUrl = false;

        function yjfpaycnew() {
            global $order;

            $this->code        = 'yjfpaycnew';
            $this->title       = MODULE_PAYMENT_YJFPAYCNEW_TEXT_TITLE;
            $this->description = MODULE_PAYMENT_YJFPAYCNEW_TEXT_DESCRIPTION_ADMIN;
            $this->sort_order  = defined('MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER') ? MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER : null;
            $this->enabled     = defined('MODULE_PAYMENT_YJFPAYCNEW_STATUS') ? MODULE_PAYMENT_YJFPAYCNEW_STATUS == 'True' : false;

            if (is_object($order)) $this->update_status();

            /*
            if (MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL == 'True') {
                $this->form_action_url = self::PRODUCT_URL;
            } else {
                $this->form_action_url = self::DEBUG_URL;
            }
            */
        }

        function update_status() {
            global $db, $order;

            if ($this->enabled && (int)MODULE_PAYMENT_YJFPAYCNEW_ZONE > 0 && isset($order->billing['country']['id'])) {
                $check_flag = false;
                $check      = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_YJFPAYCNEW_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
                while (!$check->EOF) {
                    if ($check->fields['zone_id'] < 1) {
                        $check_flag = true;
                        break;
                    } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
                        $check_flag = true;
                        break;
                    }
                    $check->MoveNext();
                }

                if ($check_flag == false) {
                    $this->enabled = false;
                }
            }
        }

        function  check() {
            global $db;
            if (!isset($this->_check)) {
                $check_query  = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_YJFPAYCNEW_STATUS'");
                $this->_check = $check_query->RecordCount();
            }

            return $this->_check;
        }

        function selection() {
            return array(
                'id'     => $this->code,
                'module' => $this->title . '<br/>' . MODULE_PAYMENT_YJFPAYCNEW_TEXT_DESCRIPTION
            );
        }

        function  javascript_validation() {
            return false;
        }

        function  pre_confirmation_check() {
            return false;
        }

        function  confirmation() {
            return array();
        }

        function process_button() {
            return false;
        }

        function before_process() {
            /*
                global $messageStack;


                if (!$_GET['referer'] || !$_GET['params'] || $_GET['referer'] != 'yjfpayc') {
                    $messageStack->add('error');
                    zen_redirect(zen_href_link(PAGE_SHOPPING_CART, '', 'SSL'));
                }

                # assign params
                $this->params = json_decode(base64_decode($_GET['params']), true);

                # check params valid
                if ($this->params == false) {
                    $messageStack->add('error');
                    zen_redirect(zen_href_link(PAGE_SHOPPING_CART, '', 'SSL'));
                }

                # check sign valid
                $value = $this->params;
                $sign  = array_key_pop($value, 'sign');

                if ($sign != $this->_signature($value)) {
                    $messageStack->add('error');
                    zen_redirect(zen_href_link(PAGE_SHOPPING_CART, '', 'SSL'));
                }

                # check result code
                if (!in_array($this->params['resultCode'], array('EXECUTE_SUCCESS', 'EXECUTE_PROCESSING'))) {
                    $messageStack->add('error');
                    zen_redirect(zen_href_link(PAGE_CHECKOUT_SHIPPING, '', 'SSL'));
                }
                */

            return true;
        }

        function after_process() {
            # global var
            global $insert_id, $db, $order;

            $order_total    = $this->_orderTotal($order);
            $order_detail   = $this->_optionOrderDetail($order);
            $order_products = $this->_optionOrderProducts($order);
            $service_option = $this->_optionService($order, $order_total, $insert_id);

            $options  = array_merge($order_products, $order_detail, $service_option);
            $add_data = base64_encode(json_encode($options));

            $order_history_array = array(
                array('fieldName' => 'order_no', 'type' => 'string', 'value' => '000000000000000000'),
                array('fieldName' => 'order_id', 'type' => 'integer', 'value' => $insert_id),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 0),

                array('fieldName' => 'pay_total', 'type' => 'currency', 'value' => $order_total),
                array('fieldName' => 'add_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'add_data', 'type' => 'string', 'value' => $add_data)
            );

            $order_update_array = array(
                array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID)
            );

            # write business process
            $db->perform(TABLE_YJFPAYC_HISTORY, $order_history_array);
            $db->perform(TABLE_ORDERS, $order_update_array, 'update', 'orders_id = ' . $insert_id);

            # set payment method message
            $_SESSION['cqnew_payment_method_messages'] = $this->_submitFrame($insert_id);
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_YJFPAY_CQ, '', 'SSL'));
            return false;
        }

        function  install() {
            global $db, $messageStack;

            if (defined('MODULE_PAYMENT_YJFPAYCNEW_STATUS')) {
                $messageStack->add_session('YjfPay module already installed.', 'error');
                zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=yjfpaycnew', 'NONSSL'));
                return 'failed';
            }

            if (!defined('MODULE_PAYMENT_YJFPAYCNEW_TEXT_TITLE')) {
                include(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/yjfpaycnew.php');
            }

            //$language_option = 'zen_cfg_select_drop_down(array(array("id"=>"en", "text"=>"English"), array("id"=>"jp", "text"=>"Japanese"),array("id"=>"de", "text"=>"Deutsch"),array("id"=>"esp", "text"=>"El español"),array("id"=>"fr", "text"=>"Français")),';

            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_STATUS_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_STATUS', 'True', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_STATUS_DESCRIPTION . "', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'),', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_NAME_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_NAME', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_NAME_DESCRIPTION . "', '6', '2', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_EMAIL_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_EMAIL', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION . "', '6', '3', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PARTNER_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PARTNER_ID_DESCRIPTION . "', '6', '4', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SECRET_KEY_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_SECRET_KEY', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SECRET_KEY_DESCRIPTION . "', '6', '5', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ACQUIRING_TYPE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION . "', '6', '1', 'zen_cfg_select_option(array(\'CRDIT\', \'YANDEX\'),', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SUBMIT_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SUBMIT_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_AUTHORIZE_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FAIL_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FAIL_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            //$db->Execute("insert into " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_TITLE ."', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE', 'en', '". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION ."', '6', '6',".$language_option.", now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE', 'en', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION . "', '6', '8', 'zen_cfg_select_option(array(\'en\', \'ja\',\'fr\',\'de\',\'esp\'),', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ZONE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_ZONE', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ZONE_DESCRIPTION . "', '6', '1', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL', 'True', '" . MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL_DESCRIPTION . "', '7', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_DESCRIPTION . "', '8', '8', now())");


            /**
             * zen_cfg_select_drop_down(array(array('id'=>'0', 'text'=>'Non-Compliant'), array('id'=>'1', 'text'=>'On')),
             *
             *
             $db->Execute("insert into " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_TITLE ."', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE', '0', '". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION ."', '6', '6','zen_cfg_select_drop_down(array(array(\'id\'=>\'en\'\, \'text\'=>\'English\')\, array(\'id\'=>\'jp\'\, \'text\'=>\'Japanese\')\,array(\'id\'=>\'de\', \'text\'=>\'Deutsch\')\,array(\'id\'=>\'esp\'\, \'text\'=>\'El español\')\,array(\'id\'=>\'fr\'\, \'text\'=>\'Français\')),', now())");
             *
             */


            $createHistoryTable
                = <<<EOF
            CREATE TABLE IF NOT EXISTS yjfpaycnew_history (
                order_id            INT NOT NULL PRIMARY KEY,
                order_no            CHAR(20) NOT NULL,

                status              TINYINT NOT NULL DEFAULT 1
                                    COMMENT "1:Processing,2:Authorize,3:Payed,4:Refund,5:Cancel",

                pay_total           DECIMAL(14,2),
                pay_date            DATETIME NULL,
                pay_status          VARCHAR(10) NOT NULL DEFAULT '',
                pay_message         VARCHAR(256) NOT NULL DEFAULT '',

                refund_date         DATETIME NULL,
                refund_total        DECIMAL(10,2) NOT NULL DEFAULT 0,
                refund_reason       VARCHAR(256) NOT NULL DEFAULT '',

                auth_date           DATETIME NULL,
                auth_accept         CHAR(5) NOT NULL DEFAULT 0 COMMENT "0:none,1:access,2:deny",
                auth_reason         VARCHAR(256) NOT NULL DEFAULT '',
                auth_message        VARCHAR(256) NOT NULL DEFAULT '',

                add_date            DATETIME NOT NULL,
                add_data            TEXT NOT NULL
            );
EOF;
            $db->Execute($createHistoryTable);


            return 'success';
        }

        function remove() {
            global $db;
            $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        }

        function keys() {
            return array(
                'MODULE_PAYMENT_YJFPAYCNEW_STATUS',
                'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_NAME',
                'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_EMAIL',
                'MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_SECRET_KEY',
                'MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE',
                'MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE',
                'MODULE_PAYMENT_YJFPAYCNEW_ZONE',
                'MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL',
                'MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER'

            );
        }

        function admin_notification($order_id) {
            # read yjfpayc history
            $history = $this->_readHistory($order_id);

            if ($history->EOF) {
                return '<td>' . MODULE_PAYMENT_YJFPAYCNEW_ADMIN_PAY_EXCEPTION . '</td>';
            }

            $output = '';
            if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/yjfpaycnew/admin_notification.php')) {
                include(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/yjfpaycnew/admin_notification.php');
            }

            return $output;
        }

        function _doRefund($orderID) {
            # global member
            global $db, $messageStack;

            $history      = $this->_readHistory($orderID);
            $refundReason = $_POST['refundReason'];

            if ($history->RecordCount() && !$history->EOF) {
                # check refund admount
                if (isset($_POST['refundAmount'])) {
                    $refundAmount = intval($_POST['refundAmount']);
                } else {
                    $refundAmount = $history->fields['pay_total'];
                }

                if ($refundAmount == false) {
                    $messageStack->add_session(MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_AMOUNT_TEXT, 'fail');
                    return true;
                }

                # do refund
                if (isset($_POST['cancel'])) {
                    $result = $this->_pay_cancel($orderID, $refundAmount, $refundReason);
                } else {
                    # check refund amount
                    if ($_POST['refundAmount'] > $history->fields['pay_total']) {
                        $messageStack->add_session(MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_AMOUNT_MORE_TEXT, 'fail');
                        return true;
                    }

                    $result = $this->_pay_refund($orderID, $refundAmount, $refundReason);
                }

                if ($result->success) {
                    # update history array
                    $update_history_array = array(
                        array('fieldName' => 'status', 'type' => 'integer', 'value' => 4),
                        array('fieldName' => 'refund_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                        array('fieldName' => 'refund_total', 'type' => 'integer', 'value' => $refundAmount),
                        array('fieldName' => 'refund_reason', 'type' => 'string', 'value' => $refundReason),
                        array('fieldName' => 'pay_total', 'type' => 'integer', 'value' => $history->fields['pay_total'] - $refundAmount)
                    );

                    if (isset($_POST['cancel']) || $refundAmount == $history['pay_total']) {
                        $order_status_history_array = array(
                            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $orderID),
                            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID),
                            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                            array('fieldName' => 'comments', 'type' => 'string', MODULE_PAYMENT_YJFPAYCNEW_STATUS_5)
                        );

                        $update_order_array = array(
                            array('fieldName' => 'orders_status', 'type' => 'int', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID)
                        );

                        # update orders status
                        $db->perform(TABLE_ORDERS, $update_order_array, 'update', 'orders_id=' . $orderID);

                    } else {
                        $order_status_history_array = array(
                            array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $orderID),
                            array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID),
                            array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                            array('fieldName' => 'comments', 'type' => 'string', MODULE_PAYMENT_YJFPAYCNEW_STATUS_4)
                        );
                    }

                    # update history and add status history
                    $db->perform(TABLE_YJFPAYC_HISTORY, $update_history_array, 'update', 'order_id=' . $orderID);
                    $db->perform(TABLE_ORDERS_STATUS_HISTORY, $order_status_history_array);
                }

                $messageStack->add_session($result->resultMessage, 'success');
            }

            return true;
        }

        function _doCapt($orderID, $amt = 0, $currency = 'USD') {
            # global var
            global $db, $messageStack;

            $history = $this->_readHistory($orderID);

            # check history record
            if ($history->RecordCount() && !$history->EOF) {
                # if current status is not authorize
                if ($history->fields['status'] != 2) return true;

                if (isset($_POST['authorize'])) {
                    $result = $this->_pay_authorize($orderID, $history->fields['order_no'], 'true', $_POST['resolveReason']);
                } else {
                    $result = $this->_pay_authorize($orderID, $history->fields['order_no'], 'false', $_POST['resolveReason']);

                    $update_history_array = array(
                        array('fieldName' => 'status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID)
                    );

                    $db->perform(TABLE_YJFPAYC_HISTORY, $update_history_array, 'update', 'order_id=' . $orderID);
                }

                $messageStack->add_session($result->resultMessage, 'success');
            }

            return true;
        }

        protected function _pay_cancel($orderID, $refundAmount, $refundReason) {
            # do cancel
            $submit = array(
                'orderNo'      => date('YmdHis') . rand(100000, 999999),
                'service'      => 'espRefund',
                'partnerId'    => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo' => $orderID,
                'refundAmount' => $refundAmount,
                'refundReason' => $refundReason,
                'returnUrl'    => '',
                'type'         => 'QUICK_REFUND'
            );

            $submit['sign'] = yjfpaycnew_signature($submit);
            $result         = $this->_execute($submit);

            return json_decode($result);
        }

        protected function _pay_refund($orderID, $refundAmount, $refundReason) {
            # do cancel
            $submit = array(
                'orderNo'      => date('YmdHis') . rand(100000, 999999),
                'service'      => 'espRefund',
                'partnerId'    => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo' => $orderID,
                'refundAmount' => $refundAmount,
                'refundReason' => $refundReason,
                'returnUrl'    => '',
                'type'         => 'DEFAULT_REFUND'
            );

            $submit['sign'] = yjfpaycnew_signature($submit);
            $result         = $this->_execute($submit);

            return json_decode($result);
        }

        protected function _pay_authorize($orderID, $orderNo, $isAccept, $resolveReason) {
            # do cancel
            $submit = array(
                'orderNo'         => date('YmdHis') . rand(100000, 999999),
                'service'         => 'espOrderJudgment',
                'partnerId'       => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo'    => $orderID,
                'resolveReason'   => $resolveReason,
                'isAccept'        => $isAccept,
                'originalOrderNo' => $orderNo,
                'returnUrl'       => '',
            );

            $submit['sign'] = yjfpaycnew_signature($submit);
            $result         = $this->_execute($submit);

            return json_decode($result);
        }

        protected function _execute($params) {
            # declare url var
            $url  = MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL ? YJFPAYC_DEBUG_URL : YJFPAYC_PRODUCT_URL;
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

            $result = curl_exec($curl);

            curl_close($curl);

            return $result;
        }

        /**
         * @return queryFactoryResult
         */
        protected function _readHistory($orderID) {
            $sql
                = <<<EOF
            SELECT
                order_id,order_no,status,
                pay_total,pay_date,pay_status,pay_message,
                refund_date,refund_total,refund_reason,
                auth_date,auth_accept,auth_reason,auth_message,add_date
            FROM
                yjfpaycnew_history
            WHERE
                order_id = :order_id
EOF;
            global $db;

            # bind var and return value
            $sql = $db->bindVars($sql, ':order_id', $orderID, 'integer');
            return $db->Execute($sql);
        }

        protected function _submitFrame($order_id) {
            # declare iframe source
            $submitUrl = zen_href_link(FILENAME_YJFPAYC_SUBMIT, 'order_id=' . $order_id, 'SSL', false, false, true);

            return <<<EOF
            <iframe frameborder="0" scrolling="no" src="$submitUrl"
                    marginheight="0" marginwidth="0"  width="100%" height="600px">
            </iframe>
EOF;
        }

        /**
         * return order product payment format
         *
         * @param mixed $order order information
         *
         * @return array
         */
        protected function _optionOrderProducts($order) {
            # declare var option products
            $optionProducts = array();

            foreach ($order->products as $product) {
                array_push($optionProducts, array(
                    'goodsNumber'          => $product['model'],
                    'goodsName'            => $this->_filterValue($product['name']),
                    'goodsCount'           => $product['qty'],
                    'itemSharpProductcode' => $product['model'],
                    'itemSharpUnitPrice'   => $product['final_price']
                ));
            }
            return array('goodsInfoList' => json_encode($optionProducts, true));

            // return array('goodsInfoOrders' => json_encode($optionProducts, JSON_UNESCAPED_UNICODE));
        }

        /**
         * return submit service options
         *
         * @param mixed $order   order information
         * @param mixed $total   total information
         * @param mixed $orderID order no information
         *
         * @return array
         */
        protected function _optionService($order, $total, $orderID) {
            session_start();
            $session_id = session_id();
            return array(
                'userId'        => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'currency'      => $order->info['currency'],
                // 'orderAmount'   => $total,
                'amount'        => $total,
                'webSite'       => $_SERVER['HTTP_HOST'], # 'www.baidu.com'
                'merchOrderNo'  => $orderID,
                // yoko
                'memo'        => $this->_filterValue($order->info['comments']),
                'acquiringType' => MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE,
                // yoko
                'deviceFingerprintId' => $session_id
            );
        }

        protected function _filterValue($value) {
            return str_replace(array("'", '"'), ' ', $value);
        }

        /**
         * return order detail information
         *
         * @param mixed $order 订单信息
         *
         * @return array
         */
        protected function _optionOrderDetail($order) {
            # declare local var
            $billing  = $order->billing;
            $customer = $order->customer;
            $ship     = $order->ship ? $order->ship : $billing;

            $detail = array(
                'ipAddress'           => zen_get_ip_address(),
                'billtoCountry'       => $billing['country']['iso_code_2'],
                'billtoState'         => $this->_filterValue($billing['state']),
                'billtoCity'          => $this->_filterValue($billing['city']),
                'billtoStreet'        => $this->_filterValue($billing['street_address']),
                'billtoPostalcode'    => $billing['postcode'],

                'billtoFirstname'     => $this->_filterValue($billing['firstname']),
                'billtoLastname'      => $this->_filterValue($billing['lastname']),
                'billtoEmail'         => $customer['email_address'],
                'billtoPhonenumber'   => $customer['telephone'],

                'shiptoCountry'       => $ship['country']['iso_code_2'],
                'shiptoState'         => $this->_filterValue($ship['state']),
                'shiptoCity'          => $this->_filterValue($ship['city']),
                'shiptoStreet'        => $this->_filterValue($ship['street_address']),
                'shiptoPostalcode'    => $ship['postcode'],

                'shiptoFirstname'     => $this->_filterValue($ship['firstname']),
                'shiptoLastname'      => $this->_filterValue($ship['lastname']),
                'shiptoEmail'         => $customer['email_address'],
                'shiptoPhonenumber'   => $customer['telephone'],

                'logisticsFee'        => $order->info['shipping_cost'],
                'logisticsMode'       => $order->info['shipping_method'],
                'customerEmail'       => $customer['email_address'],
                'customerPhonenumber' => $customer['telephone'],
                'merchantEmail'       => MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_NAME,
                'merchantName'        => MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_EMAIL,
            );

            // return array('attachDetails' => json_encode($detail, JSON_UNESCAPED_UNICODE));
            return array('orderDetail' => json_encode($detail,true));

        }

        /**
         * @param $order
         *
         * @return mixed
         */
        protected function _orderTotal($order) {
            return $order->info['total'];
            /*
            if (MODULE_ORDER_TOTAL_INSTALLED) {
                echo 'total:', MODULE_ORDER_TOTAL_INSTALLED, 'install', ' < br />';
                echo DIR_WS_CLASSES . 'order_total . php';

                # include order total module
                require(DIR_WS_CLASSES . 'order_total . php');

                $order_total_modules = new order_total();
                $order_totals        = $order_total_modules->process();

                foreach ($order_totals as $total) {
                    if ($total['code'] == 'ot_total') {
                        return $total['value'];
                    }
                }
            }

            return $order->info['total'];
            */
        }


    }

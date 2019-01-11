<?php

    # declare yjfpaycnew submit page
    define('FILENAME_YJFPAYC_SUBMIT', 'yjfpaycnew.php');
    define('FILENAME_CHECKOUT_YJFPAY_CQ', 'checkout_yjfpaycqnew');
    # import common functions
    require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/yjfpaycnew/common_functions.php');

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
            $params,
            $order_prefix;

        var $submitUrl = false;
        var $order_status = DEFAULT_ORDERS_STATUS_ID;

        function __construct() {

            global $order;

            $this->code        = 'yjfpaycnew';
            $this->title       = MODULE_PAYMENT_YJFPAYCNEW_TEXT_TITLE;
            $this->description = MODULE_PAYMENT_YJFPAYCNEW_TEXT_DESCRIPTION_ADMIN;
            $this->sort_order  = defined('MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER') ? MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER : null;
            $this->enabled     = defined('MODULE_PAYMENT_YJFPAYCNEW_STATUS') ? MODULE_PAYMENT_YJFPAYCNEW_STATUS == 'True' : false;
            $this->order_prefix= defined('MODULE_PAYMENT_YJFPAYCNEW_ORDER_PREFIX') && MODULE_PAYMENT_YJFPAYCNEW_ORDER_PREFIX != null ? MODULE_PAYMENT_YJFPAYCNEW_ORDER_PREFIX : 'zen';

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
            global $order;

            $expires_month [] = array (
                "id" => "",
                "text" => MODULE_PAYMENT_YJFPAY_TEXT_MONTH
            );
            $expires_year [] = array (
                "id" => "",
                "text" => MODULE_PAYMENT_YJFPAY_TEXT_YEAR
            );
            for($i = 1; $i < 13; $i ++) {
                $expires_month [] = array (
                    'id' => sprintf ( '%02d', $i ),
                    'text' => strftime ( '%m', mktime ( 0, 0, 0, $i, 1, 2000 ) )
                );
            }

            $today = getdate ();
            for($i = $today ['year']; $i < $today ['year'] + 20; $i ++) {
                $expires_year [] = array (
                    'id' => strftime ( '%Y', mktime ( 0, 0, 0, 1, 1, $i ) ),
                    'text' => strftime ( '%Y', mktime ( 0, 0, 0, 1, 1, $i ) )
                );
            }



            $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

            $selection = array (
                'id' => $this->code,
                'module' => $this->title . '<br/>' . MODULE_PAYMENT_YJFPAYCNEW_TEXT_DESCRIPTION,
                'fields' => array (
                    array (
                        'title' => MODULE_PAYMENT_YJFPAY_TEXT_CREDIT_CARD_NUMBER,
                        'field' => zen_draw_input_field ( 'yjfpay_cardNo', '', 'id="yjfpay_cardNo" autocomplete="off"  onpaste="return false;" oncopy="return false;" maxlength="16"' ) . zen_draw_hidden_field ( 'yjfpay_os', '', 'id="yjfpay_os"' ) . zen_draw_hidden_field ( 'yjfpay_brower', '', 'id="yjfpay_brower"' ) . zen_draw_hidden_field ( 'yjfpay_brower_lang', '', 'id="yjfpay_brower_lang"' ) . zen_draw_hidden_field ( 'yjfpay_time_zone', '', 'id="yjfpay_time_zone"' ) . zen_draw_hidden_field ( 'yjfpay_resolution', '', 'id="yjfpay_resolution"' ) . zen_draw_hidden_field ( 'yjfpay_is_copycard', '0', 'id="yjfpay_is_copycard"' )
                    ),

                    array (
                        'title' => MODULE_PAYMENT_YJFPAY_TEXT_CREDIT_CARD_CVV,
                        'field' => zen_draw_password_field ( 'yjfpay_cvv', '', 'id="yjfpay_cvv" autocomplete="off" size="4" oncopy="return false;" maxlength="4"' . $onFocus )
                    ),

                    array (
                        'title' => MODULE_PAYMENT_YJFPAY_TEXT_CREDIT_CARD_EXPIRES,
                        'field' => zen_draw_pull_down_menu ( 'yjfpay_expires_month', $expires_month, '-------', 'id="yjfpay_expires_month"' . $onFocus ) . '&nbsp;' . zen_draw_pull_down_menu ( 'yjfpay_expires_year', $expires_year, '-------', 'id="yjfpay_expires_year"' . $onFocus ) . zen_draw_hidden_field ( 'yjfpay_mypretime', '0', 'id="yjfpay_mypretime"' )
                    ),


                )
            );
            return $selection;
        }

        function  javascript_validation() {
            $js = $this->func_init_JS () . "\n";
            $js .= ' var yjfpay_Today = new Date();' . "\n" . ' var yjfpay_Now_Hour = yjfpay_Today.getHours();' . "\n" . ' var yjfpay_Now_Minute = yjfpay_Today.getMinutes();' . "\n" . '  var yjfpay_Now_Second = yjfpay_Today.getSeconds();' . "\n" . '  var yjfpay_mysec = (yjfpay_Now_Hour*3600)+(yjfpay_Now_Minute*60)+yjfpay_Now_Second;' . "\n";
            $js .= ' if (payment_value == "' . $this->code . '") {' . "\n" . ' var yjfpay_number = document.getElementById("yjfpay_cardNo").value;' . "\n" . '    var yjfpay_cvv = document.getElementById("yjfpay_cvv").value;' . "\n" . '    var yjfpay_expires_month = document.getElementById("yjfpay_expires_month").value;' . "\n" . '    var yjfpay_expires_year = document.getElementById("yjfpay_expires_year").value;' . "\n";
            $js .= ' if (!checkCardNum(yjfpay_number)) {' . "\n" . '   error_message = error_message + "' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_CARD_NUM . '";' . "\n" . '      error = 1;' . "\n" . '    }' . "\n";
            $js .= ' if (!checkCvv(yjfpay_cvv)) {' . "\n" . '      error_message = error_message + "' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_CVV . '";' . "\n" . '      error = 1;' . "\n" . '    }' . "\n";
            $js .= ' if (!checkExpdate(yjfpay_expires_month)) {' . "\n" . '    error_message = error_message + "' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_EXP_MONTH . '";' . "\n" . '      error = 1;' . "\n" . '    }' . "\n";
            $js .= ' if (!checkExpdate(yjfpay_expires_year)) {' . "\n" . '     error_message = error_message + "' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_EXP_YEAR . '";' . "\n" . '      error = 1;' . "\n" . '    }' . "\n";

            // 防刷新(防止两次提交时间过短,具体时间可以设置)
            $js .= '  if(!error){' . "\n" . '  if((yjfpay_mysec - document.getElementById("yjfpay_mypretime").value)>30) { ' . "\n" . ' document.getElementById("yjfpay_mypretime").value=yjfpay_mysec;' . "\n" . '} else { ' . "\n" . ' alert("' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_REFRESH . '"); ' . "\n" . ' return false; ' . "\n" . '} ' . "\n" . '} ' . "\n";
            $js .= '}' . "\n";
            return $js;
        }

        /**
         * 订单交易
         */
        function pre_confirmation_check() {
            global $insert_id, $db, $order;
            // 构造参数
            $this->confirmation ();
            // 支付数据
            $order_total    = $this->_orderTotal($order);
            $order_detail   = $this->_optionOrderDetail($order);
            $order_products = $this->_optionOrderProducts($order);
            $service_option = $this->_optionService($order, $order_total, $_SESSION ['yjfpay_order_id']);
//            $post_data = $this->_optionPost();

            $options  = array_merge($order_products, $order_detail, $service_option);
            $options['sign'] = yjfpaycnew_signature($options);
            file_put_contents(DIR_WS_INCLUDES.'../logs/Options.log',"\r\n".date('Y-m-d H:i:s ')."Options内容:".json_encode($options),FILE_APPEND);
            $add_data = base64_encode(json_encode($options));

            //创建订单历史
            $order_history_array = array(
                array('fieldName' => 'order_no', 'type' => 'string', 'value' => '000000000000000000'),
                array('fieldName' => 'order_id', 'type' => 'integer', 'value' => $_SESSION ['yjfpay_order_id']),
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
            $db->perform(TABLE_ORDERS, $order_update_array, 'update', 'orders_id = ' . $_SESSION ['yjfpay_order_id']);

            # set payment method message
//            $_SESSION['cqnew_payment_method_messages'] = $this->_submitFrame($insert_id);
            //进行交易
            $resultData = $this->payment_submit ($options );
            $success = $resultData['success'];
            $status = $resultData['status'];
            $orderID = str_replace($this->order_prefix,'',$resultData['merchOrderNo']);
            $returnParam = '';
            foreach ( $resultData as $key => $val ) {
                $returnParam .= $key . '=' . $this->formatEmpty ( $val ) . "&";
            }

            $returnParam = trim($returnParam).'&orderAmount='.$order_total.'&orderCurrency='.$order->info['currency'];
            $returnUrl = zen_href_link ( FILENAME_CHECKOUT_YJFPAY_CQ, '', 'SSL' );
            if (strstr ( $returnUrl, "?" )) {
                $returnUrl = $returnUrl . '&' . $returnParam;
            } else {
                $returnUrl = $returnUrl . '?' . $returnParam;
            }

            // 是否清缓存
            $isClearSession = ($success && $status !='fail') ? true : false ;
            // 清除附加信息缓存
            unset ( $_SESSION ['additionInfo'] );

            $orderHistory = $this->read_pay_history($db, $orderID);

            # process notify status
            if ($status == 'success') {
                $this->do_pay_success($db, $orderHistory,$resultData);
                $this->resetCart($isClearSession);
                zen_redirect (zen_href_link ( FILENAME_CHECKOUT_SUCCESS, '', 'SSL' ));
            } else if ($status == 'authorizing') {
                $this->resetCart($isClearSession);
                $this->do_pay_authorizing($db, $orderHistory,$resultData);
            } else if ($status == 'fail') {
                $this->do_pay_fail($db, $orderHistory,$resultData);
                echo "<script language='javascript'> alert('" . $resultData['resultMessage'] . "');</script>";
            }else if ($status === 'processing'){
                $this->resetCart($isClearSession);
                $this->do_pay_processing($db, $orderHistory,$resultData);
            }
            zen_redirect($returnUrl);
            exit ();
        }

        function  confirmation() {
            // 信用卡信息
            $additionInfo = array (
                'cardNo' => $this->formatEmpty ( $_POST ['yjfpay_cardNo'] ),
                'cardType'=>$this->formatEmpty ( $this->getCardTypeByCardNum($_POST ['yjfpay_cardNo']) ),
                'cardSecurityCode' => $this->formatEmpty ( $_POST ['yjfpay_cvv'] ),
                'cardExpireYear' => $this->formatEmpty ( $_POST ['yjfpay_expires_year'] ),
                'cardExpireMonth' => $this->formatEmpty ( $_POST ['yjfpay_expires_month'] ),
//                'os' => $_POST ['yjfpay_os'],
//                'brower' => $_POST ['yjfpay_brower'],
//                'browerLang' => $_POST ['yjfpay_brower_lang'],
//                'timeZone' => $_POST ['yjfpay_time_zone'],
//                'resolution' => $_POST ['yjfpay_resolution'],
            );

            // 校验信用卡信息
            $errorMsg = $this->validateCardInfo ( $additionInfo );
            if (! empty ( $errorMsg ) && strlen ( $errorMsg ) > 1) {
                $errorMsg = $errorMsg . ' ' . MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_RE_INPUT;
                // 校验信用卡校验日志
                echo "<script language='javascript'> alert('" . $errorMsg . "');window.history.go(-1);</script>";
                exit ();
            }

            // 下订单
            $this->create_order ();
            // 保存到Session中
            $_SESSION ['additionInfo'] = $additionInfo;
            return false;
        }

        function process_button() {
            return false;
        }

        function before_process() {
            return true;
        }

        function after_process() {
            # global var
//            global $insert_id, $db, $order;
//
//            $order_total    = $this->_orderTotal($order);
//            $order_detail   = $this->_optionOrderDetail($order);
//            $order_products = $this->_optionOrderProducts($order);
//            $service_option = $this->_optionService($order, $order_total, $insert_id);
//
//            $options  = array_merge($order_products, $order_detail, $service_option);
//            $add_data = base64_encode(json_encode($options));
//
//            $order_history_array = array(
//                array('fieldName' => 'order_no', 'type' => 'string', 'value' => '000000000000000000'),
//                array('fieldName' => 'order_id', 'type' => 'integer', 'value' => $insert_id),
//                array('fieldName' => 'status', 'type' => 'integer', 'value' => 0),
//
//                array('fieldName' => 'pay_total', 'type' => 'currency', 'value' => $order_total),
//                array('fieldName' => 'add_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
//                array('fieldName' => 'add_data', 'type' => 'string', 'value' => $add_data)
//            );
//
//            $order_update_array = array(
//                array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID)
//            );
//
//            # write business process
//            $db->perform(TABLE_YJFPAYC_HISTORY, $order_history_array);
//            $db->perform(TABLE_ORDERS, $order_update_array, 'update', 'orders_id = ' . $insert_id);
//
//            # set payment method message
//            $_SESSION['cqnew_payment_method_messages'] = $this->_submitFrame($insert_id);
//            zen_redirect(zen_href_link(FILENAME_CHECKOUT_YJFPAY_CQ, '', 'SSL'));
            return false;
        }

        function after_order_create($zf_order_id) {
            return true;
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

            //是否启用
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_STATUS_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_STATUS', 'True', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_STATUS_DESCRIPTION . "', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'),', now())");
            //merchant name
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_NAME_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_NAME', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_NAME_DESCRIPTION . "', '6', '2', now())");
            //merchant email
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_EMAIL_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_MERCHANT_EMAIL', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION . "', '6', '3', now())");
            //partnerID
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PARTNER_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PARTNER_ID_DESCRIPTION . "', '6', '4', now())");
            //file password
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FILE_PASSWORD_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_FILE_PASSWORD', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FILE_PASSWORD_DESCRIPTION . "', '6', '5', now())");
            //acquiring type
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ACQUIRING_TYPE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION . "', '6', '1', 'zen_cfg_select_option(array(\'CRDIT\', \'YANDEX\'),', now())");
            //submit status
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SUBMIT_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_SUBMIT_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            //payment status
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            //authorize status
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_AUTHORIZE_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            //fail status
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FAIL_STATUS_ID_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_FAIL_STATUS_ID_DESCRIPTION . "', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            //$db->Execute("insert into " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_TITLE ."', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE', 'en', '". MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION ."', '6', '6',".$language_option.", now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE', 'en', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION . "', '6', '8', 'zen_cfg_select_option(array(\'en\', \'ja\',\'fr\',\'de\',\'esp\'),', now())");
            //payment language
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ZONE_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_ZONE', '', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ZONE_DESCRIPTION . "', '6', '1', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
            //gateway url
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL', 'True', '" . MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL_DESCRIPTION . "', '7', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());");
            //order
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER', '0', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_DESCRIPTION . "', '8', '8', now())");
            //order prefix
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_PREFIX_TITLE . "', 'MODULE_PAYMENT_YJFPAYCNEW_ORDER_PREFIX', '" . '' . "', '" . MODULE_PAYMENT_YJFPAYCNEW_CONFIGURATION_ORDER_PREFIX_DESCRIPTION . "', '6', '2', now())");

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
                'MODULE_PAYMENT_YJFPAYCNEW_FILE_PASSWORD',
                'MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE',
                'MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID',
                'MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE',
                'MODULE_PAYMENT_YJFPAYCNEW_ZONE',
                'MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL',
                'MODULE_PAYMENT_YJFPAYCNEW_SORT_ORDER',
                'MODULE_PAYMENT_YJFPAYCNEW_ORDER_PREFIX'

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
                            array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID)
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
                'signType'     => 'RSA',
                'partnerId'    => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo' => $this->order_prefix.$orderID,
                'refundAmount' => $refundAmount,
                'refundReason' => $refundReason,
                'returnUrl'    => '',
                'type'         => 'QUICK_REFUND',
                'originalOrderNo'=>$this->order_prefix.$orderID
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
                'signType'     => 'RSA',
                'partnerId'    => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo' => $this->order_prefix.$orderID,
                'refundAmount' => $refundAmount,
                'refundReason' => $refundReason,
                'returnUrl'    => '',
                'type'         => 'DEFAULT_REFUND',
                'originalOrderNo'=>$this->order_prefix.$orderID
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
                'signType'        => 'RSA',
                'partnerId'       => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'merchOrderNo'    => $this->order_prefix.$orderID,
                'resolveReason'   => $resolveReason,
                'isAccept'        => $isAccept,
                'returnUrl'       => '',
            );

            $submit['sign'] = yjfpaycnew_signature($submit);
            $result         = $this->_execute($submit);

            return json_decode($result);
        }

        /**
         * 清空相关session
         */
        public function resetCart($isClearSession){
            //只有支付成功或者待处理才清除购物车
            if ($isClearSession){
                $_SESSION ['cart']->reset ( true );
                unset ( $_SESSION ['sendto'] );
                unset ( $_SESSION ['billto'] );
                unset ( $_SESSION ['shipping'] );
                unset ( $_SESSION ['payment'] );
                unset ( $_SESSION ['comments'] );
                unset ( $_SESSION ['yjfpay_order_id'] );
                unset ( $_SESSION['yjfOrderNo'] );
            }
        }
        protected function _execute($params) {

            # declare url var
            $url  = MODULE_PAYMENT_YJFPAYCNEW_GATEWAY_URL == 'True'? YJFPAYC_DEBUG_URL : YJFPAYC_PRODUCT_URL;
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
            $sql = "SELECT order_id,order_no,`status`,`pay_total`,pay_date,pay_status,pay_message, refund_date,refund_total,refund_reason,auth_date,auth_accept,auth_reason,auth_message,add_date FROM yjfpaycnew_history WHERE order_id = :order_id";
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
                    marginheight="0" marginwidth="0"  width="100%" height="600px" style="display:none">
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
            $_SESSION['yjfOrderNo'] = $orderNo = date('YmdHis') . rand(10000, 999999);
            $billing = $order->billing;
            $returnUrl = zen_href_link('yjfpaycnew_return.php', '', 'SSL', false, false, true);
            // $notifyUrl = 'http://cq.zencarttest.new.com/yjfpaycnew_handler.php.html';
            $notifyUrl = zen_href_link('yjfpaycnew_handler.php', '', 'SSL', false, false, true);

            $cardExpireYear   = trim($_SESSION ['additionInfo'] ['cardExpireYear']);
            $cardExpireMonth  = trim($_SESSION ['additionInfo'] ['cardExpireMonth']);
            $cardNo = trim($_SESSION ['additionInfo'] ['cardNo']);
            $cvv = trim($_SESSION ['additionInfo'] ['cardSecurityCode']);

            return array(
                'service'       =>'espPciOrderPay',
                'version'       =>'3.0',
                'orderNo'   => $orderNo,
                'partnerId' => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'signType'      => 'RSA',

                'userId'        => MODULE_PAYMENT_YJFPAYCNEW_PARTNER_ID,
                'currency'      => $order->info['currency'],
                'amount'        => $total,
                'webSite'       => $_SERVER['HTTP_HOST'], # 'www.baidu.com'
                'merchOrderNo'  => $this->order_prefix.$orderID,
                // yoko
                'memo'        => $this->_filterValue($order->info['comments']),
                'acquiringType' => MODULE_PAYMENT_YJFPAYCNEW_ACQUIRING_TYPE,
                // yoko
                'deviceFingerprintId' => $session_id,
                'returnUrl' => getModifyUrl($returnUrl),
                'notifyUrl' => getModifyUrl($notifyUrl),
                'language'     => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_LANGUAGE,
                'cardNo'=>signPublicKey($cardNo.$orderNo),
                'cvv'=>signPublicKey($cvv.$orderNo),
                'cardHolderFirstName'=>$this->_filterValue($billing['firstname']),
                'cardHolderLastName'=>$this->_filterValue($billing['lastname']),
                'expirationDate'=>sprintf('%s%s', substr($cardExpireYear,-2), $cardExpireMonth)
            );
        }

        /**
         * 组装卡号CVV等参数
         * @return array
         */
        protected function _optionPost(){
            global $order;
            $data = array();

            $billing = $order->billing;

            $cardExpireYear   = trim($_SESSION ['additionInfo'] ['cardExpireYear']);
            $cardExpireMonth  = trim($_SESSION ['additionInfo'] ['cardExpireMonth']);
            $cardNo = trim($_SESSION ['additionInfo'] ['cardNo']);
            $cvv = trim($_SESSION ['additionInfo'] ['cardSecurityCode']);

            $data['cardNo']  = signPublicKey($cardNo);
            $data['cvv']  = signPublicKey($cvv);
            $data['cardHolderFirstName']  = $this->_filterValue($billing['firstname']);
            $data['cardHolderLastName']  = $this->_filterValue($billing['lastname']);
            $data['expirationDate']  = sprintf('%s%s', substr($cardExpireYear,-2), $cardExpireMonth);

            return $data;

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
            global $currencies;
            # declare local var
            $billing  = $order->billing;
            $customer = $order->customer;
            $ship     = $order->ship ? $order->ship : $billing;

            $detail = array(
                'cardType'            => trim($_SESSION ['additionInfo'] ['cardType']),
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

//                'logisticsFee'        => $order->info['shipping_cost'],
                'logisticsFee'        => number_format ( $order->info ['shipping_cost'] * $currencies->get_value ( $order->info['currency'] ), 2, '.', '' ),
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
            global $currencies;
            return number_format ( $order->info ['total'] * $currencies->get_value ( $order->info['currency'] ), 2, '.', '' );
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

        /**
         * 提交支付请求
         * 其中分为两种方式提交，curl和普通的http提交
         *
         * @param
         *        	$payUrl
         * @param
         *        	$backupUrl
         * @param
         *        	$data
         * @return string
         */
        function payment_submit($data) {

            // 发送支付请求
            $resultJson = $this->_execute($data);

            $resultObject = $this->json_parser ( $resultJson );

            return (array)$resultObject;
        }

        /**
         * 解析XML格式的字符串
         *
         * @param string $str
         * @return 解析正确就返回解析结果,否则返回空,说明字符串不是XML格式
         */
        function json_parser($str) {
            if (empty ( $str )) {
                return '';
            }else{
                return json_decode($str);
            }

        }

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

        /*
	 * 通过普通的http发送post请求
	 * http_build_query($post_data, '', '&')用于生成URL-encode之后的请求字符串
	 * stream_context_create() 创建并返回一个流的资源
	 * @param string $url 请求地址
	 * @param array $post_data post键值对数据
	 * @return string
	 */
        function http_post($payUrl, $data) {
            $webSite = empty ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_HOST'] : $_SERVER ['HTTP_REFERER']; // 获取网站域名
            $options = array (
                'http' => array (
                    'method' => "POST",
                    'header' => "Accept-language: en\r\n" . "Cookie: foo=bar\r\n" . "referer:$webSite \r\n",
                    // "Authorization: Basic " . base64_encode("$username:$password").'\r\n',
                    'content-type' => "multipart/form-data",
                    'content' => $data,
                    'timeout' => 60
                )
            ); // 超时时间（单位:s）

            // 创建并返回一个流的资源
            $context = stream_context_create ( $options );
            $resultXml = file_get_contents ( $payUrl, false, $context );
            return $resultXml;
        }

        /**
         * @param       $db
         * @param mixed $orderHistory
         */
        function do_pay_success($db, $orderHistory,$resultData) {
            $order_id      = $orderHistory->fields['order_id'];
            $totalAmount = $orderHistory->fields['pay_total'];
            $statusHistory = array(
                array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id),
                array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID),
                array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $resultData['resultMessage'] . '  Order Id:'.$order_id. '  Total Amount:'.$totalAmount.'  Status:'.$resultData['status'].')')
            );

            $orderUpdate = array(
                array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_PAYMENT_STATUS_ID)
            );

            $historyUpdate = array(
                array('fieldName' => 'order_no', 'type' => 'integer', 'value' => $_SESSION['yjfOrderNo']),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 3),
                array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'success'),
                array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $resultData['resultCode'] . ')')
            );

            $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory,'update','orders_id = ' . $order_id);
            $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $order_id);
            $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $order_id);
        }

        function do_pay_authorizing($db, $orderHistory,$resultData) {
            # order id
            $orderID       = $orderHistory->fields['order_id'];
            $totalAmount = $orderHistory->fields['pay_total'];
            $statusHistory = array(
                array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $orderID),
                array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID),
                array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $resultData['resultMessage'] . '  Order Id:'.$orderID. '  Total Amount:'.$totalAmount.'  Status:'.$resultData['status'].')')
            );

            $orderUpdate = array(
                array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_AUTHORIZE_STATUS_ID)
            );

            $historyUpdate = array(
                array('fieldName' => 'order_no', 'type' => 'integer', 'value' => $_SESSION['yjfOrderNo']),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 2),
                array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'authorizing'),
                array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $resultData['resultCode'] . ')'),
            );

            $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory,'update','orders_id = ' . $orderID);
            $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $orderID);
            $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $orderID);

        }

        function do_pay_fail($db, $orderHistory,$resultData) {
            
            $order_id      = $orderHistory->fields['order_id'];
            $totalAmount = $orderHistory->fields['pay_total'];
            $statusHistory = array(
                array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id),
                array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID),
                array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $resultData['resultMessage'] . '  Order Id:'.$order_id. '  Total Amount:'.$totalAmount.'  Status:'.$resultData['status'].')')
            );

            $orderUpdate = array(
                array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_FAIL_STATUS_ID)
            );

            $historyUpdate = array(
                array('fieldName' => 'order_no', 'type' => 'integer', 'value' => $_SESSION['yjfOrderNo']),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 5),
                array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'fail'),
                array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $resultData['resultCode'] . ')')
            );

            $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory,'update','orders_id = ' . $order_id);
            $db->perform(TABLE_ORDERS, $orderUpdate, 'update', 'orders_id = ' . $order_id);
            $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $order_id);
        }

        function do_pay_processing($db, $orderHistory,$resultData){
            $order_id      = $orderHistory->fields['order_id'];
            $totalAmount = $orderHistory->fields['pay_total'];
            $statusHistory = array(
                array('fieldName' => 'orders_id', 'type' => 'integer', 'value' => $order_id,'update','orders_id = ' . $order_id),
                array('fieldName' => 'orders_status_id', 'type' => 'integer', 'value' => MODULE_PAYMENT_YJFPAYCNEW_SUBMIT_STATUS_ID),
                array('fieldName' => 'date_added', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'comments', 'type' => 'string', 'value' => '(' . $resultData['resultMessage'] . '  Order Id:'.$order_id. '  Total Amount:'.$totalAmount.'  Status:'.$resultData['status'].')')
            );

            $historyUpdate = array(
                array('fieldName' => 'order_no', 'type' => 'integer', 'value' => $_SESSION['yjfOrderNo']),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 1),
                array('fieldName' => 'pay_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'pay_status', 'type' => 'string', 'value' => 'processing'),
                array('fieldName' => 'pay_message', 'type' => 'string', 'value' => '(' . $resultData['resultCode'] . ')')
            );

            $db->perform(TABLE_ORDERS_STATUS_HISTORY, $statusHistory,'update','orders_id = ' . $order_id);
            $db->perform(TABLE_YJFPAYC_HISTORY, $historyUpdate, 'update', 'order_id = ' . $order_id);
        }
        function read_pay_history($db, $orderID) {
            $sql = "SELECT order_no,order_id,`status`,`pay_total`,pay_date,pay_status,pay_message FROM  yjfpaycnew_history WHERE order_id = :order_id AND `status` < 3";
            $cmdSQL  = $db->bindVars($sql, ':order_id', $orderID, 'string');
            $history = $db->Execute($cmdSQL);

            # check history value and status
            return ($history->EOF) ? false : $history;
        }

        /**
         * 格式化空格
         *
         * @param string $str
         * @return string
         */
        function formatEmpty($str) {
            if (! isset ( $str ) || (empty ( $str ) && $str != 0)) {
                return '';
            } else {
                return trim ( $str );
            }
        }

        /**
         * 创建订单
         */
        function create_order() {
            global $order, $order_totals, $order_total_modules;
            $order->info ['payment_method'] = MODULE_PAYMENT_YJFPAYCNEW_TEXT_TITLE;
            $order->info ['payment_module_code'] = $this->code;
            $order->info ['order_status'] = $this->order_status;
            $order_totals = $order_total_modules->pre_confirmation_check ();
            $order_totals = $order_total_modules->process ();
            $_SESSION ['yjfpay_order_id'] = $order->create ( $order_totals, 2 );
            $order->create_add_products ( $_SESSION ['yjfpay_order_id'] );
        }

        /**
         * 校验信用卡信息是否有效
         *
         * @param
         *        	$cardNum
         * @param
         *        	$cvv
         * @param
         *        	$year
         * @param
         *        	$month
         * @return String
         */
        function validateCardInfo($additionInfo) {
            $cardNum = $additionInfo ['cardNo'];
            $cvv = $additionInfo ['cardSecurityCode'];
            $year = $additionInfo ['cardExpireYear'];
            $month = $additionInfo ['cardExpireMonth'];

            $errorMsg = $this->validateCardNum ( $cardNum );
            if (! empty ( $errorMsg ) && strlen ( $errorMsg ) > 1) {
                return $errorMsg;
            }

            $errorMsg = $this->validateCardType ( $cardNum );
            if (! empty ( $errorMsg ) && strlen ( $errorMsg ) > 1) {
                return $errorMsg;
            }

            $errorMsg = $this->validateCVV ( $cvv );
            if (! empty ( $errorMsg ) && strlen ( $errorMsg ) > 1) {
                return $errorMsg;
            }

            $errorMsg = $this->validateExpiresDate ( $year, $month );
            if (! empty ( $errorMsg ) && strlen ( $errorMsg ) > 1) {
                return $errorMsg;
            }
            return "";
        }

        /**
         * 校验信用卡卡号是否有效
         *
         * @param
         *        	$cardNum
         * @return String
         */
        function validateCardNum($cardNum) {
            $msg = "";
            if (empty ( $cardNum ) || ! is_numeric ( $cardNum ) || strlen ( $cardNum ) < 13 || strlen ( $cardNum ) > 16 || ! $this->card_check_by_luhn ( $cardNum )) {
                $msg = MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_CARD_NUM;
            }
            return $msg;
        }

        /**
         * 通过Luhn算法校验信用卡卡号是否有效
         *
         * @param
         *        	$cardNum
         * @return bool
         */
        function card_check_by_luhn($cardNum) {
            $str = '';
            foreach ( array_reverse ( str_split ( $cardNum ) ) as $i => $c )
                $str .= ($i % 2 ? $c * 2 : $c);
            return array_sum ( str_split ( $str ) ) % 10 == 0;
        }

        /**
         * 校验信用卡卡号是否有效
         *
         * @param
         *        	$cardNum
         * @return String
         */
        function validateCardType($cardNum) {
            $msg = "";
            $cardType = $this->getCardTypeByCardNum ( $cardNum );
            if (empty ( $cardType ) || strlen ( $cardType ) < 1) {
                $msg .= MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_CARD_ALLOW . ' !\n';
            }
            return $msg;
        }

        /**
         * 校验信用卡卡号是否有效
         *
         * @param
         *        	$cardNum
         * @return String
         */
        function getCardTypeByCardNum($cardNum) {
            $cardType = "";
            $left = substr ( $cardNum, 0, 2 );
            if ($left >= 40 && $left <= 49) {
                $cardType = "Visa";
            } else if ($left >= 50 && $left <= 59) {
                $cardType = "MasterCard";
            } else if ($left == 35) {
                $cardType = "JCB";
            }
            return $cardType;
        }

        /**
         * 校验信用卡CVV是否有效
         *
         * @param
         *        	$cvv
         * @return String
         */
        function validateCVV($cvv) {
            $msg = "";
            if (empty ( $cvv ) || ! is_numeric ( $cvv ) || strlen ( $cvv ) < 3 || strlen ( $cvv ) > 4) {
                $msg = MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_CVV;
            }
            return $msg;
        }

        /**
         * 校验信用卡有效期是否有效
         *
         * @param
         *        	$year
         * @param
         *        	$month
         * @return String
         */
        function validateExpiresDate($year, $month) {
            $msg = "";
            if (empty ( $year ) || ! is_numeric ( $year ) || strlen ( $year ) != 4) {
                $msg = MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_EXP_YEAR;
            } else if (empty ( $month ) || ! is_numeric ( $month ) || strlen ( $month ) != 2 || $month < 1 || $month > 12) {
                $msg = MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_EXP_MONTH;
            } else {
                $currentYear = date ( 'Y' ); // 当前时间年份
                $currentMonth = date ( 'm' ); // 当前时间月份
                if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
                    $msg = MODULE_PAYMENT_YJFPAY_TEXT_ERROR_MSG_EXPIRE;
                }
            }
            return $msg;
        }

        /**
         * 初始化Javascript函数
         */
        function func_init_JS() {
            $jsInit = 'function broserInit() {
                document.getElementById("yjfpay_os").value = getOS();
                document.getElementById("yjfpay_resolution").value=getResolution();
                document.getElementById("yjfpay_brower").value = getBrowser();
                document.getElementById("yjfpay_brower_lang").value=getBrowserLang();
                document.getElementById("yjfpay_time_zone").value=getTimezone();
             }
            function pasteCard() {
                document.getElementById("yjfpay_is_copycard").value = 1;
                return true;
            }
            function checkCardNum(cardNumber) {
                if(cardNumber == null || cardNumber == "" || cardNumber.length > 16 || cardNumber.length < 13) {
                    return false;
                }else if(cardNumber.charAt(0) != 3 && cardNumber.charAt(0) != 4 && cardNumber.charAt(0) != 5){
                    return false;
                }else {
                    return luhnCheckCard(cardNumber);
                }
            }
            function luhnCheckCard(cardNumber){
                var sum=0;var digit=0;var addend=0;var timesTwo=false;
                for(var i=cardNumber.length-1;i>=0;i--){
                    digit=parseInt(cardNumber.charAt(i));
                    if(timesTwo){
                        addend = digit * 2;
                        if (addend > 9) {
                            addend -= 9;
                        }
                    }else{
                        addend = digit;
                    }
                    sum += addend;
                    timesTwo=!timesTwo;
                }
                return sum%10==0;
            }
            function checkExpdate(expdate) {
                if(expdate == null || expdate == "" || expdate.length < 1) {
                    return false;
                }else {
                    return true;
                }
            }
            function checkCvv(cvv) {
                if(cvv == null || cvv =="" || cvv.length < 3 || cvv.length > 4 || isNaN(cvv)) {
                    return false;
                }else {
                    return true;
                }
            }
            function getResolution() {
                return window.screen.width + "x" + window.screen.height;
            }
            function getTimezone() {
                return new Date().getTimezoneOffset()/60*(-1);
            }
            function getBrowser() {
                var userAgent = navigator.userAgent;
                var isOpera = userAgent.indexOf("Opera") > -1;
                if (isOpera) {
                    return "Opera"
                }
                if (userAgent.indexOf("Chrome") > -1) {
                    return "Chrome";
                }
                if (userAgent.indexOf("Firefox") > -1) {
                    return "Firefox";
                }
                if (userAgent.indexOf("Safari") > -1) {
                    return "Safari";
                }
                if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1
                    && !isOpera) {
                    return "IE";
                }
            }
            function getBrowserLang() {
                return navigator.language || window.navigator.browserLanguage;
            }
            function getOS() {
                var sUserAgent = navigator.userAgent;
                var isWin = (navigator.platform == "Win32")
                    || (navigator.platform == "Windows");
                var isMac = (navigator.platform == "Mac68K")
                    || (navigator.platform == "MacPPC")
                    || (navigator.platform == "Macintosh")
                    || (navigator.platform == "MacIntel");
                if (isMac)
                    return "Mac";
                var isUnix = (navigator.platform == "X11") && !isWin && !isMac;
                if (isUnix)
                    return "Unix";
                var isLinux = (String(navigator.platform).indexOf("Linux") > -1);
                if (isLinux)
                    return "Linux";
                if (isWin) {
                    var isWin2K = sUserAgent.indexOf("Windows NT 5.0") > -1
                        || sUserAgent.indexOf("Windows 2000") > -1;
                    if (isWin2K)
                        return "Win2000";
                    var isWinXP = sUserAgent.indexOf("Windows NT 5.1") > -1
                        || sUserAgent.indexOf("Windows XP") > -1;
                    if (isWinXP)
                        return "WinXP";
                    var isWin2003 = sUserAgent.indexOf("Windows NT 5.2") > -1
                        || sUserAgent.indexOf("Windows 2003") > -1;
                    if (isWin2003)
                        return "Win2003";
                    var isWin2003 = sUserAgent.indexOf("Windows NT 6.0") > -1
                        || sUserAgent.indexOf("Windows Vista") > -1;
                    if (isWin2003)
                        return "WinVista";
                    var isWin2003 = sUserAgent.indexOf("Windows NT 6.1") > -1
                        || sUserAgent.indexOf("Windows 7") > -1;
                    if (isWin2003)
                        return "Win7";
                }
                return "None";
            }
            function getOsLang() {
                return navigator.language || window.navigator.systemLanguage;
            }';
            return $jsInit;
        }

    }

<?php

    # payment information
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_TITLE'] = 'Credit-Card Payment';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_DESCRIPTION'] = '<strong>YJF </strong>：make cross-boader payment by credit card';


    # configuration const define
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_TITLE'] = ' open the YJF payment module';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_DESCRIPTION'] = ' do you need open the YJF payment module';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_TITLE'] = 'Merchant name';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_DESCRIPTION'] = 'merchant name';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_TITLE'] = ' Merchant email';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION'] = ' merchant email (recipient)';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_TITLE'] = 'Merchant ID';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_DESCRIPTION'] = 'YJF will distribute the signed merchants';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_TITLE'] = ' Merchant secret key';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_DESCRIPTION'] = ' merchant secret key from YJF';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_TITLE'] = 'Acquiring type';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION'] = 'Acquiring type crdit or yandex';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_TITLE'] = ' Order submit status';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_DESCRIPTION'] = ' Order submit status set.';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_TITLE'] = ' Order payment status';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_DESCRIPTION'] = ' Order payment status set.';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE'] = ' Order authorize status';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_DESCRIPTION'] = ' Order authorize status set.';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_TITLE'] = ' Order fail status';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_DESCRIPTION'] = ' Order fail status set.';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_TITLE'] = ' Payment Zone';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_DESCRIPTION'] = 'If you choose a payment zone,then the payment module can be used in this zone';

    $_['MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_TITLE'] = ' Debug mode';
    $_['MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_DESCRIPTION'] = ' if you choose debug mode, then the payment is a test';

    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_TITLE'] = ' Order display';
    $_['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_DESCRIPTION'] = 'order display: the Small one displays in the former';

    # end configuration const define


    # begin administrators
    $_['MODULE_PAYMENT_YJFPAYC_ADMIN_PAY_EXCEPTION'] = ' payment abnormal';


    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_BUTTON_TEXT'] = ' refund';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CANCEL_BUTTON_TEXT'] = ' cancel order';

    $_['MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_CONFIRM_ERROR'] = 'Error: you want a refund, but you didn\'t choose confirmation box';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_INVALID_REFUND_AMOUNT'] = 'Error: you want a refund, but the input amount is incorrect';

    $_['MODULE_PAYMENT_YJFPAYC_TEXT_CC_NUM_REQUIRED_ERROR'] = 'Error: you want a refund, but you did not enter the last four credit card number';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_INITIATED'] = 'refund, transaction ID: %s - authorization code: %s';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_CAPTURE_CONFIRM_ERROR'] = 'Error: you want to make collections, but did not choose the confirmation box';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_BUTTON_TEXT'] = 'transaction confirms';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_INVALID_CAPTURE_AMOUNT'] = 'Error: if you want to collect, please enter the amount';

    $_['MODULE_PAYMENT_YJFPAYC_TEXT_TRANS_ID_REQUIRED_ERROR'] = 'Error: please confirm the transaction ID';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_CAPT_INITIATED'] = 'collection.amount: %s.  transaction number: %s - authorization code: %s';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_BUTTON_TEXT'] = 'cancel';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_VOID_CONFIRM_ERROR'] = 'Error: you want to cancel order, but didn\'t choose confirmation box';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_VOID_INITIATED'] = 'cancel.transaction ID: %s - authorization code: %s ';


    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TITLE'] = '<strong>repeal/refund transaction</strong>';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND'] = 'you can refund to the customer\'s credit card here';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_CONFIRM_CHECK'] = ' confirm by choosing the box';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_TEXT'] = ' input the demanded refund amount';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_MORE_TEXT'] = ' refund amount is more than the payment amount';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_HELP'] = '';


    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TRANS_ID'] = ' enter the original transaction number';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TEXT_COMMENTS'] = ' introduction(will be displayed in the order record)';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_DEFAULT_MESSAGE'] = ' refund finished';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_SUFFIX'] = 'The refund amount is no more than the payment amount, the original order the last 4 digits of credit card of the original order must be provided<br />The refund must be within 120 days of the original transaction';

    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TITLE'] = '<strong>authorization</strong>';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE'] = 'The current order need pre authorization';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_AMOUNT_TEXT'] = 'enter the amount you want to collect: ';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_CAPTURE_CONFIRM_CHECK'] = ' confirm by choosing the box: ';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TRANS_ID'] = ' enter the original transaction ID: ';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TEXT_COMMENTS'] = ' introduction(will be display in the order record): ';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_DEFAULT_MESSAGE'] = ' confirm the authorized order: ';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_SUFFIX'] = 'Payment must be made within 30 days after the original authorization, only one order can be authorized at one time<br />please make sure the right amount is entered<br />If the amount is empty, we will use the original amount: ';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_BUTTON_CANCEL_TEXT'] = 'transaction cancel: ';

    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_TITLE'] = '<strong>invalid transaction</strong>';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID'] = 'only unprocessed transactions can be cancelled <br />Please enter the unprocessed transaction number:';
    $_['MODULE_PAYMENT_YJFPAYC_TEXT_VOID_CONFIRM_CHECK'] = 'confirm by choosing the box:';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_TEXT_COMMENTS'] = 'introduction(will be displayed in the order record):';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_DEFAULT_MESSAGE'] = 'transaction cancel';
    $_['MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_SUFFIX'] = 'you can only cancel the transaction before the daily transaction confirmations:';

    $_['MODULE_PAYMENT_YJFPAYC_WAIT_PAY_OR_NOT_PAY'] = 'Customer do\'t pay or wait pay result';
    $_['MODULE_PAYMENT_YJFPAYC_CANCEL_ORDER'] = 'Cancel order';


    $_['MODULE_PAYMENT_YJFPAYC_STATUS_0'] = 'Wait Payment';
    $_['MODULE_PAYMENT_YJFPAYC_STATUS_1'] = 'Wait Payment';
    $_['MODULE_PAYMENT_YJFPAYC_STATUS_2'] = 'Wait Authorizing';
    $_['MODULE_PAYMENT_YJFPAYC_STATUS_3'] = 'Finish';
    $_['MODULE_PAYMENT_YJFPAYC_STATUS_4'] = 'Refund';
    $_['MODULE_PAYMENT_YJFPAYC_STATUS_5'] = 'Cancel Order';

    $_['NAVBAR_TITLE_1'] = 'Checkout';
    $_['NAVBAR_TITLE_2'] = 'Credit-Card Payment';

    $_['MODULE_PAYMENT_YJFPAYC_NOT_PAY_OR_WAIT_PAY'] = 'Do \'t pay or wait paying';


$_['heading_title']      = 'yjfpayc';

// Text 
$_['text_payment']       = 'Payment';
$_['text_success']       = 'Success: You have modified Gofpay account details!';
//$_['text_authorization'] = 'Authorization';
//$_['text_capture']       = 'Capture';


// Entry
$_['entry_websiteid']    = 'Website Id:';
$_['entry_secretkey']    = 'Secret Key:';
$_['entry_gateway']      = 'Gateway:';
$_['entry_mode']         = 'Mode:';
$_['entry_method']       = 'Transaction Method:';
$_['entry_order_status'] = 'New Order Status:';
$_['entry_order_notify_status'] = 'Order Status After Notify:';
$_['entry_status']       = 'Status:';
$_['entry_sort_order']   = 'Sort Order:';

// Error 
$_['error_permission']   = 'Warning: You do not have permission to modify payment Gateway!';
$_['error_websiteid']    = 'Website Id Required!';
$_['error_secretkey']    = 'Secret Key Required!';
$_['error_gateway']      = 'Gateway Required!';

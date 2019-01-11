<?php

    # payment information
    define('MODULE_PAYMENT_YJFPAYC_TEXT_TITLE', 'Credit-Card Payment-HK');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_DESCRIPTION', '<img src=includes/modules/pages/yjpayhk/images/V.jpg alt="Pay with Credit Cards" title="Pay with Credit Cards"><img src=includes/modules/pages/yjpayhk/images/M.jpg alt="Pay with Credit Cards" title="Pay with Credit Cards"><img src=includes/modules/pages/yjpayhk/images/J.jpg alt="Pay with Credit Cards" title="Pay with Credit Cards"><strong>YJF </strong>：make cross-boader payment by credit card');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_DESCRIPTION_ADMIN', '<strong>YJFPAY </strong>：make cross-boader payment by credit card');


    # configuration const define
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_TITLE', ' open the YJF payment module');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_DESCRIPTION', ' do you need open the YJF payment module');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_TITLE', 'Merchant name');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_DESCRIPTION', 'merchant name');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_TITLE', ' Merchant email');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION', ' merchant email (recipient)');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_TITLE', 'Merchant ID');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_DESCRIPTION', 'YJF will distribute the signed merchants');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_TITLE', ' Merchant secret key');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_DESCRIPTION', ' merchant secret key from YJF');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_TITLE', 'Acquiring type');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION', 'Acquiring type crdit or yandex');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_TITLE', ' Order submit status');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_DESCRIPTION', ' Order submit status set.');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_TITLE', ' Order payment status');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_DESCRIPTION', ' Order payment status set.');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE', ' Order authorize status');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_DESCRIPTION', ' Order authorize status set.');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_TITLE', ' Order fail status');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_DESCRIPTION', ' Order fail status set.');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_TITLE', ' Payment Zone');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_DESCRIPTION', 'If you choose a payment zone,then the payment module can be used in this zone');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_LANGUAGE_TITLE', ' Payment language');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_LANGUAGE_DESCRIPTION', 'Payment language by show payment page example en:English  jp:Japanese  de:Deutsch  esp:El español  fr:Français');


    define('MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_TITLE', ' Debug mode');
    define('MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_DESCRIPTION', ' if you choose debug mode, then the payment is a test');

    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_TITLE', ' Order display');
    define('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_DESCRIPTION', 'order display: the Small one displays in the former');

    # end configuration const define


    # begin administrators
    define('MODULE_PAYMENT_YJFPAYC_ADMIN_PAY_EXCEPTION', ' payment abnormal');


    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_BUTTON_TEXT', ' refund');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CANCEL_BUTTON_TEXT', ' cancel order');

    define('MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_CONFIRM_ERROR', 'Error: you want a refund, but you didn\'t choose confirmation box');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_INVALID_REFUND_AMOUNT', 'Error: you want a refund, but the input amount is incorrect');

    define('MODULE_PAYMENT_YJFPAYC_TEXT_CC_NUM_REQUIRED_ERROR', 'Error: you want a refund, but you did not enter the last four credit card number');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_INITIATED', 'refund, transaction ID: %s - authorization code: %s');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_CAPTURE_CONFIRM_ERROR', 'Error: you want to make collections, but did not choose the confirmation box');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_BUTTON_TEXT', 'transaction confirms');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_INVALID_CAPTURE_AMOUNT', 'Error: if you want to collect, please enter the amount');

    define('MODULE_PAYMENT_YJFPAYC_TEXT_TRANS_ID_REQUIRED_ERROR', 'Error: please confirm the transaction ID');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_CAPT_INITIATED', 'collection.amount: %s.  transaction number: %s - authorization code: %s');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_BUTTON_TEXT', 'cancel');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_VOID_CONFIRM_ERROR', 'Error: you want to cancel order, but didn\'t choose confirmation box');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_VOID_INITIATED', 'cancel.transaction ID: %s - authorization code: %s ');


    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TITLE', '<strong>repeal/refund transaction</strong>');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND', 'you can refund to the customer\'s credit card here');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_REFUND_CONFIRM_CHECK', ' confirm by choosing the box');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_TEXT', ' input the demanded refund amount');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_MORE_TEXT', ' refund amount is more than the payment amount');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_AMOUNT_HELP', '');


    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TRANS_ID', ' enter the original transaction number');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_TEXT_COMMENTS', ' introduction(will be displayed in the order record)');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_DEFAULT_MESSAGE', ' refund finished');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_REFUND_SUFFIX', 'The refund amount is no more than the payment amount, the original order the last 4 digits of credit card of the original order must be provided<br />The refund must be within 120 days of the original transaction');

    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TITLE', '<strong>authorization</strong>');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE', 'The current order need pre authorization');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_AMOUNT_TEXT', 'enter the amount you want to collect: ');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_CAPTURE_CONFIRM_CHECK', ' confirm by choosing the box: ');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TRANS_ID', ' enter the original transaction ID: ');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_TEXT_COMMENTS', ' introduction(will be display in the order record): ');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_DEFAULT_MESSAGE', ' confirm the authorized order: ');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_SUFFIX', 'Payment must be made within 30 days after the original authorization, only one order can be authorized at one time<br />please make sure the right amount is entered<br />If the amount is empty, we will use the original amount: ');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_CAPTURE_BUTTON_CANCEL_TEXT', 'transaction cancel: ');

    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_TITLE', '<strong>invalid transaction</strong>');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID', 'only unprocessed transactions can be cancelled <br />Please enter the unprocessed transaction number:');
    define('MODULE_PAYMENT_YJFPAYC_TEXT_VOID_CONFIRM_CHECK', 'confirm by choosing the box:');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_TEXT_COMMENTS', 'introduction(will be displayed in the order record):');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_DEFAULT_MESSAGE', 'transaction cancel');
    define('MODULE_PAYMENT_YJFPAYC_ENTRY_VOID_SUFFIX', 'you can only cancel the transaction before the daily transaction confirmations:');

    define('MODULE_PAYMENT_YJFPAYC_WAIT_PAY_OR_NOT_PAY', 'Customer do\'t pay or wait pay result');
    define('MODULE_PAYMENT_YJFPAYC_CANCEL_ORDER', 'Cancel order');


    define('MODULE_PAYMENT_YJFPAYC_STATUS_0', 'Wait Payment');
    define('MODULE_PAYMENT_YJFPAYC_STATUS_1', 'Wait Payment');
    define('MODULE_PAYMENT_YJFPAYC_STATUS_2', 'Wait Authorizing');
    define('MODULE_PAYMENT_YJFPAYC_STATUS_3', 'Finish');
    define('MODULE_PAYMENT_YJFPAYC_STATUS_4', 'Refund');
    define('MODULE_PAYMENT_YJFPAYC_STATUS_5', 'Cancel Order');

    define('NAVBAR_TITLE_1', 'Checkout');
    define('NAVBAR_TITLE_2', 'Credit-Card Payment');

    define('MODULE_PAYMENT_YJFPAYC_NOT_PAY_OR_WAIT_PAY', 'Do \'t pay or wait paying');


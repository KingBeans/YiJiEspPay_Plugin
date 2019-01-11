<?php

$_['heading_title'] = 'YJF-Crossborder Payment Platform';

$_['text_edit']    = 'Edit YJF-Crossborder Payment Platform';
$_['text_success'] = 'Save yiji pay setting success';

$_['text_merchant_info'] = 'Merchant Info';
$_['text_order_status']  = 'Order Status';
$_['text_service_url']   = 'Service URL';
$_['text_status']        = 'Payment General';

$_['entry_merchant_name']    = 'Merchant Name';
$_['entry_merchant_email']   = 'Merchant Email';
$_['entry_partner_id']       = 'Partner ID';
$_['entry_certificate_cipher']       = 'Certificate Cipher';
$_['entry_currency']         = 'Currency';
$_['entry_currency_range']   = array('CNY' => 'CNY', 'USD' => 'USD', 'JPY' => 'JPY');
$_['entry_status_submit']    = 'Submit Pay';
$_['entry_status_authorize'] = 'Authorize';
$_['entry_status_complete']  = 'Pay Success';
$_['entry_status_fail']      = 'Pay Fail';
$_['entry_notify']           = 'Notify Url';
$_['entry_sort_order']       = 'Sort Order';
$_['entry_geo_zone']         = 'Geo Zone';
$_['entry_geo_all']          = 'All Zones';
$_['entry_status']           = 'Status';
$_['entry_status_range']     = array('1' => 'Enabled', '0' => 'Disabled');
$_['entry_debug']            = 'Pay Url';
$_['entry_debug_range']      = array(
    'release' => 'https://openapiglobal.yiji.com/gateway.html (PRODUCT)',
    'debug' => 'https://openapi.yijifu.net/gateway.html (TEST)'
);

$_['error_merchant_name_empty']    = 'Merchant name can\'t empty';
$_['error_merchant_email_empty']   = 'Merchant email can\'t empty';
$_['error_merchant_email_invalid'] = 'Merchant email invalid';
$_['error_certificate_cipher_empty']       = 'Certificate Cipher can\'t empty';
$_['error_partner_id_empty']       = 'Partner ID can\'t empty';
$_['error_partner_id_invalid']     = 'Partner ID invalid';
$_['error_currency_invalid']       = 'Currency invalid';
$_['error_debug_invalid']          = 'Pay Url error pleas retry';


$_['view_text_heading'] = 'YJF-Crossborder Payment Platform';
$_['view_status']       = 'Payment Status';
$_['view_order_fee']    = 'Order Total';
$_['view_payment_fee']  = 'Payment Total';
$_['view_authorize']    = 'Authorize';
$_['view_refund_fee']   = 'Refund Total';

$_['view_authorize_summary'] = '<strong>Warning</strong> Current pay has warning, Please confirm or cancel order?';

$_['view_order_status']         = 'Order Status';
$_['view_refund_heading']       = 'Refund';
$_['view_refund_enable_money']  = 'Enable Refund Money';
$_['view_refund_money']         = 'Refund Money';
$_['view_refund_money_empty']   = 'Refund money can not be empty';
$_['view_refund_money_invalid'] = 'Refund money invalid';

$_['view_refund_note']    = 'Refund Note';
$_['view_refund_deny']    = '<strong>Sorry !</strong> Order can\'t refund.';
$_['view_refund_success'] = '<strong>Success !</strong> Order refund success.';
$_['view_refund_confirm'] = 'Please confirm refund order?';

$_['view_cancel_heading'] = 'Cancel Order';
$_['view_cancel_note']    = 'Cancel Note';
$_['view_cancel_deny']    = '<strong>Sorry !</strong> Order can\'t cancel.';
$_['view_cancel_success'] = '<strong>Success !</strong> Order cancel success.';
$_['view_cancel_confirm'] = 'Please confirm cancel order?';

$_['view_button_refund'] = 'Refund';
$_['view_button_submit'] = 'Submit';
$_['view_button_cancel'] = 'Cancel';

$_['view_button_authorize_pass'] = 'Confirm Order';
$_['view_button_authorize_deny'] = 'Cancel Order';

$_['view_authorize_success'] = '<strong>Success !</strong> Process authorize success.';

#$_['view_payment_date']     = 'Payment Date';
#$_['view_authorize_date']   = 'Authorize Date';
#$_['view_authorize_result'] = 'Authorize Result';
#$_['view_refund_date']      = 'Refund Date';

$_['view_status_names'] = array(
    1 => 'Wait Pay',
    2 => 'Authorizing',
    3 => 'Authorized',
    4 => 'Payed',
    5 => 'Refund',
    6 => 'Cancel',
    7 => 'Pay Fail'
);

$_['view_authorize_names'] = array(
    0 => 'None',
    1 => 'Authorize'
);

$_['view_authorize_result'] = array(
    0 => '',
    1 => 'Yes',
    2 => 'No'
);

$_['view_cancel_order']  = 'Cancel Order';
$_['view_confirm_order'] = 'Confirm Order';
$_['view_refund_order']  = 'Refund';

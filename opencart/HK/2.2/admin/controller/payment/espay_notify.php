<?php
class ControllerPaymentEspaynotify extends Controller {

 

     public function index() { 
     // $_POST = array(
     //            'orderNo' => '',
     //            'merchOrderNo' => 'oc20170111091655-27',
     //            'notifyTime' => '2016-10-17 16:19:27',
     //            'resultCode' => 'EXECUTE_SUCCESS',
     //            'sign' => '4254a8eb7b5201d8134361ea00b41642',
     //            'resultMessage' => '成功',
     //            'outOrderNo' => '2015102587408',
     //            'version' => '1.0',
     //            'protocol' => 'httpPost',
     //            'service' => 'cardAcquiringCashierPay',
     //            // 'status' => 'authorizing',
     //            // 'status' => 'success',
     //            'status' => 'fail',
     //            'signType' => 'MD5',
     //            'partnerId' => '20140526020000027815',
     //            'description' => 'authoriziing order infos',
     //        );
        if(!empty($_POST))
        {
            $db      = $this->db;
            
            $sign = array_key_pop($_POST,'sign');

            $setting_data = espay_setting($db);
            // var_dump($setting_data);
            // exit();
            // if($sign == yjfpayc_signature($_POST,$setting_data)) {
            if(true) {
                // orderid 处理
                $merchOrderNo = $_POST['merchOrderNo'];
                $pos = strpos($merchOrderNo, '-');

                if(isset($pos)) {
                    $orderID = substr($merchOrderNo, $pos+1);
                } else {
                    $orderID = $orderNo;
                }                         
                // var_dump($orderID);
                # if order exists
                if ($order = espay_order($db, $orderID)) {
                    $setting = espay_setting($db);
                
                    if ($_POST['status'] == 'success') {
                        espay_order_update($db, $orderID, $setting['yjfpayc_payment_status_id']);
                        espay_order_history($db, $orderID, $setting['yjfpayc_payment_status_id'], '<strong>YJF Pay:<strong>' . $_POST['resultMessage']);
                    } else if ($_POST['status'] == 'authorizing') {
                        espay_order_update($db, $orderID, $setting['yjfpayc_authorize_status_id']);
                        espay_order_history($db, $orderID, $setting['yjfpayc_authorize_status_id'], '<strong>YJF Authorize:<strong>' . $_POST['resultMessage']);
                    } else {
                        espay_order_update($db, $orderID, $setting['yjfpayc_fail_status_id']);
                        espay_order_history($db, $orderID, $setting['yjfpayc_fail_status_id'], '<strong>YJF Pay Fail:<strong>' . $_POST['resultMessage']);
                    }
                }
                echo 'success'; 
            } else {
                echo "other service";
            }      
        } else {
            echo "empty post data";
        }  
    }

}

function espay_order($db, $orderID) {
    $result = $db->query('SELECT * FROM `' . DB_PREFIX . 'order` WHERE `payment_code` = "yjfpayc" AND `order_id`=' . intval($orderID));
    return $result->rows ? $result->rows[0] : false;
}

function espay_order_update($db, $orderID, $status) {
    $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . $status . " WHERE order_id = " . $orderID);
}

function espay_order_history($db, $orderID, $statusID, $message) {
    $message = $db->escape($message);
    $date    = date('Y-m-d H:i:s', time());

    $db->query("INSERT INTO `" . DB_PREFIX . "order_history`(`order_id`,`order_status_id`,`notify`,`comment`,`date_added`) VALUES({$orderID},{$statusID},0,'{$message}','{$date}')");
}

function espay_setting($db) {
    $setting = array();
    $result  = $db->query("SELECT `key`,`value` FROM `" . DB_PREFIX . "setting` WHERE `key` like 'yjfpayc_%'");

    foreach ($result->rows as $row) {
        $setting[$row['key']] = $row['value'];
    }

    return $setting;
}


// get post sign
function array_key_pop(&$array, $key, $default = false) {
    # if isset key value
    if (isset($array[$key])) {
        $default = $array[$key];
    }

    unset($array[$key]);
    return $default;
    }

/**
 * get pay signature
 */
function yjfpayc_signature(array $params,$data) {
    $merchantkey = $data['yjfpayc_secret_key'];
    # sort for key
    ksort($params);

    $clientSignatureString = '';
    foreach ($params as $key => $value) {
        $clientSignatureString .= ($key . '=' . $value . '&');
    }

    $clientSignatureString = substr($clientSignatureString, 0, -1);
    $clientSignatureString = trim($clientSignatureString) . $merchantkey;

    return md5($clientSignatureString);
}
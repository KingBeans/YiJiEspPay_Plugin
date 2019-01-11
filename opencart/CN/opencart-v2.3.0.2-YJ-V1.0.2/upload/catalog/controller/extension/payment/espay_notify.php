<?php
class ControllerExtensionPaymentEspaynotify extends Controller {

     public function index() { 

        if(!empty($_POST))
        {
            $db      = $this->db;
            
            $sign = array_key_pop($_POST,'sign');

            $setting_data = espay_setting($db);

            if($sign == yjfpayc_signature($_POST,$setting_data)) {
                // orderid 处理
                $merchOrderNo = $_POST['merchOrderNo'];
                $pos = strpos($merchOrderNo, '-');

                if(isset($pos)) {
                    $orderID = substr($merchOrderNo, $pos+1);
                } else {
                    $orderID = $orderNo;
                }                         
                
                # if order exists
                if ($order = espay_order($db, $orderID)) {
                    $setting = espay_setting($db);
                
                    if ($_POST['status'] == 'success') {
                        espay_order_update($db, $orderID, $setting['espay_status_complete']);
                        espay_order_history($db, $orderID, $setting['espay_status_complete'], '<strong>YJF Pay:<strong>' . $_POST['resultMessage']);
                    } else if ($_POST['status'] == 'authorizing') {
                        espay_order_update($db, $orderID, $setting['espay_status_authorize']);
                        espay_order_history($db, $orderID, $setting['espay_status_authorize'], '<strong>YJF Authorize:<strong>' . $_POST['resultMessage']);
                    } else {
                        espay_order_update($db, $orderID, $setting['espay_status_fail']);
                        espay_order_history($db, $orderID, $setting['espay_status_fail'], '<strong>YJF Pay Fail:<strong>' . $_POST['resultMessage']);
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
    $result = $db->query('SELECT * FROM `' . DB_PREFIX . 'order` WHERE `payment_code` = "espay" AND `order_id`=' . intval($orderID));
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
    $result  = $db->query("SELECT `key`,`value` FROM `" . DB_PREFIX . "setting` WHERE `key` like 'espay_%'");

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
    $merchantkey = $data['espay_secret_key'];
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
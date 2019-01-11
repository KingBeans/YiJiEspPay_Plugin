<?php

define('ESPAY_DEBUG', 1);
class ControllerPaymentEspayreceive extends Controller {
    function index()
    {
        if (ESPAY_DEBUG) {
            ob_start();
            print_r($_POST);
        
            $content = ob_get_contents();
            ob_end_clean();
            $this->cache->set('espay_receive_log', $content);
           // file_put_contents(dirname(__FILE__) . '/espay_receive_log.txt', $content, FILE_APPEND);
        }     
        $this->cache->set('espay_receive_POST', $_POST);   
        if(!empty($_POST))
        {
            $orderID = $_POST['merchOrderNo'];
            $db      = $this->db;
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
        }  
        echo 'success';
    }
    function returnurl()
    {
        var_dump($_GET);
        var_dump($_POST);
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
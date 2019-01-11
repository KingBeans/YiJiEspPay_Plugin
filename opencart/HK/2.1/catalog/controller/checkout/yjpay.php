<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 13:48
 */
class ControllerCheckoutYjpay extends Controller {
    public function index(){
        $html = '<script type="text/javascript">';
        $html .= 'if (window != top){ top.location.href = location.href }';
        $html .= '</script>';
        echo $html;

        $_get = $this->request->get;
        unset($_get['route']);
        $yjpay_sign = $_get['sign'];
        unset($_get['sign']);
        $local_sign = $this->getSign($_get);
        if( $local_sign == $yjpay_sign ){
            $db = $this->db;
            $order = $this->yjpay_order($db,$_get['merchOrderNo']);
            if(!$order){
                echo "the order error!";
            }
            switch ($_get['status']){
                case 'success':
                    $this->yjpay_order_update($db,$_get['merchOrderNo'],$this->config->get('yjpay_success_status'));
                    $this->yjpay_order_history($db,$_get['merchOrderNo'],$this->config->get('yjpay_success_status'),'<strong>YJPay:<strong>'.$_get['description']);
                    break;
                case 'authorizing':
                    $this->yjpay_order_update($db,$_get['merchOrderNo'],$this->config->get('yjpay_authorizing_status'));
                    $this->yjpay_order_history($db,$_get['merchOrderNo'],$this->config->get('yjpay_authorizing_status'),'<strong>YJPay:<strong>'.$_get['description']);
                    break;
                case 'processing':
                    $this->yjpay_order_update($db,$_get['merchOrderNo'],$this->config->get('yjpay_start_status'));
                    $this->yjpay_order_history($db,$_get['merchOrderNo'],$this->config->get('yjpay_start_status'),'<strong>YJPay:<strong>'.$_get['description']);
                    break;
                default:
                    $this->yjpay_order_update($db,$_get['merchOrderNo'],$this->config->get('yjpay_fail_status'));
                    $this->yjpay_order_history($db,$_get['merchOrderNo'],$this->config->get('yjpay_fail_status'),'<strong>YJPay:<strong>'.$_get['description']);
                    break;
            }
            $this->response->redirect($this->url->link('account/order/info&order_id='.$_get['merchOrderNo'],'','SSL'));
        }else{
            echo "the order error!";
        }

    }

    private function getSign($data){
        foreach ( $data as $k => $v ){
            if($v == ''){
                unset($data[$k]);
            }
        }
        ksort($data);
        $waitSign = '';
        foreach ($data as $k => $v ){
            $waitSign .= '&'.$k.'='.$v;
        }
        $waitSign = trim($waitSign,'&').trim($this->config->get('yjpay_secretKey'));

        return md5($waitSign);
    }

    private function yjpay_order($db, $orderID) {
        $result = $db->query('SELECT * FROM `' . DB_PREFIX . 'order` WHERE `payment_code` = "yjpay" AND `order_id`=' . intval($orderID));
        return $result->rows ? $result->rows[0] : false;
    }

    private function yjpay_order_update($db, $orderID, $status) {
        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . $status . " WHERE order_id = " . $orderID);
    }

    private function yjpay_order_history($db, $orderID, $statusID, $message) {
        $message = $db->escape($message);
        $date    = date('Y-m-d H:i:s', time());

        $db->query("INSERT INTO `" . DB_PREFIX . "order_history`(`order_id`,`order_status_id`,`notify`,`comment`,`date_added`) VALUES({$orderID},{$statusID},0,'{$message}','{$date}')");
    }

    private function yjpay_setting($db) {
        $setting = array();
        $result  = $db->query("SELECT `key`,`value` FROM `" . DB_PREFIX . "setting` WHERE `key` like 'yjpay_%'");

        foreach ($result->rows as $row) {
            $setting[$row['key']] = $row['value'];
        }

        return $setting;
    }

}
<?php
class ControllerCheckoutCcpaysuccess extends Controller {
	public function index() {
        $this->load->language('checkout/success');

        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();

            // Add to activity log
            $this->load->model('account/activity');

            if ($this->customer->isLogged()) {
                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
                    'order_id'    => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_account', $activity_data);
            } else {
                $activity_data = array(
                    'name'     => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
                    'order_id' => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_guest', $activity_data);
            }

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }
        
        if(!empty($_GET))
        {
            $merchOrderNo = $_GET['merchOrderNo'];
            $pos = strpos($merchOrderNo, '-');

            if(isset($pos)) {
                $orderID = substr($merchOrderNo, $pos+1);
            } else {
                $orderID = $orderNo;
            }

            $db      = $this->db;
            
            # if order exists
            if ($order = espay_order($db, $orderID)) {
                $setting = espay_setting($db);
                // var_dump($setting);           
                if ($_GET['status'] == 'success') {
                    espay_order_update($db, $orderID, $setting['espay_status_complete']);
                    espay_order_history($db, $orderID, $setting['espay_status_complete'], '<strong>YJF Pay:<strong>' . $_GET['resultMessage']);
                } else if ($_GET['status'] == 'authorizing') {
                    espay_order_update($db, $orderID, $setting['espay_status_authorize']);
                    espay_order_history($db, $orderID, $setting['espay_status_authorize'], '<strong>YJF Authorize:<strong>' . $_GET['resultMessage']);
                } else if($_GET['status'] == 'processing') {
                	espay_order_update($db, $orderID, $setting['espay_status_submit']);
                    espay_order_history($db, $orderID, $setting['espay_status_submit'], '<strong>YJF Processing:<strong>' . $_GET['resultMessage']);
                } else {
                    espay_order_update($db, $orderID, $setting['espay_status_fail']);
                    espay_order_history($db, $orderID, $setting['espay_status_fail'], '<strong>YJF Pay Fail:<strong>' . $_GET['resultMessage']);
                }
                
            }

        } 

        $debug = $this->config->get('espay_debug');
        if($debug){
            $host = 'https://'.$_SERVER['HTTP_HOST'];
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }

        $data['continueUrl'] = $this->url->link('common/home');

        if($_GET['status'] === 'fail'){
                $data['showTitle'] = '';
                $data['showSubTitle'] = 'An error occurred in the process of payment';
                $data['showMesaage'] ='you order # '.$_GET['orderNo'].' ,<br>Order description:'.$_GET['description'];
                // '<br>Click <a taget=_blank href="'. $data['continueUrl'] . '">here</a> to continue shopping.';
            }
        
        if ($_GET['status'] === 'success' || $_GET['status'] === 'authorizing' || $_GET['status'] === 'processing') {

            $data['showTitle'] = 'Your order has been received.';
            $data['showSubTitle'] = 'thank you for your purchase!';
            $data['showMesaage'] = 'We are processing your order and you will soon receive an email with details of the order. Once the order has shipped you will receive another email with a link to track its progress.';

        }

        $_GET['footer'] = $this->load->controller('common/footer');
        $_GET['header'] = $this->load->controller('common/header');

        #跳转地址
		// $success_url = $this->url->link('checkout/yj_success');
		// $failure_url = $this->url->link('checkout/yj_failure');

		if($_GET['success'] == 'false') {
			$this->response->setOutput($this->load->view('default/template/common/ccpay_success.tpl', $data));
		
		} else {
            $this->response->setOutput($this->load->view('default/template/common/ccpay_success.tpl', $data));

		}
	}


}

// extension　function

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

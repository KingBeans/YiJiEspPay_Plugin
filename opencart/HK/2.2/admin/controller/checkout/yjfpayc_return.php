<?php
class ControllerCheckoutYjfpaycreturn extends Controller {
	public function index() {
		// echo "test return url";
		// var_dump($_GET);

		// $post_json = json_encode($_GET,true);

        // file_put_contents("D:/phpworkspace/opencart/logs/espay_receive_postjson_log.txt", $post_json . '123123test'); 
        if($_GET['success'] == 'false'){
            $orderNo        = $_GET['orderNo'];
            $description    = $_GET['resultMessage'];
            $showTitle = '';
            $html ='';
            $showSubTitle = 'an error occurred in the process of payment';
            $showMesaage ='you order # '.$orderNo.' ,<br>description:'.$description.'<br>Click <a href="/">here</a> to continue shopping.';

            $html .= '<div style="text-align: center;">
                         <div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
                            <h1 data-role="page-title">'.$showTitle.'</h1>
                        </div>
                        <h2 style="margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">'.$showSubTitle.'</h2>
                                <p style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">'.$showMesaage.'</p>
                            </div>';
            echo $html;     
            // echo "Order pay success";
            return;
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
            // echo 'success';       
        } 

        #跳转地址
		$success_url = $this->url->link('checkout/success');
		$failure_url = $this->url->link('checkout/failure');

		if($_GET['success'] == 'false') {
			
			$this->response->redirect($failure_url);
		
		} else {

			$this->response->redirect($success_url);
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

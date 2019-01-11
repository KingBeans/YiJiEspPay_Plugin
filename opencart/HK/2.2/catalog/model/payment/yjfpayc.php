<?php 
class ModelPaymentYjfpayc extends Model {
  	public function getMethod($address) {
		$this->load->language('payment/yjfpayc');
		
		if ($this->config->get('yjfpayc_status')) {
      		$status = TRUE;
      	} else {
			$status = FALSE;
		}
		
		$method_data = array();
	
		if ($status) {  

      		$method_data = array( 
        		'code'         => 'yjfpayc',
        		'title'      => $this->language->get('MODULE_PAYMENT_YJFPAYC_TEXT_DESCRIPTION'),
				'terms' => '',
				'sort_order' => $this->config->get('yjfpayc_sort_order')
      		);
    	}
	
    	return $method_data;
  	}


  public function do_pay_success($order_info, $datapost) {
        $order_id      = $order_info['order_id'];
		$message = $datapost['description'] ;
		$message = htmlentities( $message, ENT_QUOTES, $this->detect_encoding($message));
		$this->db->query("UPDATE `" . DB_PREFIX . "yjfpayc_history` SET `status` = 3,  `pay_date` = '" .  date('Y-m-d H:i:s') . "',  `pay_status` = 'success' , `pay_message` = '" . $message . "' WHERE `order_id` = " . (int)$order_id . "");
    }
   public function do_pay_processing($order_info, $datapost) {
        $order_id = $order_info['order_id'];
		$message = $datapost['description'] ;
		$message = htmlentities( $message, ENT_QUOTES, $this->detect_encoding($message));
		$this->db->query("UPDATE `" . DB_PREFIX . "yjfpayc_history` SET `status` = 1,  `pay_date` = '" .  date('Y-m-d H:i:s') . "',  `pay_status` = 'processing' , `pay_message` = '" . $message . "'  WHERE `order_id` = " . (int)$order_id . "");

    }
   public function do_pay_authorizing($order_info, $datapost) {
        $order_id      = $order_info['order_id'];
		$message = $datapost['description'] ;
		$message =   htmlentities( $message, ENT_QUOTES, $this->detect_encoding($message));
		$this->db->query("UPDATE `" . DB_PREFIX . "yjfpayc_history` SET `status` = 2,  `pay_date` = '" .  date('Y-m-d H:i:s') . "',  `pay_status` = 'authorizing' , `pay_message` = '" . $message . "', `auth_message` = '" . $datapost['authorizingInfo'] . "' WHERE `order_id` = " . (int)$order_id . "");
    }

   public function do_pay_fail($order_info,$datapost) {

        $order_id      = $order_info['order_id'];
		$message = $datapost['description'] ;
		$message =  htmlentities( $message, ENT_QUOTES, $this->detect_encoding($message));
		$this->db->query("UPDATE `" . DB_PREFIX . "yjfpayc_history` SET `status` = 5,  `pay_date` = '" .  date('Y-m-d H:i:s') . "',  `pay_status` = 'fail' , `pay_message` = '" . $message . "' WHERE `order_id` = " . (int)$order_id . "");

    }

	public	function detect_encoding( $str ) {
		// auto detect the character encoding of a string
		return mb_detect_encoding( $str, 'UTF-8,ISO-8859-15,ISO-8859-1,cp1251,KOI8-R' );
	}

	public function read_pay_history($orderID) {
			$qry = $this->db->query("SELECT * FROM " . DB_PREFIX . "yjfpayc_history WHERE order_id = " . (int)$orderID);
			return $qry->row;
	}


	public function yjfpayc_signature(array $params) {
        # sort for key
        ksort($params);

        $clientSignatureString = '';
        foreach ($params as $key => $value) {
            $clientSignatureString .= ($key . '=' . $value . '&');
        }

        $clientSignatureString = substr($clientSignatureString, 0, -1);
        $clientSignatureString = trim($clientSignatureString) . $this->config->get('yjfpayc_secret_key');

        return md5($clientSignatureString);
    }
   public  function array_key_pop($array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $array;
    }



}
?>
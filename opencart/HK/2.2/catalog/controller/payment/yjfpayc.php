<?php
class ControllerPaymentYjfpayc extends Controller {

	public function  index(){



			$data['iframe_url'] =HTTP_SERVER.'index.php?route=payment/yjfpayc/yjfpayc_iframe';
			return $this->load->view('payment/yjfpayc_iframe', $data);



	}
	public function yjfpayc_iframe() {
		$this->language->load('payment/yjfpayc');
			$this->load->model('checkout/order');

	
		
			$server = HTTP_SERVER;
			// $server = $_SERVER['HTTP_HOST'];


			$data['transactionid'] = '';
			$data['text_credit_card'] = $this->language->get('text_credit_card');
			$data['text_wait'] = $this->language->get('text_wait');		
			$data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
			$data['entry_cc_number'] = $this->language->get('entry_cc_number');
			$data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
			$data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');
		
			$data['button_confirm'] = $this->language->get('button_confirm');
			
			$data['months'] = array();
			
			for ($i = 1; $i <= 12; $i++) {
				$data['months'][] = array(
					'text'  => sprintf('%02d', $i),//strftime('%02d', mktime(0, 0, 0, $i, 1, 2000)), 
					'value' => sprintf('%02d', $i)
				);
			}

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if (isset($this->session->data['shipping_method'])) {
                $shippingMethod = $this->session->data['shipping_method'];

                $shipToFirstName  = $order_info['shipping_firstname'];
                $shipToLastName   = $order_info['shipping_lastname'];
                $shipToCountry    = $order_info['shipping_iso_code_3'];
                $shipToState      = $order_info['shipping_zone'];
                $shipToCity       = $order_info['shipping_city'];
                $shipToStreet1    = $order_info['shipping_address_1'];
                $shipToPostalCode = $order_info['shipping_postcode'];

            } else {
                $shippingMethod = array('cost' => 0, 'title' => 'None');

                $shipToFirstName  = $order_info['payment_firstname'];
                $shipToLastName   = $order_info['payment_lastname'];
                $shipToCountry    = $order_info['payment_iso_code_3'];
                $shipToState      = $order_info['payment_zone'];
                $shipToCity       = $order_info['payment_city'];
                $shipToStreet1    = $order_info['payment_address_1'];
                $shipToPostalCode = $order_info['payment_address_1'];
            }

// var_dump($order_info);
// exit();
			/////////////
			$amount = 0;
	    	if(isset($order_info['shipping_iso_code_2'])&&$order_info['shipping_iso_code_2']!=null )
	    	{
	            $shippingcountry=$order_info['shipping_iso_code_2'];
	        }
	    	else 
	    	{
	            $shippingcountry=$order_info['payment_iso_code_2'];//
	        }
	    	
	        if($order_info['payment_iso_code_2']=='US'||$order_info['payment_iso_code_2']=='CA')
	        {
	    	    if(isset($order_info['payment_zone_code'])&&$order_info['payment_zone_code']!=null )
	    	    {
	                $billingstate=$order_info['payment_zone_code'];
	            }        	    
	        }
	        else
	        {
	        	if(isset($order_info['payment_zone'])&&trim($order_info['payment_zone'])!=null )
	        	{
	                $billingstate=$order_info['payment_zone'];
	            }   	    
	        }
		    if($order_info['shipping_iso_code_2']=='US'||$order_info['shipping_iso_code_2']=='CA')
	    	{
	    	    if(isset($order_info['shipping_zone_code'])&&trim($order_info['shipping_zone_code'])!=null )
	    	       $shippingstate=$order_info['shipping_zone_code'];
	            else
	                $shippingstate=$billingstate;
	    	    
	    	}
		    else
		    {
		    	if(isset($order_info['shipping_zone'])&&trim($order_info['shipping_zone'])!=null )
		           $shippingstate=$order_info['shipping_zone'];
		        else
		           $shippingstate=$billingstate;
		    }
	    	//
		$this->load->model('extension/extension');
				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;
		
				// Because __call can not keep var references so we put them into an array. 			
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);
				$totals = array();
				// Display prices
					$sort_order = array();
					$results = $this->model_extension_extension->getExtensions('total');
					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}
					array_multisort($sort_order, SORT_ASC, $results);
					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_total_' . $result['code']}->getTotal($total_data);
						}
					}
					$arr_totals = array();
					$sort_order = array();
					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
						$arr_totals[$value['code']] = $value;
					}
					array_multisort($sort_order, SORT_ASC, $totals);


			if(empty($billingstate)){$billingstate="-";}
			if(empty($shipToState)){$shipToState="-";}
			// var_dump($arr_totals);
			// var_dump($order_info);
			// exit();
            $detail = array(
                'ipAddress'           => $this->get_ip(),
                'billtoCountry'       => $order_info['payment_iso_code_2'],
                'billtoState'         => $billingstate,
                'billtoCity'          => $order_info['payment_city'],
                'billtoStreet'        =>$order_info['payment_address_1'],
                'billtoPostalcode'    => $order_info['payment_postcode'],

                'billtoFirstname'     => $order_info['payment_firstname'],
                'billtoLastname'      => $order_info['payment_lastname'],
                'billtoEmail'         => $order_info['email'],
                'billtoPhonenumber'   => $order_info['telephone'],

                'shiptoCountry'       => $shipToCountry,
                'shiptoState'         => $shipToState,
                'shiptoCity'          => $shipToCity,
                'shiptoStreet'        => $shipToStreet1,
                'shiptoPostalcode'    => $shipToPostalCode,

                'shiptoFirstname'     => $shipToFirstName,
                'shiptoLastname'      => $shipToFirstName,
                'shiptoEmail'         => $order_info['email'],
                'shiptoPhonenumber'   => $order_info['telephone'],

                'logisticsFee'        => $arr_totals['shipping']['value'],
                'logisticsMode'       => $order_info['shipping_method'],
                'customerEmail'       => $order_info['email'],
                'customerPhonenumber' => $order_info['telephone'],
                'merchantEmail'       =>  $this->config->get('yjfpayc_merchant_email'),
                'merchantName'        =>  $this->config->get('yjfpayc_merchant_name')
            );


            $optionProducts = array();
            foreach ($this->cart->getProducts()as $product) {
                array_push($optionProducts, array(
                    'goodsNumber'          => $product['model'],
                    'goodsName'            => $product['name'],
                    'goodsCount'           => $product['quantity'],
                    'itemSharpProductcode' => $product['model'],
                    'itemSharpUnitPrice'   => number_format($this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], FALSE),2,'.','')
                ));
            }

			$arr_optionService = array(
                'userId'        => $this->config->get('yjfpayc_partner_id'),
                'currency'      => $order_info['currency_code'],
                 'orderAmount'   => number_format(($order_info['total'] * $order_info['currency_value']),2,'.',''),
                //'amount'   => number_format(($order_info['total'] * $order_info['currency_value']),2,'.',''),
                'webSite'       => $_SERVER['HTTP_HOST'], # 'www.baidu.com'
                'merchOrderNo'  =>$this->session->data['order_id'],
                // yoko
                'Remark'        =>  $order_info['comment'],
                'acquiringType' =>  $this->config->get('yjfpayc_acquiring_type'),
                // yoko
                //'deviceFingerprintId' => $this->session->getId()
            );

			$order_total    = $order_info['total'];
            $order_detail   =  array('attachDetails' => json_encode($detail));
            $order_products =    array('goodsInfoOrders' => json_encode($optionProducts));
            $service_option = $arr_optionService;

			$orderNo = date('YmdHis') . rand(10000, 999999);
            $options  = array_merge($order_products, $order_detail, $service_option);
            $add_data = base64_encode(json_encode($options));

            $order_history_array = array(
                array('fieldName' => 'order_no', 'type' => 'string', 'value' =>$orderNo),
                array('fieldName' => 'order_id', 'type' => 'integer', 'value' => $this->session->data['order_id']),
                array('fieldName' => 'status', 'type' => 'integer', 'value' => 0),

                array('fieldName' => 'pay_total', 'type' => 'currency', 'value' => $order_total),
                array('fieldName' => 'add_date', 'type' => 'date', 'value' => date('Y-m-d H:i:s')),
                array('fieldName' => 'add_data', 'type' => 'string', 'value' => $add_data)
            );


           // $order_update_array = array(array('fieldName' => 'orders_status', 'type' => 'integer', 'value' => $this->config->get('yjfpayc_submit_status_id')));

			$this->db->query("INSERT INTO "  . "yjfpayc_history SET order_no = '" . $orderNo . "', order_id = '" . $this->session->data['order_id'] . "', status = '".$this->config->get('yjfpayc_submit_status_id')."', pay_total = '" .$order_total. "', add_date = '" . date('Y-m-d H:i:s') . "', add_data = '" .$add_data. "'"); 
			$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_submit_status_id'));

			$gateway = array(
				// 0905
				'service'   => 'cardAcquiringCashierPay',
				'partnerId' => $this->config->get('yjfpayc_partner_id'),
				'orderNo'   => $orderNo,
				// 外部订单号
				//'merchOrderNo' => $this->session->data['order_id'],
				// 'returnUrl' => $server.'yjfpayc_return.php',
				// 'notifyUrl' =>$server.'return_yjfpayc.php'
				'returnUrl' => $this->url->link('checkout/yjfpayc_return'),
				'notifyUrl' => $this->url->link('payment/yjfpayc_notify'),
			);
			//print_r($gateway );
			$allOptions = array_merge($options,$gateway);

			$allOptions['sign'] = $this-> yjfpayc_signature($allOptions);
			$gatewayURL = $this->language->get('YJFPAYC_PRODUCT_URL');

			$data['gatewayURL'] = $gatewayURL;
			$data['allOptions'] = $allOptions;
		$this->response->setOutput($this->load->view('payment/yjfpayc_api', $data));


		//	return $this->load->view('payment/yjfpayc_api', $data);
				//$this->response->setOutput($this->load->view('payment/yjfpayc_api', $data));

			//if($order_info["order_status_id"]<1&&!empty($order_info['email']))
			//{
    			//$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('gofpay_order_status_id'));
			//}	
	}

	public function  yjfpayc_return()
    {   
		$this->load->model('checkout/order');	
		$this->load->model('payment/yjfpayc');	
		$this->language->load('payment/yjfpayc');

		$order_info = $this->model_checkout_order->getOrder($_GET['merchOrderNo']);
		//$post_json = json_encode($_GET,true);
		//file_put_contents("E:/log/post_zen_j1.log", $post_json);

		// echo "post arrar";

		$sign = $_GET['sign'];
		$no_sign_get = $this->model_payment_yjfpayc->array_key_pop($_GET, 'sign');
			//	print_r($sign);echo "<br/>";print_r($this-> yjfpayc_signature($no_sign_get));exit;
		# check sign security
		//if ($sign == $this-> yjfpayc_signature($no_sign_get)) {
			//echo "dd";exit;
			# read order status
			$orderID = $_GET['merchOrderNo'];
			$resultCode  = $_GET['resultCode'];
			$status  = strtolower($_GET['status']);
			if ($orderHistory = $this->model_payment_yjfpayc->read_pay_history($orderID)) {
				# process notify status
				if ($status == 'success') {
					$this->model_payment_yjfpayc->do_pay_success($order_info,$_GET);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_payment_status_id'));
				} else if ($status == 'authorizing') {
					$this->model_payment_yjfpayc->do_pay_authorizing($order_info,$_GET);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_authorize_status_id'));
				 } else if ($status == 'processing') {
					$this->model_payment_yjfpayc->do_pay_processing($order_info,$_GET);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_submit_status_id'));
				 }else if ($status == 'fail') {
					$this->model_payment_yjfpayc->do_pay_fail($order_info,$_GET);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_fail_status_id'));
				}
			}
		//}

		$this->load->model('checkout/order');	  
		$this->language->load('checkout/success');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['breadcrumbs'] = array(); 

      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
		
      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);
				
		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);	
					
      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	);
		
    	$data['heading_title'] = $this->language->get('heading_title');

		if ($this->customer->isLogged()) {
    		$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
		} else {
    		$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}
		
    	$data['button_continue'] = $this->language->get('button_continue');
        $data['button_account'] ='My Account';
    	$data['continue'] = $this->url->link('common/home');
    	$data['account']=$this->url->link('account/account');

    	$data['resultMessage']=$_GET['description'] . '(' . $_GET['status'] . ')';


			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('common/return_yjfpayc', $data));

	}
	
    function yjfpayc_signature(array $params) {
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
	
	public function  callback()
    {   
    	try
        {
            $this->load->model('checkout/order');
            $transactionid = $_POST['transactionid'];
            $orderid = $_POST['orderid'];
            $status = $_POST['status'];
            $currency = $_POST['currency'];
    	    //if(!empty($_POST['amount']))//*********
            $amount = $_POST['amount'];
    	    //if(!empty($_POST['currencytype']))//*********
            $originalcurrency = $_POST['originalcurrency'];
            $originalamount = $_POST['originalamount'];
            $signature = $_POST['signature'];
            $secretkey = $this->config->get('gofpay_secretkey');

    	    //if(empty($Amount))$Signature_local=md5($TransactionId.$OrderId.$Status.$SecretKey);//*********
    	    //else
            $signature_local = md5($transactionid . $orderid . $status . $currency . $amount . $originalcurrency . $originalamount . $secretkey);         
            if ($status != 'error' && !strcasecmp ($signature_local, $signature))
            {
            //if(empty($Amount))$note='abdpay notification-'. "transactionid: " . $TransactionId .", " . "orderid: " . $OrderId . ", " . "status: " . $Status ."";
    		//else
                $note='gofpay notification-'. "transactionid: " . $transactionid .", " . "orderid: " . $orderid . ", " . "status: " . $status . ", " . "amount: " . $currency.$amount . "";
                //TODO:记录日志
                //$this->add_log('receive order notify: transactionid=' . $transactionid . '&orderid=' . $orderid . '&status=' . $status . '&currency=' . $currency . '&amount=' . $amount . '&originalcurrency=' . $originalcurrency . '&originalamount=' . $originalamount. '&signature=' . $signature . "\r\n");
                //{
                    // TODO: Add your code...
                    //$this->model_checkout_order->update($orderid,1,$note);//Pending
                    //$array= $this->model_checkout_order->getOrder($orderid);
                    //if(!$array['order_status_id']==1)throw new Exception('The SQL statement is not executed successfully');
                //}

                if ($status === 'Failure')
                {
                    // TODO: Add your code...
                    //$this->model_checkout_order->update($orderid,10,$note);//Failed
                    //$array= $this->model_checkout_order->getOrder($orderId);
                    //if(!$array['order_status_id']==10)throw new Exception('The SQL statement is not executed successfully');
                }

                if ($status === 'Processing')
                {
                    // TODO: Add your code...
                    //$this->model_checkout_order->update($orderid,2,$note);//Failed
                    //$array= $this->model_checkout_order->getOrder($orderid);
                    //if(!$array['order_status_id']==2)throw new Exception('The SQL statement is not executed successfully');
                }
                if ($status == 'Success' )
              	{
        			$this->model_checkout_order->update($orderid,$this->config->get('gofpay_order_notify_status_id'),$note);//Complete 
              		$array= $this->model_checkout_order->getOrder($orderid);
        		    if(!$array['order_status_id']==5)throw new Exception('The SQL statement is not executed successfully');
              	}
            }
          
            else
            { 
          	 throw new Exception('The verification of the data is invalid!');
          	}
        }
        catch (Exception $e)
        {
        	$this->add_log('signature validate error: transactionid=' . $transactionid . '&orderid=' . $orderid . '&status=' . $status . '&currency=' . $currency . '&amount=' . $amount . '&originalcurrency=' . $originalcurrency . '&originalamount=' . $originalamount . '&signature=' . $signature);
        	echo $e->getMessage();
        	header('HTTP/1.1 404 Not Found');
        }
    }

    function get_ip()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $online_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $online_ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            $online_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $online_ip;
    }

	function explode_return_str($original_str)
	{
        $original_str=explode('&',$original_str);
        $middle_str=array();
        $last_str=array();
        for($i=0;$i<count($original_str);$i++){
          $middle_str[$i]=explode('=',$original_str[$i]);
        }
        for($i=0;$i<count($middle_str);$i++){
          $last_str[$middle_str[$i][0]]=$middle_str[$i][1];
        }  
        return $last_str;
    }

    function add_log ($log)
    {
        $fp = fopen("system/logs/gofpay-log-" . date("Y-m-d") . ".txt", "a");
        flock($fp, LOCK_EX) ;
        fwrite($fp, "[" . date("Y-m-d h:i:s") . "]" . $log . "\r\n");
        flock($fp, LOCK_UN); 
        fclose($fp);
    }
}
?>
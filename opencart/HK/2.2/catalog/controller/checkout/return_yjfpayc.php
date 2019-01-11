<?php 
class ControllerCheckoutReturnYjfpayc extends Controller { 
	public function index() { 
	  $this->load->model('checkout/order');	
	  $this->load->model('payment/yjfpayc');	
		$this->language->load('payment/yjfpayc');

	$order_info = $this->model_checkout_order->getOrder($_POST['merchOrderNo']);
    $post_json = json_encode($_POST,true);
    //file_put_contents("E:/log/post_zen_j1.log", $post_json);



    // echo "post arrar";
    // var_dump($_POST);
	$sign = $_POST['sign'];
    $no_sign_get = $this->model_payment_yjfpayc->array_key_pop($_POST, 'sign');

    # check sign security
    if ($sign == $this-> yjfpayc_signature($no_sign_get)) {
        # read order status
        $orderID = $_POST['merchOrderNo'];
			$resultCode  = $_POST['resultCode'];
			$status  = strtolower($_POST['status']);

			if ($orderHistory = $this->model_payment_yjfpayc->read_pay_history($orderID)) {
				# process notify status
				if ($status == 'success') {
					$this->model_payment_yjfpayc->do_pay_success($order_info,$_POST);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_payment_status_id'));
				} else if ($status == 'authorizing') {
					$this->model_payment_yjfpayc->do_pay_authorizing($order_info,$_POST);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_authorize_status_id'));
				 } else if ($status == 'processing') {
					$this->model_payment_yjfpayc->do_pay_processing($order_info,$_POST);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_submit_status_id'));
				 }else if ($status == 'fail') {
					$this->model_payment_yjfpayc->do_pay_fail($order_info,$_POST);
					$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('yjfpayc_fail_status_id'));
				}
			}
    }

    echo 'success';exit;
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


			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('common/return_yjfpayc', $data));
  	}
}
?>
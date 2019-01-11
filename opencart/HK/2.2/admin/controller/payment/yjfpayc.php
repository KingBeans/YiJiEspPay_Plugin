<?php 
class ControllerPaymentYjfpayc extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/yjfpayc');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('yjfpayc', $this->request->post);
			$this->load->model('payment/espay');				
			
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['MODULE_PAYMENT_YJFPAYC_TEXT_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_TEXT_TITLE');	

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_TITLE');

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_TITLE');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_TITLE');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_TITLE');		


		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_TITLE');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_DESCRIPTION');		

		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_TITLE'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_TITLE');		
		$data['MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_DESCRIPTION'] = $this->language->get('MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_DESCRIPTION');		

		$data['text_all_zones'] = $this->language->get('text_all_zones');


		//$data['text_test'] = $this->language->get('text_test');
		//$data['text_live'] = $this->language->get('text_live');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_capture'] = $this->language->get('text_capture');		
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}



		//fffffffffffffffff
		if(isset($this->error['entry_gateway'])){
			$data['error_gateway'] = $this->error['entry_gateway'];
		} else {
			$data['error_gateway'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/yjfpayc', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$data['action'] = $this->url->link('payment/yjfpayc', 'token=' . $this->session->data['token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['yjfpayc_status'])) {
			$data['yjfpayc_status'] = $this->request->post['yjfpayc_status'];
		} else {
			$data['yjfpayc_status'] = $this->config->get('yjfpayc_status');
		}

		if (isset($this->request->post['merchant_name'])) {
			$data['yjfpayc_merchant_name'] = $this->request->post['yjfpayc_merchant_name'];
		} else {
			$data['yjfpayc_merchant_name'] = $this->config->get('yjfpayc_merchant_name');
		}
	
		if (isset($this->request->post['merchant_email'])) {
			$data['yjfpayc_merchant_email'] = $this->request->post['yjfpayc_merchant_email'];
		} else {
			$data['yjfpayc_merchant_email'] = $this->config->get('yjfpayc_merchant_email');
		}
		
		if (isset($this->request->post['partner_id'])) {
			$data['yjfpayc_partner_id'] = $this->request->post['yjfpayc_partner_id'];
		} else {
			$data['yjfpayc_partner_id'] = $this->config->get('yjfpayc_partner_id');
		}
		
		if (isset($this->request->post['secret_key'])) {
			$data['yjfpayc_secret_key'] = $this->request->post['yjfpayc_secret_key'];
		} else {
			$data['yjfpayc_secret_key'] = $this->config->get('yjfpayc_secret_key');
		}
		
		if (isset($this->request->post['acquiring_type'])) {
			$data['yjfpayc_acquiring_type'] = $this->request->post['yjfpayc_acquiring_type'];
		} else {
			$data['yjfpayc_acquiring_type'] = $this->config->get('yjfpayc_acquiring_type');
		}
		
		if (isset($this->request->post['yjfpayc_submit_status_id'])) {
			$data['yjfpayc_submit_status_id'] = $this->request->post['yjfpayc_submit_status_id'];
		} else {
			$data['yjfpayc_submit_status_id'] = $this->config->get('yjfpayc_submit_status_id'); 
		} 

		if (isset($this->request->post['yjfpayc_payment_status_id'])) {
			$data['yjfpayc_payment_status_id'] = $this->request->post['yjfpayc_payment_status_id'];
		} else {
			$data['yjfpayc_payment_status_id'] = $this->config->get('yjfpayc_payment_status_id'); 
		}
		if (isset($this->request->post['yjfpayc_authorize_status_id'])) {
			$data['yjfpayc_authorize_status_id'] = $this->request->post['yjfpayc_authorize_status_id'];
		} else {
			$data['yjfpayc_authorize_status_id'] = $this->config->get('yjfpayc_authorize_status_id'); 
		}
		if (isset($this->request->post['yjfpayc_fail_status_id'])) {
			$data['yjfpayc_fail_status_id'] = $this->request->post['yjfpayc_fail_status_id'];
		} else {
			$data['yjfpayc_fail_status_id'] = $this->config->get('yjfpayc_fail_status_id'); 
		}

		if (isset($this->request->post['yjfpayc_gateway_url_debug'])) {
			$data['yjfpayc_gateway_url_debug'] = $this->request->post['yjfpayc_gateway_url_debug'];
		} else {
			$data['yjfpayc_gateway_url_debug'] = $this->config->get('yjfpayc_gateway_url_debug');
		}

		if (isset($this->request->post['yjfpayc_sort_order'])) {
			$data['yjfpayc_sort_order'] = $this->request->post['yjfpayc_sort_order'];
		} else {
			$data['yjfpayc_sort_order'] = $this->config->get('yjfpayc_sort_order');
		}


		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['yjfpayc_geo_zone_id'])) {
			$data['yjfpayc_geo_zone_id'] = $this->request->post['yjfpayc_geo_zone_id'];
		} else {
			$data['yjfpayc_geo_zone_id'] = $this->config->get('yjfpayc_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();




		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/yjfpayc', $data));

	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/yjfpayc')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		

		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
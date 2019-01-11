<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/20
 * Time: 14:20
 */
class ControllerExtensionPaymentYjpay extends Controller {

    /**
     * @var array
     */
    private $error = array();

    /**
     *
     */
    public function index() {

        $this->load->language('extension/payment/yjpay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            //$this->request->post['yjpay_status'] = 1;
            $this->model_setting_setting->editSetting('payment_yjpay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get( 'heading_title' );
        $data['text_partnerId'] = $this->language->get( 'text_partnerId' );
        $data['text_secretKey'] = $this->language->get( 'text_secretKey' );
        $data['text_email'] = $this->language->get( 'text_email' );
        $data['text_mname'] = $this->language->get( 'text_mname' );
        $data['text_notifyUrl'] = $this->language->get( 'text_notifyUrl' );
        $data['entry_debug'] = $this->language->get( 'entry_debug' );
        $data['entry_status'] = $this->language->get( 'entry_status' );
        $data['text_sort_order'] = $this->language->get( 'text_sort_order' );
        $data['entry_style'] = $this->language->get( 'entry_style' );
        $data['entry_currency'] = $this->language->get( 'entry_currency' );
        $data['entry_language'] = $this->language->get( 'entry_language' );

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();



        $data['entry_start_status'] = $this->language->get( 'entry_start_status' );
        $data['entry_success_status'] = $this->language->get( 'entry_success_status' );
        $data['entry_authorizing_status'] = $this->language->get( 'entry_authorizing_status' );
        $data['entry_fail_status'] = $this->language->get( 'entry_fail_status' );



        if (isset ( $this->error ['warning'] )){
            $data ['error_warning'] = $this->error ['warning'];
        } else {
            $data ['error_warning'] = '';
        }

        if(count($this->error) > 0 ){
            foreach ($this->error as $k => $v ){
                $data['error_'.$k] = $v;
            }
        }

        $data['payment_yjpay_debug'] = isset($this->request->post['payment_yjpay_debug']) ? $this->request->post['payment_yjpay_debug'] : $this->config->get( 'payment_yjpay_debug' );
        $data['payment_yjpay_status'] = isset($this->request->post['payment_yjpay_status']) ? $this->request->post['payment_yjpay_status'] : $this->config->get( 'payment_yjpay_status' );
        $data['payment_yjpay_sort_order'] = isset($this->request->post['payment_yjpay_sort_order']) ? $this->request->post['payment_yjpay_sort_order'] : $this->config->get( 'payment_yjpay_sort_order' );
        $data['payment_yjpay_style'] = isset($this->request->post['payment_yjpay_style']) ? $this->request->post['payment_yjpay_style'] : $this->config->get( 'payment_yjpay_style' );
        $data['payment_yjpay_partnerId'] = isset($this->request->post['payment_yjpay_partnerId']) ? $this->request->post['payment_yjpay_partnerId'] : $this->config->get( 'payment_yjpay_partnerId' );
        $data['payment_yjpay_secretKey'] = isset($this->request->post['payment_yjpay_secretKey']) ? $this->request->post['payment_yjpay_secretKey'] : $this->config->get( 'payment_yjpay_secretKey' );
        $data['payment_yjpay_email'] = isset($this->request->post['payment_yjpay_email']) ? $this->request->post['payment_yjpay_email'] : $this->config->get( 'payment_yjpay_email' );
        $data['payment_yjpay_mname'] = isset($this->request->post['payment_yjpay_mname']) ? $this->request->post['payment_yjpay_mname'] : $this->config->get( 'payment_yjpay_mname' );
        $data['payment_yjpay_currency'] = isset($this->request->post['payment_yjpay_currency']) ? $this->request->post['payment_yjpay_currency'] : $this->config->get( 'payment_yjpay_currency' );
        $data['payment_yjpay_language'] = isset($this->request->post['payment_yjpay_language']) ? $this->request->post['payment_yjpay_language'] : $this->config->get( 'payment_yjpay_language' );
        $data['payment_yjpay_success_status_id'] = isset($this->request->post['payment_yjpay_success_status_id']) ? $this->request->post['payment_yjpay_success_status_id'] : $this->config->get( 'payment_yjpay_success_status_id' );
        $data['payment_yjpay_authorizing_status_id'] = isset($this->request->post['payment_yjpay_authorizing_status_id']) ? $this->request->post['payment_yjpay_authorizing_status_id'] : $this->config->get( 'payment_yjpay_authorizing_status_id' );
        $data['payment_yjpay_fail_status_id'] = isset($this->request->post['payment_yjpay_fail_status_id']) ? $this->request->post['payment_yjpay_fail_status_id'] : $this->config->get( 'payment_yjpay_fail_status_id' );
        $data['payment_yjpay_processing_status_id'] = isset($this->request->post['payment_yjpay_processing_status_id']) ? $this->request->post['payment_yjpay_processing_status_id'] : $this->config->get( 'payment_yjpay_processing_status_id' );
        $data['payment_yjpay_geo_zone_id'] = isset($this->request->post['payment_yjpay_geo_zone_id']) ? $this->request->post['payment_yjpay_geo_zone_id'] : $this->config->get( 'payment_yjpay_geo_zone_id' );

        $data['action'] = $this->url->link('extension/payment/yjpay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['breadcrumbs'] = array(
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ),
            array(
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/yjpay', 'user_token=' . $this->session->data['user_token'], true)
            ),
        );

        $this->response->setOutput($this->load->view('extension/payment/yjpay', $data));

    }

    private function validate(){
        if (!$this->user->hasPermission('modify', 'extension/payment/yjpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (! $this->request->post ['payment_yjpay_partnerId']) {
            $this->error['partnerId'] = $this->language->get ( 'error_partnerId' );
        }

        if (! $this->request->post ['payment_yjpay_secretKey']) {
            $this->error['secretKey'] = $this->language->get ( 'error_secretKey' );
        }

        return !$this->error;
    }
}
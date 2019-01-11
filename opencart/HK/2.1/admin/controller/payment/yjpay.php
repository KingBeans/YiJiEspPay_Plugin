<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/30
 * Time: 13:45
 */
class ControllerPaymentYjpay extends Controller
{
    private $error = [];
    public function index() {
        $this->load->language('payment/yjpay');
        $this->document->setTitle ( $this->language->get ( 'heading_title' ) );
        $this->load->model ( 'setting/setting' );
        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate ())) {
            //$this->load->model ( 'setting/setting' );
            $this->request->post['yjpay_notifyUrl'] = $this->url->link('extension/payment/yjpay/notify');
            $this->model_setting_setting->editSetting( 'yjpay', $this->request->post );

            $this->session->data['success'] = $this->language->get( 'text_success' );

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));

        }

        $data['heading_title'] = $this->language->get( 'heading_title' );
        $data['text_partnerId'] = $this->language->get ( 'text_partnerId' );
        $data['text_secretKey'] = $this->language->get ( 'text_secretKey' );
        $data['text_email'] = $this->language->get ( 'text_email' );
        $data['text_mname'] = $this->language->get ( 'text_mname' );
        $data['text_notifyUrl'] = $this->language->get ( 'text_notifyUrl' );
        $data['entry_debug'] = $this->language->get ( 'entry_debug' );
        $data['entry_status'] = $this->language->get ( 'entry_status' );
        $data['text_sort_order'] = $this->language->get ( 'text_sort_order' );
        $data['entry_style'] = $this->language->get ( 'entry_style' );
        $data['entry_currency'] = $this->language->get ( 'entry_currency' );
        $data['entry_language'] = $this->language->get ( 'entry_language' );
        $data['orderStatusList'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['entry_start_status'] = $this->language->get ( 'entry_start_status' );
        $data['entry_success_status'] = $this->language->get ( 'entry_success_status' );
        $data['entry_authorizing_status'] = $this->language->get ( 'entry_authorizing_status' );
        $data['entry_fail_status'] = $this->language->get ( 'entry_fail_status' );


        if (isset ( $this->error ['warning'] )) {
            $data ['error_warning'] = $this->error ['warning'];
        } else {
            $data ['error_warning'] = '';
        }

        if(count($this->error) > 0 ){
            foreach ($this->error as $k => $v ){
                $data['error_'.$k] = $v;
            }
        }

        $data['yjpay_debug'] = isset($this->request->post['yjpay_debug']) ? $this->request->post['yjpay_debug'] : $this->config->get( 'yjpay_debug' );
        $data['yjpay_status'] = isset($this->request->post['yjpay_status']) ? $this->request->post['yjpay_status'] : $this->config->get( 'yjpay_status' );
        $data['yjpay_sort_order'] = isset($this->request->post['yjpay_sort_order']) ? $this->request->post['yjpay_sort_order'] : $this->config->get( 'yjpay_sort_order' );
        $data['yjpay_style'] = isset($this->request->post['yjpay_style']) ? $this->request->post['yjpay_style'] : $this->config->get( 'yjpay_style' );
        $data['yjpay_partnerId'] = isset($this->request->post['yjpay_partnerId']) ? $this->request->post['yjpay_partnerId'] : $this->config->get( 'yjpay_partnerId' );
        $data['yjpay_secretKey'] = isset($this->request->post['yjpay_secretKey']) ? $this->request->post['yjpay_secretKey'] : $this->config->get( 'yjpay_secretKey' );
        $data['yjpay_email'] = isset($this->request->post['yjpay_email']) ? $this->request->post['yjpay_email'] : $this->config->get( 'yjpay_email' );
        $data['yjpay_mname'] = isset($this->request->post['yjpay_mname']) ? $this->request->post['yjpay_mname'] : $this->config->get( 'yjpay_mname' );
        $data['yjpay_currency'] = isset($this->request->post['yjpay_currency']) ? $this->request->post['yjpay_currency'] : $this->config->get( 'yjpay_currency' );
        $data['yjpay_language'] = isset($this->request->post['yjpay_language']) ? $this->request->post['yjpay_language'] : $this->config->get( 'yjpay_language' );
        $data['yjpay_start_status'] = isset($this->request->post['yjpay_start_status']) ? $this->request->post['yjpay_start_status'] : $this->config->get( 'yjpay_start_status' );
        $data['yjpay_success_status'] = isset($this->request->post['yjpay_success_status']) ? $this->request->post['yjpay_success_status'] : $this->config->get( 'yjpay_success_status' );
        $data['yjpay_authorizing_status'] = isset($this->request->post['yjpay_authorizing_status']) ? $this->request->post['yjpay_authorizing_status'] : $this->config->get( 'yjpay_authorizing_status' );
        $data['yjpay_fail_status'] = isset($this->request->post['yjpay_fail_status']) ? $this->request->post['yjpay_fail_status'] : $this->config->get( 'yjpay_fail_status' );
        $data['yjpay_notifyUrl'] =  $this->config->get( 'yjpay_notifyUrl' ) ? $this->config->get( 'yjpay_notifyUrl' ) :'' ;

        //var_dump($data);

        $data['action'] = $this->url->link('payment/yjpay', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['breadcrumbs'] = array(
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
            ),
            array(
                'text' => $this->language->get('text_payment'),
                'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
            ),
            array(
                'text' => $this->language->get( 'heading_title' ),
                'href' => $this->url->link('payment/yjpay', 'token=' . $this->session->data['token'], 'SSL')
            )
        );

        $this->response->setOutput($this->load->view('payment/yjpay.tpl', $data));


    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'payment/yjpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (! $this->request->post ['yjpay_partnerId']) {
            $this->error['partnerId'] = $this->language->get ( 'error_partnerId' );
        }

        if (! $this->request->post ['yjpay_secretKey']) {
            $this->error['secretKey'] = $this->language->get ( 'error_secretKey' );
        }

        return !$this->error;
    }
}
<?php

class ControllerPaymentEspay extends Controller {
    private $error = array();

    public function index() {
        # load language file
        $this->load->language('payment/espay');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('espay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }
        # Order status
        $this->load->model('localisation/order_status');
        $data = array(
            'heading_title' => $this->language->get('heading_title'),
            'text_merchant_info' => $this->language->get('text_merchant_info'),
            'text_order_status' => $this->language->get('text_order_status'),
            'text_service_url' => $this->language->get('text_service_url'),
            'error_warning' => $this->errpr['warning'],
            'text_status' => $this->language->get('text_status'),

            'order_statuses' => $this->model_localisation_order_status->getOrderStatuses()
        );

        # merchant name
        $data['entry_merchant_name'] = $this->language->get('entry_merchant_name');
        if (isset($this->request->post['espay_merchant_name'])) {
            $data['espay_merchant_name'] = $this->request->post['espay_merchant_name'];
        } elseif ($this->config->get('espay_merchant_name')) {
            $data['espay_merchant_name'] = $this->config->get('espay_merchant_name');
        } else {
            $data['espay_merchant_name'] = '';
        }

        if (isset($this->error['error_merchant_name'])) {
            $data['error_merchant_name'] = $this->error['error_merchant_name'];
        } else {
            $data['error_merchant_name'] = '';
        }

        $data['entry_merchant_email'] = $this->language->get('entry_merchant_email');
        if (isset($this->request->post['espay_merchant_email'])) {
            $data['espay_merchant_email'] = $this->request->post['espay_merchant_email'];
        } elseif ($this->config->get('espay_merchant_email')) {
            $data['espay_merchant_email'] = $this->config->get('espay_merchant_email');
        } else {
            $data['espay_merchant_email'] = '';
        }

        if (isset($this->error['error_merchant_email'])) {
            $data['error_merchant_email'] = $this->error['error_merchant_email'];
        } else {
            $data['error_merchant_email'] = '';
        }

        # partner id
        $data['entry_partner_id'] = $this->language->get('entry_partner_id');
        if (isset($this->request->post['espay_partner_id'])) {
            $data['espay_partner_id'] = $this->request->post['espay_partner_id'];
        } elseif ($this->config->get('espay_partner_id')) {
            $data['espay_partner_id'] = $this->config->get('espay_partner_id');
        } else {
            $data['espay_partner_id'] = '';
        }

        if (isset($this->error['error_partner_id'])) {
            $data['error_partner_id'] = $this->error['error_partner_id'];
        } else {
            $data['error_partner_id'] = '';
        }

        # secret key
        $data['entry_certificate_cipher'] = $this->language->get('entry_certificate_cipher');
        if (isset($this->request->post['espay_certificate_cipher'])) {
            $data['espay_certificate_cipher'] = $this->request->post['espay_certificate_cipher'];
        } elseif ($this->config->get('espay_certificate_cipher')) {
            $data['espay_certificate_cipher'] = $this->config->get('espay_certificate_cipher');
        } else {
            $data['espay_certificate_cipher'] = '';
        }

        if (isset($this->error['error_certificate_cipher'])) {
            $data['error_certificate_cipher'] = $this->error['error_certificate_cipher'];
        } else {
            $data['error_certificate_cipher'] = '';
        }

        # currency
        $data['entry_currency']       = $this->language->get('entry_currency');
        $data['entry_currency_range'] = $this->language->get('entry_currency_range');
        if (isset($this->request->post['espay_currency'])) {
            $data['espay_currency'] = $this->request->post['espay_currency'];
        } elseif ($this->config->get('espay_currency')) {
            $data['espay_currency'] = $this->config->get('espay_currency');
        } else {
            $data['espay_currency'] = 'USD';
        }

        if (isset($this->error['error_currency'])) {
            $data['error_currency'] = $this->error['error_currency'];
        } else {
            $data['error_currency'] = '';
        }

        $data['entry_certificate_cipher'] = $this->language->get('entry_certificate_cipher');
        if (isset($this->request->post['espay_certificate_cipher'])) {
            $data['espay_certificate_cipher'] = $this->request->post['espay_certificate_cipher'];
        } elseif ($this->config->get('espay_certificate_cipher')) {
            $data['espay_certificate_cipher'] = $this->config->get('espay_certificate_cipher');
        } else {
            $data['espay_certificate_cipher'] = '';
        }

        if (isset($this->error['error_certificate_cipher'])) {
            $data['error_certificate_cipher'] = $this->error['error_certificate_cipher'];
        } else {
            $data['error_certificate_cipher'] = '';
        }

        $data['entry_status_submit'] = $this->language->get('entry_status_submit');
        if (isset($this->request->post['espay_status_submit'])) {
            $data['espay_status_submit'] = $this->request->post['espay_status_submit'];
        } elseif ($this->config->get('espay_status_submit')) {
            $data['espay_status_submit'] = $this->config->get('espay_status_submit');
        } else {
            $data['espay_status_submit'] = '';
        }
        if (isset($this->error['error_status_submit'])) {
            $data['error_status_submit'] = $this->error['error_status_submit'];
        } else {
            $data['error_status_submit'] = '';
        }

        $data['entry_status_authorize'] = $this->language->get('entry_status_authorize');
        if (isset($this->request->post['espay_status_authorize'])) {
            $data['espay_status_authorize'] = $this->request->post['espay_status_authorize'];
        } elseif ($this->config->get('espay_status_authorize')) {
            $data['espay_status_authorize'] = $this->config->get('espay_status_authorize');
        } else {
            $data['espay_status_authorize'] = '';
        }

        if (isset($this->error['error_status_authorize'])) {
            $data['error_status_authorize'] = $this->error['error_status_authorize'];
        } else {
            $data['error_status_authorize'] = '';
        }

        $data['entry_status_complete'] = $this->language->get('entry_status_complete');
        if (isset($this->request->post['espay_status_complete'])) {
            $data['espay_status_complete'] = $this->request->post['espay_status_complete'];
        } elseif ($this->config->get('espay_status_complete')) {
            $data['espay_status_complete'] = $this->config->get('espay_status_complete');
        } else {
            $data['espay_status_complete'] = '';
        }

        if (isset($this->error['error_status_complete'])) {
            $data['error_status_complete'] = $this->error['error_status_complete'];
        } else {
            $data['error_status_complete'] = '';
        }

        $data['entry_status_fail'] = $this->language->get('entry_status_fail');
        if (isset($this->request->post['espay_status_fail'])) {
            $data['espay_status_fail'] = $this->request->post['espay_status_fail'];
        } elseif ($this->config->get('espay_status_fail')) {
            $data['espay_status_fail'] = $this->config->get('espay_status_fail');
        } else {
            $data['espay_status_fail'] = '';
        }

        if (isset($this->error['error_status_fail'])) {
            $data['error_status_fail'] = $this->error['error_status_fail'];
        } else {
            $data['error_status_fail'] = '';
        }


        # service url
        $data['entry_notify'] = $this->language->get('entry_notify');
        $data['espay_notify'] = 'http://' . $_SERVER['SERVER_NAME'] . '/catalog/controller/payment/espay_notify.php';

        $data['entry_debug']       = $this->language->get('entry_debug');
        $data['entry_debug_range'] = $this->language->get('entry_debug_range');
        if (isset($this->request->post['espay_debug'])) {
            $data['espay_debug'] = $this->request->post['espay_debug'];
        } elseif ($this->config->get('espay_debug')) {
            $data['espay_debug'] = $this->config->get('espay_debug');
        } else {
            $data['espay_debug'] = 'debug';
        }

        if (isset($this->error['error_debug'])) {
            $data['error_debug'] = $this->error['error_debug'];
        } else {
            $data['error_debug'] = '';
        }

        # Yiji status
        $this->load->model('localisation/geo_zone');

        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_geo_all']  = $this->language->get('entry_geo_all');
        $data['geo_zones']      = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['espay_geo_zone'])) {
            $data['espay_geo_zone'] = $this->request->post['espay_geo_zone'];
        } else {
            $data['espay_geo_zone'] = $this->config->get('espay_geo_zone');
        }

        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        if (isset($this->request->post['espay_sort_order'])) {
            $data['espay_sort_order'] = $this->request->post['espay_sort_order'];
        } else {
            $data['espay_sort_order'] = $this->config->get('espay_sort_order');
        }


        $data['entry_status']       = $this->language->get('entry_status');
        $data['entry_status_range'] = $this->language->get('entry_status_range');

        if (isset($this->request->post['espay_status'])) {
            $data['espay_status'] = $this->request->post['espay_status'];
        } elseif ($this->config->get('espay_status')) {
            $data['espay_status'] = $this->config->get('espay_status');
        } else {
            $data['espay_status'] = '0';
        }

        # breadcrumbs
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
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('payment/espay', 'token=' . $this->session->data['token'], 'SSL')
            )
        );

        # btn toolbar
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_save']   = $this->language->get('button_save');

        $data['action'] = $this->url->link('payment/espay', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        # edit from
        $data['text_edit'] = $this->language->get('text_edit');

        # layout controller
        $data['header']      = $this->load->controller('common/header');
        $data['footer']      = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');

        $this->response->setOutput($this->load->view('payment/espay.tpl', $data));
    }


    public function order() {
        require_once(DIR_SYSTEM . 'vendor/espay_api.php');

        $orderID = $this->request->get['order_id'];
        $this->load->model('payment/espay');

        $espay = $this->model_payment_espay->query($orderID);
        if ($espay == false) return '';

        if ($espay['status'] == ModelPaymentEspay::STATUS_NEW ||
            $espay['status'] == ModelPaymentEspay::STATUS_AUTHORIZED ||
            $espay['status'] == ModelPaymentEspay::STATUS_AUTHORIZING
        ) {
            $api = new ESPayApi(
                $this->config->get('espay_partner_id'),
                $this->config->get('espay_certificate_cipher'),
                $this->config->get('espay_debug')
            );

            $result = $api->queryOrderPay(date('YmdHis', $espay['order_date']) . $orderID);

            if ($result) {
                if ($result->orderStatus == 'PAY_SUCCESS' ) {
                    $this->model_payment_espay->payed($orderID, $result->tradeTime);
                    $this->model_payment_espay->addOrderHistory($orderID,
                        $this->config->get('espay_status_complete'),
                        '<strong>YJF Pay: </strong>' . sprintf('%.2f',$result->amountLoc)
                    );
                    $espay = $this->model_payment_espay->query($orderID);
                } else if ($result->orderStatus == 'PAY_FAIL') {
                    $this->model_payment_espay->payedFail($orderID, $result->tradeTime);
                    $this->model_payment_espay->addOrderHistory($orderID,
                        $this->config->get('espay_status_fail'),
                        '<strong>YJF Pay Fail: </strong>' . sprintf('%.2f',$result->amountLoc)
                    );
                    $espay = $this->model_payment_espay->query($orderID);
                } else if ($result->orderStatus == 'AUTHORIZE_SUCCESS') {
                    $this->model_payment_espay->authorizing($orderID, $result->tradeTime);
                    $this->model_payment_espay->addOrderHistory($orderID,
                        $this->config->get('espay_status_authorize'),
                        '<strong>YJF Wait authorize: </strong>' . sprintf('%.2f',$result->amountLoc)
                    );
                    $espay = $this->model_payment_espay->query($orderID);
                }
            }
        }

        $this->load->language('payment/espay');
        $data = array();

        $data['textHeading']    = $this->language->get('view_text_heading');
        $data['textStatus']     = $this->language->get('view_status');
        $data['textOrderFee']   = $this->language->get('view_order_fee');
        $data['textPaymentFee'] = $this->language->get('view_payment_fee');
        $data['textAuthorize']  = $this->language->get('view_authorize');
        $data['textRefundFee']  = $this->language->get('view_refund_fee');

        $data['statuses']         = $this->language->get('view_status_names');
        $data['authorizes']       = $this->language->get('view_authorize_names');
        $data['authorizeResults'] = $this->language->get('view_authorize_result');

        $data['espay']         = $espay;
        $data['paymentDate']   = $espay['payment_date'] ? date('Y-m-d H:i:s', $espay['payment_date']) : '';
        $data['refundDate']    = $espay['refund_date'] ? date('Y-m-d H:i:s', $espay['refund_date']) : '';
        $data['authorizeDate'] = $espay['authorize_date'] ? date('Y-m-d H:i:s', $espay['authorize_date']) : '';

        $data['token']    = $this->session->data['token'];
        $data['order_id'] = $orderID;
        $data['order_sn'] = date('YmdHis', $espay['order_date']) . $orderID;

        $data['cancel_confirm']    = $this->language->get('view_cancel_confirm');
        $data['refund_confirm']    = $this->language->get('view_refund_confirm');
        $data['authorize_summary'] = $this->language->get('view_authorize_summary');

        $data['button_refund'] = $this->language->get('view_button_refund');
        $data['button_cancel'] = $this->language->get('view_button_cancel');

        $data['button_authorize_pass'] = $this->language->get('view_button_authorize_pass');
        $data['button_authorize_deny'] = $this->language->get('view_button_authorize_deny');

        return $this->load->view('payment/espay_order.tpl', $data);
    }


    public function cancel() {
        $this->load->language('payment/espay');
        $this->load->model('payment/espay');
        $this->load->model('sale/order');

        $orderID = $this->request->get['order_id'];
        $espay   = $this->model_payment_espay->query($orderID);

        if ($espay && $espay['status'] == ModelPaymentEspay::STATUS_PAYED) {
            if ($this->request->server['REQUEST_METHOD'] == 'POST') {
                require_once(DIR_SYSTEM . 'vendor/espay_api.php');

                $api = new ESPayApi(
                    $this->config->get('espay_partner_id'),
                    $this->config->get('espay_certificate_cipher'),
                    $this->config->get('espay_debug')
                );

                $orderSN     = date('YmdHis', $espay['order_date']) . $orderID;
                $message     = isset($this->request->post['cancel_note']) ? $this->request->post['cancel_note'] : '';
                $orderStatus = $this->request->post['order_status'];

                $result = $api->cancelOrderPay($orderSN, $message);

                if ($result->resultCode == 'EXECUTE_SUCCESS') {
                    # cancel espay order informat
                    $this->model_payment_espay->cancel($orderID);
                    $this->model_payment_espay->addOrderHistory($orderID, $orderStatus, '<strong>YJF Cancel: </strong>' . $message);

                    $this->response->setOutput($this->load->view('payment/espay_message.tpl', array(
                        'token' => 'success',
                        'summary' => $this->language->get('view_cancel_success')
                    )));
                    return;
                } else {
                    $this->error['error'] = $result->resultMessage;
                }
            }

            $data = array();

            $data['order_status']   = $this->language->get('view_order_status');
            $data['cancel_heading'] = $this->language->get('view_cancel_heading');
            $data['cancel_note']    = $this->language->get('view_cancel_note');

            $data['button_submit'] = $this->language->get('view_button_submit');
            $data['button_cancel'] = $this->language->get('view_button_cancel');


            $this->load->model('localisation/order_status');
            $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

            if (isset($this->error['error'])) {
                $data['error'] = $this->error['error'];
            } else {
                $data['error'] = '';
            }

            $this->response->setOutput($this->load->view('payment/espay_cancel.tpl', $data));
        } else {
            $data = array(
                'summary' => $this->language->get('view_cancel_deny')
            );

            $this->response->setOutput($this->load->view('payment/espay_cancel_deny.tpl', $data));
        }
    }


    public function refund() {
        require_once(DIR_SYSTEM . 'vendor/espay_api.php');

        $api = new ESPayApi(
            $this->config->get('espay_partner_id'),
            $this->config->get('espay_certificate_cipher'),
            $this->config->get('espay_debug')
        );

        $this->load->language('payment/espay');
        $this->load->model('payment/espay');

        $orderID = $this->request->get['order_id'];
        $espay   = $this->model_payment_espay->query($orderID);
        $orderSN = date('YmdHis', $espay['order_date']) . $orderID;

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateRefund()) {
            $orderStatus = $this->request->post['order_status'];
            $refundMoney = $this->request->post['refund_money'];
            $refundNote  = isset($this->request->post['refund_note']) ? $this->request->post['refund_note'] : '';

            $result = $api->refundOrderPay($orderSN, floatval($refundMoney), $refundNote);

            if ($result->resultCode == 'EXECUTE_SUCCESS') {
                $this->model_payment_espay->refund($orderID, floatval($refundMoney));
                $this->model_payment_espay->addOrderHistory($orderID, $orderStatus, '<strong>YJF Refund </strong>' . $refundNote . '(' . $refundMoney . ')');

                $this->response->setOutput($this->load->view('payment/espay_message.tpl', array(
                    'token' => 'success',
                    'summary' => $this->language->get('view_refund_success')
                )));
                return;
            } else {
                $this->error['error'] = $result->resultMessage;
            }
        }

        $result = $api->queryOrderPay($orderSN);
        $data   = array();

        if ($result && $result->amountLoc > $result->usableRefundMoney) {
            $data['refund_heading']      = $this->language->get('view_refund_heading');
            $data['refund_enable_money'] = $this->language->get('view_refund_enable_money');
            $data['refund_money']        = $this->language->get('view_refund_money');
            $data['refund_note']         = $this->language->get('view_refund_note');

            $data['button_submit'] = $this->language->get('view_button_submit');
            $data['button_cancel'] = $this->language->get('view_button_cancel');

            $data['currencyCode']      = $result->currencyCode;
            $data['enableRefundMoney'] = $result->amountLoc - $result->usableRefundMoney;

            $data['error_refund_money'] = isset($this->error['error_refund_money']) ? $this->error['error_refund_money'] : '';
            $data['error']              = isset($this->error['error']) ? $this->error['error'] : '';

            if (!empty($this->request->post['refund_money'])) {
                $data['input_refund_money'] = $this->request->post['refund_money'];
            } else {
                $data['input_refund_money'] = $data['enableRefundMoney'];
            }

            if (!empty($this->request->post['refund_note'])) {
                $data['input_refund_note'] = $this->request->post['refund_note'];
            } else {
                $data['input_refund_note'] = '';
            }

            if (!empty($this->request->post['order_status'])) {
                $data['input_order_status'] = $this->request->post['order_status'];
            } else {
                $data['input_order_status'] = '';
            }

            $this->load->model('localisation/order_status');
            $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            $data['order_status']   = $this->language->get('view_order_status');

            $this->response->setOutput($this->load->view('payment/espay_refund.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view('payment/espay_message.tpl', array(
                'token' => 'danger',
                'summary' => $this->language->get('view_refund_deny')
            )));
        }
    }

    public function authorize() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language('payment/espay');
            $this->load->model('payment/espay');

            $orderID = $this->request->get['order_id'];
            $espay   = $this->model_payment_espay->query($orderID);

            if ($espay['status'] == ModelPaymentEspay::STATUS_AUTHORIZING) {
                require_once(DIR_SYSTEM . 'vendor/espay_api.php');
                $api = new ESPayApi(
                    $this->config->get('espay_partner_id'),
                    $this->config->get('espay_certificate_cipher'),
                    $this->config->get('espay_debug')
                );

                $orderSN = date('YmdHis', $espay['order_date']) . $orderID;

                if (isset($this->request->post['pass'])) {
                    $result = $api->confirmProcess($orderSN);
                } else {
                    $result = $api->cancelProcess($orderSN, 'Authorize deny');
                }

                if ($result->resultCode == 'EXECUTE_SUCCESS') {
                    $this->model_payment_espay->authorized($orderID,
                        isset($this->request->post['pass']) ? 1 : 2
                    );

                    $orderStatus = isset($this->request->post['pass']) ?
                        $this->config->get('espay_status_complete') : $this->config->get('espay_status_fail');

                    $this->model_payment_espay->addOrderHistory($orderID, $orderStatus, '<strong>YJF Authorizing :</strong>' . $result->resultMessage);

                    $this->response->setOutput($this->load->view('payment/espay_message.tpl', array(
                        'token' => 'success',
                        'summary' => $this->language->get('view_authorize_success')
                    )));
                    return;
                } else {
                    $this->response->setOutput($this->load->view('payment/espay_message.tpl', array(
                        'token' => 'danger',
                        'summary' => $result->resultMessage
                    )));
                    return;
                }
            }
        }
    }

    protected function validate() {
        # check permission
        if (!$this->user->hasPermission('modify', 'payment/espay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['espay_merchant_name']) {
            $this->error['error_merchant_name'] = $this->language->get('error_merchant_name_empty');
        }

        if (!$this->request->post['espay_merchant_email']) {
            $this->error['error_merchant_email'] = $this->language->get('error_merchant_email_empty');
        } else if (!filter_var($this->request->post['espay_merchant_email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['error_merchant_email'] = $this->language->get('error_merchant_email_invalid');
        }

        if (!$this->request->post['espay_partner_id']) {
            $this->error['error_partner_id'] = $this->language->get('error_partner_id_empty');
        } else if (!is_numeric($this->request->post['espay_partner_id'])) {
            $this->error['error_partner_id'] = $this->language->get('error_partner_id_invalid');
        }

        if (!$this->request->post['espay_certificate_cipher']) {
            $this->error['error_certificate_cipher'] = $this->language->get('error_certificate_cipher_empty');
        }

        if (!$this->request->post['espay_currency'] || !array_key_exists($this->request->post['espay_currency'], $this->language->get('entry_currency_range'))) {
            $this->error['error_currency'] = $this->language->get('error_currency_invalid');
        }

        if (!$this->request->post['espay_debug'] || !array_key_exists($this->request->post['espay_debug'], $this->language->get('entry_debug_range'))) {
            $this->error['error_debug'] = $this->language->get('error_debug_invalid');
        }

        return $this->error ? false : true;
    }

    protected function validateRefund() {
        if (!$this->request->post['refund_money']) {
            $this->error['error_refund_money'] = $this->language->get('view_refund_money_empty');
        } else if (!is_numeric($this->request->post['refund_money'])) {
            $this->error['error_refund_money'] = $this->language->get('view_refund_money_invalid');
        } else if ($this->request->post['refund_money'] <= 0) {
            $this->error['error_refund_money'] = $this->language->get('view_refund_money_invalid');
        } else if ($this->request->post['refund_money'] > $this->request->post['enable_refund_money']) {
            $this->error['error_refund_money'] = $this->language->get('view_refund_money_invalid');
        }

        return $this->error ? false : true;
    }
}
<?php

    class ControllerExtensionPaymentEspay extends Controller {
        protected $error    = array();
        protected $cardType = false;

        public function index() {
            $data['button_confirm'] = $this->language->get('button_confirm');
            $data['text_loading']   = $this->language->get('text_loading');
            $data['action']         = $this->url->link('extension/payment/espay/submit', '', true);

			return $this->load->view('extension/payment/espay', $data);
        }

        public function submit() {
            # if current pay method is not espay
            if ($this->session->data['payment_method']['code'] != 'espay') {
                $this->response->redirect($this->url->link('checkout/checkout'));
            }

            $this->load->language('extension/payment/espay');

            $config = $this->config;
            $data   = array();
            $this->load->model('extension/payment/myespay');
            $this->model_extension_payment_myespay->submitForm();exit;            
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $products = $this->cart->getProducts();
                var_dump($products);exit;                                
                list($success, $msg) = $this->submitPay();
                if ($success) {
                    $this->load->model('checkout/order');
                    $this->load->model('extension/payment/espay');

                    $this->model_extension_payment_espay->createNew($msg['orderID'], $msg['total'], $msg['order_date']);
                    $this->model_checkout_order->addOrderHistory(
                        $msg['orderID'],
                        $this->config->get('espay_status_submit'),
                        $this->language->get('text_order_summary')
                    );

                    $this->response->redirect($this->url->link('checkout/success'));
                } else {
                    $this->error['error'] = $msg;
                }
            }

            $this->document->setTitle($this->language->get('text_title'));

            # full form information
            # full form information,card no
            if (isset($this->request->post['card_no'])) {
                $data['card_no']        = $this->request->post['card_no'];
                $data['card_no_format'] = preg_replace('/(\d{4})/', '$1  ', $data['card_no']);
            } else {
                $data['card_no'] = '';
            }

            if (isset($this->error['error_card_no'])) {
                $data['error_card_no'] = $this->error['error_card_no'];
            } else {
                $data['error_card_no'] = '';
            }

            # expire date
            if (isset($this->request->post['expire_year'])) {
                $data['expire_year'] = $this->request->post['expire_year'];
            } else {
                $data['expire_year'] = '';
            }
            if (isset($this->request->post['expire_month'])) {
                $data['expire_month'] = $this->request->post['expire_month'];
            } else {
                $data['expire_month'] = '';
            }
            if (isset($this->error['error_expire_date'])) {
                $data['error_expire_date'] = $this->error['error_expire_date'];
            } else {
                $data['error_expire_date'] = '';
            }

            # security code
            if (isset($this->request->post['security_code'])) {
                $data['security_code'] = $this->request->post['security_code'];
            } else {
                $data['security_code'] = '';
            }

            if (isset($this->error['error_security_code'])) {
                $data['error_security_code'] = $this->error['error_security_code'];
            } else {
                $data['error_security_code'] = '';
            }

            if (isset($this->error['error'])) {
                $data['text_error'] = $this->error['error'];
            } else {
                $data['text_error'] = false;
            }

            $data['text_title']        = $this->language->get('text_title');
            $data['text_summary']      = $this->language->get('text_summary');
            $data['entry_card_no']     = $this->language->get('entry_card_no');
            $data['entry_expire_date'] = $this->language->get('entry_expire_date');
            $data['entry_cvv']         = $this->language->get('entry_cvv');
            $data['entry_pay_now']     = $this->language->get('entry_pay_now');

            $data['entry_expire_year']  = $this->get_expires_year();
            $data['entry_expire_month'] = $this->get_expires_month();

            # layout
            $data['action'] = $this->url->link('extension/payment/espay/submit', '', true);
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            if ($config->get('espay_debug') == 'debug') {
                $data['sec_org']     = '1snn5n9w';
                $data['sec_session'] = 'xxxyyyzzz' . session_id();
            } else {
                $data['sec_org']     = 'k8vif92e';
                $data['sec_session'] = 'yijifu' . session_id();
            }

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/espay_submit.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/extension/payment/espay_submit.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/extension/payment/espay_submit.tpl', $data));
            }
        }

        protected function submitPay() {
            require_once(DIR_SYSTEM . 'vendor/espay_api.php');

            $orderID = $this->session->data['order_id'];
            $config  = $this->config;

            $this->load->model('checkout/order');
            $order    = $this->model_checkout_order->getOrder($orderID);
            $espOrder = new ESPayOrder();

            $espOrder->merchantName  = $config->get('espay_merchant_name');
            $espOrder->merchantEmail = $config->get('espay_merchant_email');

            #if ($config->get('espay_debug') == 'debug') {
                $espOrder->deviceFingerprintId = session_id();
            #} else {
            #    $espOrder->deviceFingerprintId = 'yijifu' . session_id();
            #}

            $espOrder->outOrderNo = date('YmdHis', strtotime($order['date_added'])) . $orderID;
            $espOrder->ipAddress  = $order['ip'];
            $espOrder->webSite    = $order['store_url'];

            # customer information
            $espOrder->customerEmail       = $order['email'];
            $espOrder->customerPhoneNumber = $order['telephone'];

            # if order need shipping
            # shipping address
            $espOrder->shipToPhoneNumber = $order['telephone'];
            $espOrder->shipToEmail       = $order['email'];

            if (isset($this->session->data['shipping_method'])) {
                $shippingMethod = $this->session->data['shipping_method'];

                $espOrder->shipToFirstName  = $order['shipping_firstname'];
                $espOrder->shipToLastName   = $order['shipping_lastname'];
                $espOrder->shipToCountry    = $order['shipping_iso_code_3'];
                $espOrder->shipToState      = $order['shipping_zone'];
                $espOrder->shipToCity       = $order['shipping_city'];
                $espOrder->shipToStreet1    = $order['shipping_address_1'];
                $espOrder->shipToPostalCode = $order['shipping_postcode'];

            } else {
                $shippingMethod = array('cost' => 0, 'title' => 'None');

                $espOrder->shipToFirstName  = $order['payment_firstname'];
                $espOrder->shipToLastName   = $order['payment_lastname'];
                $espOrder->shipToCountry    = $order['payment_iso_code_3'];
                $espOrder->shipToState      = $order['payment_zone'];
                $espOrder->shipToCity       = $order['payment_city'];
                $espOrder->shipToStreet1    = $order['payment_address_1'];
                $espOrder->shipToPostalCode = $order['payment_address_1'];
            }

            # bill address
            $espOrder->billToFirstName   = $order['payment_firstname'];
            $espOrder->billToLastName    = $order['payment_lastname'];
            $espOrder->billToPhoneNumber = $order['telephone'];
            $espOrder->billToEmail       = $order['email'];

            $espOrder->billToCountry    = $order['payment_iso_code_3'];
            $espOrder->billToState      = $order['payment_zone'];
            $espOrder->billToCity       = $order['payment_city'];
            $espOrder->billToStreet1    = $order['payment_address_1'];
            $espOrder->billToPostalCode = $order['payment_postcode'];

            # full card information
            $espOrder->cardHolderFirstName = $order['payment_firstname'];
            $espOrder->cardHolderLastName  = $order['payment_lastname'];

            $espOrder->cardType       = $this->cardType;
            $espOrder->orderType      = 'MOTO_EDC';
            $espOrder->cardNo         = $this->request->post['card_no'];
            $espOrder->cvv            = $this->request->post['security_code'];
            $espOrder->expirationDate = $this->request->post['expire_year'] . $this->request->post['expire_month'];

            $espOrder->amountLoc     = $order['total'];
            $espOrder->logisticsFee  = $shippingMethod['cost'];
            $espOrder->logisticsMode = $shippingMethod['title'];
            $espOrder->currencyCode  = $config->get('espay_currency');

            $products = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$orderID . "'");
            $espItems = array();

            foreach ($products->rows as $row) {
                $goods = new ESPayGoods();

                $goods->goodsNumber          = $row['product_id'];
                $goods->goodsName            = $row['name'];
                $goods->goodsCount           = $row['quantity'];
                $goods->itemSharpProductCode = $row['model'];
                $goods->itemSharpUnitPrice   = $row['price'];

                array_push($espItems, $goods);
            }

            #       $merchantName  = $this->model_setting_setting->getSetting('espay_merchant_name');
            #       $merchantEmail = $this->model_setting_setting->getSetting('espay_merchant_email');
            #       $partnerID     = $this->model_setting_setting->getSetting('espay_partner_id');
            #       $secretKey     = $this->model_setting_setting->getSetting('espay_certificate_cipher');
            #       $currency      = $this->model_setting_setting->getSetting('espay_currency');
            #       $debug         = $this->model_setting_setting->getSetting('espay_debug');

            $api = new ESPayApi(
                $config->get('espay_partner_id'),
                $config->get('espay_certificate_cipher'),
                $config->get('espay_debug') == 'debug'
            );

            $url = $config->get('config_url') . 'catalog/controller/extension/payment/espay_receive.php';

            $result = $api->submitOrderPay($espOrder, $espItems, $url, $url);

            if (!$result->success) {
                return array(false, $result->resultMessage, false);
            }

            return array(true, array('orderID' => $orderID, 'total' => $order['total'], 'order_date' => $order['date_added']));
        }


        protected function validate() {
            # check card
            if (!$this->request->post['card_no']) {
                $this->error['error_card_no'] = $this->language->get('error_card_no_empty');
            } else {
                $cardNo = $this->request->post['card_no'];
                if (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $cardNo)) {
                    $this->cardType = 'Visa';
                } else if (preg_match('/^5[1-5][0-9]{14}$/', $cardNo)) {
                    $this->cardType = 'MasterCard';
                } else if (preg_match('/^(35(28|29|[3-8][0-9])[0-9]{12}|2131[0-9]{11}|1800[0-9]{11})$/', $cardNo)) {
                    $this->cardType = 'JCB';
                } else {
                    $this->error['error_card_no'] = $this->language->get('error_card_no_invalid');
                }
            }

            if (!$this->request->post['expire_year'] || !$this->request->post['expire_month']) {
                $this->error['error_expire_date'] = $this->language->get('error_expire_date_empty');
            } else {
                if ($this->request->post['expire_year'] == date('y')) {
                    if ($this->request->post['expire_month'] <= date('m')) {
                        $this->error['error_expire_date'] = $this->language->get('error_expire_date_invalid');
                    }
                }
            }

            if (!$this->request->post['security_code']) {
                $this->error['error_security_code'] = $this->language->get('error_card_cvv_empty');
            } else if (!is_numeric($this->request->post['security_code'])) {
                $this->error['error_security_code'] = $this->language->get('error_card_cvv_invalid');
            }

            return empty($this->error);
        }

        protected function get_expires_month() {
            $expires_month = array();

            for ($i = 1; $i < 13; $i++) {
                $expires_month[sprintf('%02d', $i)] = strftime('%B - (%m)', mktime(0, 0, 0, $i, 1, 2000));
            }

            return $expires_month;
        }

        protected function get_expires_year() {
            $expires_year = array();
            $today        = getdate();

            for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
                $expires_year[strftime('%y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
            }

            return $expires_year;
        }
    }
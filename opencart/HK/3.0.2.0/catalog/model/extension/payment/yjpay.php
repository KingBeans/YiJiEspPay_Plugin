<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/11/21
 * Time: 16:50
 */

class ModelExtensionPaymentYjpay extends Model
{

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/yjpay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_yjpay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('payment_yjpay_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'yjpay',
                'title'      => "<img src='/image/payment/yjpay/logo.jpg' />",//"<b>".$this->language->get('heading_title').
                'terms'      => '',
                'sort_order' => $this->config->get('payment_yjpay_sort_order')
            );
        }

        return $method_data;
    }

    /**
     * @param array $data
     * @param string $secretKey
     * @return array
     */
    public function getSign(array $data , $secretKey = ''){

        foreach ($data as $key => $value){
            if(empty($value)){
                unset($data[$key]);
            }
        }

        ksort($data);

        $waitString = '';

        foreach ($data as $key => $value){
            $waitString .= '&'.$key.'='.$value;
        }

        $waitString = trim($waitString,'&').$secretKey;

        $data['sign'] = md5($waitString);

        return $data;

    }

    public function changeOrderStatus($order_id,$status_id,$comment = '', $notify = false){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if ($order_info) {
            $this->model_checkout_order->addOrderHistory($order_id,$status_id,$comment,$notify);
        }
    }

}
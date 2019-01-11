<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/22
 * Time: 16:14
 */
class ModelExtensionPaymentYjpay extends Model
{
    public function getMethod($address, $total){
        $this->load->language('extension/payment/yjpay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('yjpay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('yjpay_geo_zone')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'yjpay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('yjpay_sort_order')
            );
        }

        return $method_data;
    }

}
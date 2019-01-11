<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/31
 * Time: 16:31
 */
class ModelPaymentYjpay extends Model{
    public function getMethod($address, $total) {
        $this->load->language('payment/yjpay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('yjpay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('yjpay_status')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        if($status){
            return array(
                'code' => 'yjpay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => (int)$this->config->get('yjpay_sort_order')
            );
        }else{
            return [];
        }
    }
}
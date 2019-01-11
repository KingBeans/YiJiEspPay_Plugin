<?php

class ModelExtensionPaymentEspay extends Model {

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/espay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('espay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('espay_geo_zone')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'espay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('espay_sort_order')
            );
        }

        return $method_data;
    }

    # easy status
    const STATUS_NEW         = 1;
    const STATUS_AUTHORIZING = 2;
    const STATUS_AUTHORIZED  = 3;
    const STATUS_PAYED       = 4;
    const STATUS_REFUND      = 5;
    const STATUS_CANCEL      = 6;

    const NEED_AUTHORIZE = 1;

    const INSERT_NEW_SQL = <<<EOF
        INSERT INTO :prefixespay(`order_id`,`status`,`order_fee`,`currency`,`order_date`,`note`)
                    VALUES(:order_id,:status,:order_fee,':currency',:order_date,':note')
EOF;

    public function createNew($orderID, $orderFee, $currency, $date = false, $note = '') {
        if ($date && is_string($date)) {
            $date = strtotime($date);
        } else if ($date == false) {
            $date = time();
        }

        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':order_fee', ':order_date', ':note', ':currency'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_NEW, $orderFee, $date, $note, $currency
        ), self::INSERT_NEW_SQL));
    }

}
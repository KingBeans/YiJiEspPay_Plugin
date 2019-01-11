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
//                'title'      => "<img src='/image/payment/yjpay/logo.jpg' />",
                'title'      => $this->language->get('heading_title'),
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
            $waitString .= $key.'='.$value.'&';
        }

        if($data['signType']==="MD5"){
            $waitString = trim($waitString,'&').$secretKey;
            $data['sign'] = md5($waitString);
        }elseif ($data['signType']==="RSA"){
            $signSrc = trim(substr($waitString, 0, -1));
            $pfxPath = __DIR__.'/../../../../'.$data['partnerId'].'.pfx';
            $keyPass = $this->config->get('payment_yjpay_secretKey');
            $pkcs12 = file_get_contents($pfxPath);
            if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
                $privateKey = $certs['pkey'];
                $signedMsg = "";
                if (openssl_sign($signSrc, $signedMsg, $privateKey)) {
                    $data['sign'] = base64_encode($signedMsg);
                } else {
                    return '加密失败';
                }
            } else {
                return '文件解析失败';
            }
        }else{
            return "不支持除MD5 和 RSA 以外的加密方式";
        }

        return $data;

    }

    public function changeOrderStatus($order_id,$status_id,$comment = '', $notify = false){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if ($order_info) {
            $this->model_checkout_order->addOrderHistory($order_id,$status_id,$comment,$notify);
        }
    }

    public function signPublicKey($data){
        if ($this->config->get('payment_yjpay_debug')){
            $public_key_path =  __DIR__.'/../../../../yjf-cert-2048.pem';
        }else{
            $public_key_path =  __DIR__.'/../../../../yjf-online-2048.pem';
        }
        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));
        openssl_public_encrypt(str_pad($data, 256, "\0", STR_PAD_LEFT), $encryptedData, $public_key, OPENSSL_NO_PADDING);
        return base64_encode($encryptedData);
    }

}
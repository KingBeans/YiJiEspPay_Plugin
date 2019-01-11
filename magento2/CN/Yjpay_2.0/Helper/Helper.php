<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/10/18
 * Time: 17:13
 */

namespace Magento\Yjpay\Helper;

use Magento\Framework\App\Helper\Context;
// use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;

class Helper extends AbstractHelper
{

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    public function getSign(array $data,$serc,$sandbox){

        foreach ($data as $k => $v){
            if( !isset($v) ){
                unset($data[$k]);
            }
        }

        ksort($data);

        $string = '';

        foreach ( $data as $key => $value ){
            if ($key === 'sign'){
                continue;
            }
            $string .= $key.'='.$value.'&';
        }

//        $string = trim($string,'&').$serc;
        $verifyResult = false;
        if ($data['signType'] === 'MD5'){
            $string = trim($string,'&').$serc;
            $sign = md5($string);
            if ($sign === $data['sign']){
                $verifyResult = true;
            }
        }elseif ($data['signType'] === 'RSA'){
            $waitingForSign = trim(substr($string, 0, -1));
            $signValue = base64_decode($data['sign']);
            if (!$sandbox){//线上环境
                $cerPath = __DIR__.'/../易极付公钥网关证书.cer';
            }else{
                $cerPath = __DIR__.'/../yiji.online.cer';
            }

            $certificateCAcerContent = file_get_contents($cerPath);
            $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
                .chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL)
                .'-----END CERTIFICATE-----'.PHP_EOL;
            $pubkeyid = openssl_get_publickey($certificateCApemContent);
            if($pubkeyid){
                // state whether signature is okay or not
                $verifyResult = openssl_verify($waitingForSign, $signValue, $pubkeyid);
                openssl_free_key($pubkeyid);
            }
        }
        file_put_contents(__DIR__.'/../logs/'.$data['merchOrderNo'].'.log','SignOrder: '.$string."\n\n",FILE_APPEND);

        return $verifyResult;

    }

}
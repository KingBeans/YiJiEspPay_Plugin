<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/10/10
 * Time: 15:04
 */

namespace Magento\Yjpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class YjpayConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = "yjpay";

    protected $method;

    public function __construct(PaymentHelper $paymentHelper){
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    public function getConfig(){
        return $this->method->isAvailable() ? [
            'payment' => [
                'yjpay' => [
                    'redirectUrl' => 'yjpay/payment/checkout',
                ]
            ]
        ] : [];
    }


}
<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Yjpay\Model;



/**
 * Pay In Store payment method model
 */
class Yjpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'yjpay';
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_isOffline = false;
    protected $_canRefund = true;
    protected $_isInitializeNeeded = false;
    protected $_canUseCheckout = true;

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function getYjpayData($path){
        return $this->scopeConfig->getValue('payment/yjpay/'.$path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUserId(){
        return $this->scopeConfig->getValue('payment/yjpay/partnerId',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSecretKey(){
        return $this->scopeConfig->getValue('payment/yjpay/secretKey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}

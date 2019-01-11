<?php
class YiJi_Yjpay_Model_Source_Transport
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'https', 'label' => Mage::helper('yjpay')->__('https')),
            array('value' => 'http', 'label' => Mage::helper('yjpay')->__('http')),
        );
    }
}
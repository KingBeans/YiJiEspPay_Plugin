<?php

class YiJi_Yjpay_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => Mage::helper('yjpay')->__('English')),
            array('value' => 'FR', 'label' => Mage::helper('yjpay')->__('French')),
            array('value' => 'DE', 'label' => Mage::helper('yjpay')->__('German')),
            array('value' => 'IT', 'label' => Mage::helper('yjpay')->__('Italian')),
            array('value' => 'ES', 'label' => Mage::helper('yjpay')->__('Spain')),
            array('value' => 'NL', 'label' => Mage::helper('yjpay')->__('Dutch')),
        );
    }
}




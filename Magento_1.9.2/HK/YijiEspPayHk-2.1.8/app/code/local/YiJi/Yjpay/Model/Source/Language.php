<?php

class YiJi_Yjpay_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'en', 'label' => Mage::helper('yjpay')->__('English')),
            array('value' => 'jp', 'label' => Mage::helper('yjpay')->__('日本の')),
            array('value' => 'de', 'label' => Mage::helper('yjpay')->__('Deutsch')),
            array('value' => 'es', 'label' => Mage::helper('yjpay')->__('El español')),
            //array('value' => 'ES', 'label' => Mage::helper('yjpay')->__('Spain')),
            array('value' => 'fr', 'label' => Mage::helper('yjpay')->__('Français')),
        );
    }
}





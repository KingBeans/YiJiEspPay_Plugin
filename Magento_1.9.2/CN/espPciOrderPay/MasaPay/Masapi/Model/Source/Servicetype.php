<?php

class MasaPay_Masapi_Model_Source_Servicetype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'physical', 'label' => Mage::helper('masapi')->__('Physical Products')),
            array('value' => 'virtual', 'label' => Mage::helper('masapi')->__('Virtual Products')),
        );
    }
}

<?php

class MasaPay_Masapi_Model_CctypeConfigOptions
{
    public function toOptionArray()
    {
        return array(
        		array('value' => 'VI', 'label' => Mage::helper('paygate')->__('Visa')),
        		array('value' => 'MC', 'label' => Mage::helper('paygate')->__('MasterCard')),
        		array('value' => 'JCB', 'label' => Mage::helper('paygate')->__('JCB')),
        );
    }
}




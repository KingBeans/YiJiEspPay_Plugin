<?php

class MasaPay_Masapi_Model_SignTypeConfigOptions
{
    public function toOptionArray()
    {
    	return array(
    			array('value' => 'MD5', 'label' => Mage::helper('paygate')->__('MD5')),
    			array('value' => 'SHA256', 'label' => Mage::helper('paygate')->__('SHA256')),
    	);
    }
}




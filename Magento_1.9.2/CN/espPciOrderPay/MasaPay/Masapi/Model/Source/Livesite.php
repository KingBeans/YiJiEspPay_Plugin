<?php

class MasaPay_Masapi_Model_Source_Livesite
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'open', 'label' => Mage::helper('masapi')->__('Open')),
            array('value' => 'open1', 'label' => Mage::helper('masapi')->__('Open1')),
        );
    }
}




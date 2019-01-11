<?php

class MasaPay_Masapi_Model_CdtypeConfigOptions
{
    public function toOptionArray()
    {
        return array(
        		array('value' => 'authorize', 'label' => Mage::helper('paygate')->__('Authorize Only')),
        );
    }
}


//<option value="authorize">Authorize Only</option>

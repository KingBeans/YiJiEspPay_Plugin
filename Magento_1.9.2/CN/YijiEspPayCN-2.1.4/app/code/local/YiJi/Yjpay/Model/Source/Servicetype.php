<?php
class YiJi_Yjpay_Model_Source_Servicetype
{
    public function toOptionArray()
    {
        return array(
        	
            array('value' => 'iframe', 'label' => Mage::helper('yjpay')->__('Embed')),
            //array('value' => 'redirect_i', 'label' => Mage::helper('yjpay')->__('RedirectNewpage')),
            array('value' => 'redirect', 'label' => Mage::helper('yjpay')->__('Redirect')),
           
        );
    }
}




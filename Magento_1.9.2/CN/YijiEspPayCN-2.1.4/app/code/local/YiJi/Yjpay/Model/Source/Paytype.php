<?php
class YiJi_Yjpay_Model_Source_Paytype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'CRDIT', 'label' => Mage::helper('yjpay')->__('Credit card')),  //信用卡
            array('value' => 'YANDEX', 'label' => Mage::helper('yjpay')->__('E-Bank')),	//网银方式
           
        );
    }
}




<?php

class YiJi_Yjpay_Model_Source_Ordertype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'MOTO_EDC', 'label' => Mage::helper('yjpay')->__('MOTO_EDC')),
            array('value' => 'MOTO_DCC', 'label' => Mage::helper('yjpay')->__('MOTO_DCC')),
            array('value' => 'MOTO_MCP', 'label' => Mage::helper('yjpay')->__('MOTO_MCP')),
            array('value' => 'D3_EDC', 'label' => Mage::helper('yjpay')->__('3D_EDC')),
            array('value' => 'D3_DCC', 'label' => Mage::helper('yjpay')->__('3D_DCC')),
            array('value' => 'D3_MCP', 'label' => Mage::helper('yjpay')->__('3D_MCP')),
        );
    }
}




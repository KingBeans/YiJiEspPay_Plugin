<?php
class YiJi_Yjpay_Block_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
	{
		$standard = Mage::getModel('yjpay/payment');
        $standard->setOrder($this->getOrder());
        $type = $standard->getServiceType();
        $html = '';
        if($type === "redirect"){
            $html .= "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head><body onload=\"document.dinpayForm.submit();\">You will be redirected to Yjpay in a few seconds.<form name=\"dinpayForm\" id=\"dinpayForm\" method=\"POST\" action=\"{$standard->getYjpayUrl()}\" >";
            foreach($standard->getFormFields() as $k => $v){
                $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
            }
            $html .= "</form></body></html>";
    // onLoad=\"document.dinpayForm.submit();\"
            // <script>$(function(){ $('#dinpayForm').submit() })</script>
        }
        return $html; 
    }
}
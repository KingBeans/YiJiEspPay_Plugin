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
            $html ='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
            <body onLoad="document.dinpayForm.submit();">You will be redirected to Yjpay in a few seconds.<form name="dinpayForm" id="dinpayForm" method="POST" action="'.$standard->getYjpayUrl().'" >';
            foreach($standard->getFormFields() as $k => $v){
                $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
            }
            $html .= '</form></body></html>';
 /*       }else{
            $html='<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
            <body onLoad="document.dinpayForm.submit();"><form name="dinpayForm" method="post" action="'.$standard->getYjpayUrl().'" target="submitIframe">';
            foreach($standard->getFormFields() as $k => $v){
                $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
            }
            $html .= '</form><iframe src="" width="100%" height="100%"  name="submitIframe"  frameborder=0 scrolling="auto" />payment</iframe>';
            $html .='</body></html>'; */
        }
        return $html; 
    }
}
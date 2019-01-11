<?php
class YiJi_Yjpay_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

	public function __construct()
	{
		
		$_order = $this->getOrder();

		$displayReBtn = false;
		$displayAuBtn = false;
		$_displayStr = null;
		$_strComment= null;
		$customer = Mage::getSingleton('admin/session')->getUser();
		$username = $customer->getUsername();
		
		$standard = Mage::getModel('yjpay/payment');		
		if($_order->getPayment()->getMethod()=="yjpay_payment"){
				foreach ($_order->getStatusHistoryCollection() as $statusHistory) {
					$_strComment = $statusHistory->getComment();	

					if ($_order->getStatus()=="processing" && strstr($_strComment,",trading success!")){
						$displayReBtn = true;
					}

					if($_order->getStatus()=="payment_review" && strstr($_strComment,"Authorization,Reason")){
						$displayAuBtn = true;
						$_displayStr = strstr($_strComment,"Authorization,Reason");
					}
				}
		}
		
		$clickRe = "(function($){
				 function onSuccess(data)
				    {
						alert(data.responseText);
						history.go(0);
				   	}
				 function onFailure(data)
				    {
			            alert(data.responseText);
				   	}
				if(window.confirm('Are you sure you want to perform？".$_order->getEntityId()."')){
					//var str=prompt('Please enter refund Amount','');
					var  url=document.location.href;
					url = url.substr(0,url.indexOf('index.php'));
					var ajax = new Ajax.Request(
	          			url+'index.php/yjpay/payment/refound?orderNo=".base64_encode($_order->getEntityId())."',
				       {
						    method: 'get',
						    onSuccess: onSuccess,
						    onFailure:onFailure
					    }
	 				 );
              	}else{
                 return false;
             	}
		})($)";


		$clickAu = "(function($){
				 function onSuccess(data)
				    {
						alert(data.responseText);
						history.go(0);
				   	}
				 function onFailure(data)
				    {
			            alert(data.responseText);
				   	}
				if(window.confirm('The order ".$_displayStr."  注:Confirm:授权通过,Cancle:授权取消')){
					var str=prompt('Please enter authorize reasons','agree');
					var  url=document.location.href;
					url = url.substr(0,url.indexOf('index.php'));
					var ajax = new Ajax.Request(
	          			url+'index.php/yjpay/payment/authorization?isAccept=confirm&re=str&orderNo=".base64_encode($_order->getEntityId())."',
				       {
						    method: 'get',
						    onSuccess: onSuccess,
						    onFailure:onFailure
					    }
	 				 );
              	}else{
              		var str=prompt('Please enter deauthorize reasons','agree');
				    var  url=document.location.href;
					url = url.substr(0,url.indexOf('index.php'));
              		var ajax = new Ajax.Request(
	          			url+'index.php/yjpay/payment/authorization?isAccept=cancel&re=str&orderNo=".base64_encode($_order->getEntityId())."',
				       {
						    method: 'get',
						    onSuccess: onSuccess,
						    onFailure:onFailure
					    }
	 				 );
             	}
		})($)";
		
		if ($displayReBtn){
			$this->_addButton('order_refound', array(
					'label'    => Mage::helper('sales')->__('Refund'),
					'onclick'  => $clickRe,
					'after_element_html' => '<script type="text/javascript">//js代码 </script>',
			));
		}
		if($displayAuBtn){
			$this->_addButton('order_authorization', array(
					'label'    => Mage::helper('sales')->__('Authorization'),
					'onclick'  => $clickAu,
					'after_element_html' => '<script type="text/javascript">//js代码 </script>',
			));
		}
		
		parent::__construct();
	}
	
				


//	showPop('Authorization','Please check the authorization order,the risk reason is  [".$_confiContent."]','Cancle','OK',false,function(data){
//						if(data){
//							var ajax = new Ajax.Request(
//			          			url+'index.php/yjpay/payment/authorization?action=confirm&orderNo=".base64_encode($_order->getEntityId())."',
//						       {
//								    method: 'get',
//								    onSuccess: onSuccess,
//								    onFailure:onFailure
//							    }
//			 				 );
//						}else{
//							showPop('Authorization reason','Please check the authorization order,the risk reason is  [".$_confiContent."]','Cancle','OK',true,function(data){
//								var ajax = new Ajax.Request(
//				          			url+'index.php/yjpay/payment/authorization?action=cancel&orderNo=".base64_encode($_order->getEntityId())."&re='+data+'&admin=".$username."',
//							       {
//									    method: 'get',
//									    onSuccess: onSuccess,
//									    onFailure:onFailure
//								    }
//				 				 );
//							});
//						}
//					});
//	
//	             	
//	             	function showPop(title,content,cancle,ok,hasinput,callback){
//	             		var popupHtml = '<div id=\'message-popup-window-mask\' style=\'display:block;\'></div><div id=\'message-popup-window\' class=\'message-popup\'><div class=\'message-popup-head\'>';
//						    popupHtml = popupHtml+'<h2>'+title+'</h2></div><div class=\'message-popup-content\'><div class=\'message\'><span class=\'message-icon message-notice\' style=\'background-image:url(http://widgets.magentocommerce.com/1.9.1.0/SEVERITY_NOTICE.gif);\'>Message</span>';  
//						    popupHtml = popupHtml+'<p class=\'message-text\'>'+content+'</p></div>';
//						if(hasinput){
//							popupHtml = popupHtml+'<input id=\'popupinputs\'/>';
//							popupHtml = popupHtml+'<p class=\'read-more\'><a onclick=\''+callback(docment.getElementById(\'popupinputs\').getAttribute(\'value\'))+'\'>'+ok+'</a></p>';
//						}else{
//							popupHtml = popupHtml+'<p class=\'read-more\'><a onclick=\''+callback(true)+'\'>'+ok+'</a></p>';
//							popupHtml = popupHtml+'<p class=\'read-more\'><a onclick=\''+callback(false)+'\'>'+ok+'</a></p></div></div>';
//						}
//	             	}	

}
?>
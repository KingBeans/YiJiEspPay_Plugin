<?php
class MasaPay_Masapi_MasapayController extends Mage_Core_Controller_Front_Action {
	public function createInvoice($order) {
		try {
			$savedQtys = array ();
			$invoice = Mage::getModel ( 'sales/service_order', $order )->prepareInvoice ( $savedQtys );
			Mage::register ( 'current_invoice', $invoice );
			$invoice->register ();
// 			$invoice->setEmailSent ( true );
			$invoice->getOrder ()->setCustomerNoteNotify ( true );
			$invoice->getOrder ()->setIsInProcess ( true );
			$transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
			$invoice->setState ( "2" );
			$invoice->setCanVoidFlag ( false );
			$invoice->pay ();
			$transactionSave->save ();
			$invoice->sendEmail ( true, "" );
			
			if ($invoice->getEmailSent()) {
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper ( 'paygate' )->__( 'Invoice #' . $invoice->getIncrementId() . ' is notified to customer.' ), true );
				$order->save();
			}
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			echo print_r ( $e );
		}
	}
	public function notifyAction() {
		Mage::log ( '*****************received message from masapay******************', null, "Masapay.log" );
		if ($this->getRequest ()->isPost ()) {
			$config = Mage::getStoreConfig ( 'payment/masapi' );
			$postData = $this->getRequest ()->getPost ();
			$method = 'post';
			$partner = $config ['masapay_user_id'];
			$security_code = $config ['masapay_user_key'];
			
			$signStr = "version=" . $postData ['version'] 
				. "&signType=" . $postData ['signType'] 
				. "&merchantOrderNo=" . $postData ['merchantOrderNo'] 
				. "&currencyCode=" . $postData ['currencyCode'] 
				. "&orderAmount=" . $postData ['orderAmount'] 
				. "&paidAmount=" . $postData ['paidAmount'] 
				. "&submitTime=" . $postData ['submitTime'] 
				. "&resultCode=" . $postData ['resultCode'];
						
			$signParams = $signStr; // for log
			$signStr .= "&key=" . $security_code;
			
			if ($config ['masapay_sign_type'] == 'MD5') {
				$mysign = strtoupper ( md5 ( $signStr ) );
			} else {
				$mysign = strtoupper ( hash ( "sha256", $signStr ) );
			}
			
			Mage::log ( "signStr:" . $signParams, null, "Masapay.log" );
			Mage::log ( $config ['masapay_sign_type'], null, "Masapay.log" );
			Mage::log ( "after encrypted:" . $mysign, null, "Masapay.log" );
			if ($mysign == $postData ["signMsg"]) {
				$order = Mage::getModel ( 'sales/order' );
				Mage::log ( 'merchantOrderNo is ' . $postData ['merchantOrderNo'], null, "Masapay.log" );
				$order->loadByIncrementId ( $postData ['merchantOrderNo'] );
				
				$realOrderId = $order->getRealOrderId();
				$ext1Rtn = $postData ["ext1"];
				$stateFromMasapay = $postData ["resultCode"];
				
				if ($realOrderId) {
					$ext1 = $order->getPayment()->getCcSsIssue();
					
					if($ext1 != $ext1Rtn) {
 						Mage::log ( 'Different ext1 of OrderId '. $realOrderId . ': ' . $ext1 . ' in sys, ' . $ext1Rtn . ' from masapay.', null, "Masapay.log" );
 						echo 'OK';
					} else {
						$currency_code = $postData['currencyCode'];
						$currentOrderAmount = $order->getGrandTotal();
						if(!($currency_code=='JPY' || $currency_code=='KRW'))  $currentOrderAmount = round($currentOrderAmount, 2) * 100;
						$masapayOrderAmount = intval ( $postData ["orderAmount"] );
						Mage::log ( "order amount from DB is :" . $currentOrderAmount, null, "Masapay.log" );
						Mage::log ( "order amount from masapay is :" . $masapayOrderAmount, null, "Masapay.log" );
						$currentOrderAmounts = "$currentOrderAmount";
						if ($masapayOrderAmount != $currentOrderAmounts) {
							Mage::log ( "order amount is not equal to masapay order amount", null, "Masapay.log" );
							echo 'orderAmount is Wrong';
							exit ();
						}
						$mstate = $order->getState ();
						Mage::log ( "order state from DB is " . $mstate, null, "Masapay.log" );
						
						if (Mage_Sales_Model_Order::STATE_NEW == $mstate || Mage_Sales_Model_Order::STATE_PROCESSING == $mstate || Mage_Sales_Model_Order::STATE_CLOSED == $mstate || Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW == $mstate) {
							if(Mage_Sales_Model_Order::STATE_PROCESSING!=$mstate){
								if ('10' == $stateFromMasapay) {
									if (Mage_Sales_Model_Order::STATE_PROCESSING != $mstate) {
										$order->setState ( Mage_Sales_Model_Order::STATE_PROCESSING );
										$order->setStatus ( Mage_Sales_Model_Order::STATE_PROCESSING );
										$order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper ( 'paygate' )->__ ( 'Transaction is paid successfully.' ) );
										try {
											$order->save ();
											$this->createInvoice ( $order );
											echo 'OK';
										} catch ( Exception $e ) {
											Mage::log ( 'Save Order Exception 10: ' . $e, null, "Masapay.log" );
											echo 'Save Order Exception:' . $e;
										}
									} else {
										echo 'OK';
									}
								} else if ('11' == $stateFromMasapay) {
									if (Mage_Sales_Model_Order::STATE_CLOSED != $mstate) {
										$order->setData ( 'state', Mage_Sales_Model_Order::STATE_CLOSED );
										$order->setData ( 'status', Mage_Sales_Model_Order::STATE_CLOSED );
										$order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_CLOSED, Mage::helper ( 'paygate' )->__ ( 'Mas acquiring failed(' . $postData ['errCode'] . ':' . $postData ['errMsg'] . ').' ) );
										try {
											$order->save ();
											echo 'OK';
										} catch ( Exception $e ) {
											Mage::log ( 'Save Order Exception 11:' . $e, null, "Masapay.log" );
											echo 'Save Order Exception:' . $e;
										}
									} else {
										echo 'OK';
									}
								} else if ('12' == $stateFromMasapay) {
									if (Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW != $mstate) {
										$order->setState ( Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW );
										$order->setStatus ( Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW );
										$order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage::helper ( 'paygate' )->__ ( 'Mas acquiring succeeded ,entering fraud review flow.' ) );
										try {
											$order->save ();
											echo 'OK';
										} catch ( Exception $e ) {
											Mage::log ( 'Save Order Exception 12:' . $e, null, "Masapay.log" );
											echo 'Save Order Exception 12:' . $e;
										}
									} else {
										echo 'OK';
									}
								} else { // result code is not from masapay
									Mage::log ( "result code is unexpected", null, "Masapay.log" );
									echo 'ResultCode Error:' . $stateFromMasapay;
								}
							}else{ // order status is already processing, no handling
								Mage::log("order status from masapay async notification is " . $stateFromMasapay . ", but is already processing in sys.", null,"Masapay.log" );
								echo 'OK';
							}
						} else { // order status is not 'new' or 'processing' or 'closed' or 'review'
							Mage::log ( "order status is not new or processing or closed or review", null, "Masapay.log" );
							echo 'OK';
						}
					}
					
				} else {
					$mconfig = Mage::getStoreConfig ( 'payment/masapi' );
					if ('11' == $stateFromMasapay && ! $mconfig ['save_fail_order']) {
						echo 'OK';
					} else {
						Mage::log ( "Order is not create.", null, "Masapay.log" );
						echo 'Order is not create.';
					}
				}
			} else {
				Mage::log ( "CheckSignFail:" . $signParams, null, "Masapay.log" );
				echo 'Check sign fail.';
			} 
		} else {
			Mage::log ( 'Method is not post', null, "Masapay.log" );
			echo 'Method is not post';
		}
	}

	public function tyAction(){
        $config = Mage::getStoreConfig ( 'payment/masapi' );

        $requestData = $_POST;

        if($this->checkSign($requestData,$config['secretKey'])){

            $successOrderStatus = $config['success_order_status'];
            //$processingOrderStatus = $config['processing_order_status'];
            $failOrderStatus = $config['fail_order_status'];
            $authorizingOrderStatus = $config['authorizing_order_status'];

            $sqlStatus = '';

            switch($requestData['status']){
                case 'success':
                    $sqlStatus = $successOrderStatus;
                    break;
                case 'fail':
                    $sqlStatus = $failOrderStatus;
                        break;
                case 'authorizing':
                    $sqlStatus = $authorizingOrderStatus;
                        break;
            }
            try{
                $orderNo = $requestData['merchOrderNo'];
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($orderNo);
                $order->addStatusToHistory($sqlStatus,Mage::helper('paygate')->__('Customer got successful notification  from Yijifu,trading '.$sqlStatus.'!'),true);
                $order->setState($sqlStatus, true);
                $order->save();
                echo "success";
            }catch(Exception $e){
                echo 'save has error';
            }
        }else{
            echo "sign has error!";
        }

    }

    protected function checkSign( $data ,$key){
        $sign = $data['sign'];
        unset($data['sign']);

        ksort($data);

        $waitStr = '';

        foreach ( $data as $k => $v ){
            $waitStr .= $k.'='.$v.'&';
        }
        $waitStr = trim($waitStr,'&');

        $localSign = md5($waitStr.$key);

        return $localSign == $sign;
    }

}
<?php
class YiJi_Yjpay_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;
	protected $_gateway="https://openapiglobal.yiji.com?";
		
    /**
     *  Get order
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

  
    /**
     * 构造参数
     */
     public function redirectAction()
    {

            $session = Mage::getSingleton('checkout/session');
            $order = $this->getOrder();
            if (!$order->getId())
            {
                $this->norouteAction();
                return;
            }
            $order->addStatusToHistory(
                $order->getStatus(),
                Mage::helper('yjpay')->__('Customer was redirected to YijiPay.')
            );
            $order->save();
           
            $standard = Mage::getModel('yjpay/payment');
            $type = $standard->getServiceType();

            if($type === "redirect"){
                 $temp1 = $this->getResponse()
                ->setBody($this->getLayout()
                    ->createBlock('yjpay/redirect')
                    ->setOrder($order)
                    ->toHtml());
                //Mage::log("测试1：".$temp1,null,"log.log");
                $session->unsQuoteId();
            }elseif($type === "iframe"){
                $this->_redirect('yjpay/payment/success');
            }

    }
    

     /**
     * 易极付异步通知
     */
    public function notifyAction()
    {
    	// echo "notify";
        // file_put_contents("E:/log/notify_info/info0.log","notify start",FILE_APPEND);
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            // $postJson = json_encode($postData,true);
            // file_put_contents("E:/log/notify_info/info1.log", "postJson:".$postJson,FILE_APPEND);
            $method = 'post';
        } else if ($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        } else
        {
            return;
        }

        // $postData = array(
        //         'orderNo' => '20151025874081445761309',
        //         'merchOrderNo' => '100000072',
        //         'notifyTime' => '2016-10-17 16:19:27',
        //         'resultCode' => 'EXECUTE_SUCCESS',
        //         'sign' => '4254a8eb7b5201d8134361ea00b41642',
        //         'resultMessage' => '成功',
        //         'outOrderNo' => '2015102587408',
        //         'version' => '1.0',
        //         'protocol' => 'httpPost',
        //         'service' => 'espOrderPay',
        //         'status' => 'authorizing',
        //         // 'status' => 'success',
        //         'signType' => 'MD5',
        //         'partnerId' => '20140526020000027815',
        //         'description' => 'authoriziing order infos',
        //     );

		$yjpay = Mage::getModel('yjpay/payment');
		$security_code=$yjpay->getConfigData('secretKey');
		$mysign="";				
		$post           = $yjpay->para_filter($postData);//过滤sign			
		$sort_post      = $yjpay->arg_sort($post);//排序		
		$arg="";
		while (list ($key, $val) = each ($sort_post)) {
			$arg.=$key."=".$val."&";
		}

		$prestr="";
		$prestr = substr($arg,0,count($arg)-2);  
//		$mysign = $yjpay->sign($prestr,'RSA');
        $is_doVerify = $yjpay->doVerify($postData);
		Mage::log("异步请求结果数据：".$postData, null,'log.log');
		$service = $postData['service'];

        if ( $is_doVerify)  {
            
            if ($service === 'espOrderPay' OR $service === 'espOrderJudgment' OR $service === 'espRefund') {
                $_orderEntityId = $postData["merchOrderNo"];
                $order = Mage::getModel('sales/order');
                //$order->load($_orderEntityId);
                $order->loadByIncrementId($_orderEntityId);
                $cStatus = $order->getStatus();                
                if($cStatus == "processing"){
                    echo "success";
                    exit;
                }
//                Mage::log("异步通知-status：".$postData["merchOrderNo"].$postData['status'], null,'log.log');
                if($postData['status']=="SUCCESS" || $postData['status'] == "success") { //交易成功
                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_PROCESSING,//交易成功，订单状态 processing处理中
                        Mage::helper('yjpay')->__('Customer got successful notification  from Yijifu,trading success!'),true);
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true); //->save();
                }elseif($postData['status']=="AUTHORIZING" || $postData['status'] == "authorizing"){        //交易授权, 订单状态 payment_review
                    $reason =  $postData['authorizingInfo']?$postData['authorizingInfo']:"Unknown,Please contact the Yijifu.";
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                        Mage::helper('yjpay')->__('Waiting for Authorization,Reason:'.$reason),true);
                    $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
                }elseif($postData['status']=="PROCESSING" || $postData['status'] == "processing"){ //交易处理中,暂不明确处理
                    echo "success";
                    return;
                }elseif($postData['status']=="FAILURE" || $postData['status']=="fail" ){ ////交易失败，pending->>pending_payment
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                            Mage::helper('yjpay')->__('Customer got payment failure notification from Yijifu,trading failed!'),true);
                    $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                }
                try{
                    $order->save();
                    echo "success";
                } catch(Exception $e){
                }
            }else {
                echo "otherService";
            }
		}
    }


    /**
	*預授权
	*/
	public function authorizationAction(){
		$_orderNo = $_GET['orderNo'];
		$action   = $_GET['isAccept'];
		$order_mode = Mage::getModel('sales/order');
        $order = $order_mode->load(base64_decode($_orderNo));

		$outOrderNo = $order->getEntityId();
    	$orderNo    = $order->getRealOrderId();
    	
    	$standard = Mage::getModel('yjpay/payment');
    	$_gatWay  = $standard->getYjpayUrl();
        $_notifyUrl = $standard->getYjpayNotify();
        $cancelReason = $_GET['re']?$_GET['re']:'agree';
        $isAccept = null;
    	if ($action === "confirm"){
            $isAccept = 'true';
        }elseif($action === "cancel"){
            $isAccept = 'false';
        }
        $para_array = Array(
            'service'          => 'espOrderJudgment',   //服务代码
            'partnerId'        => $standard->getConfigData('merchant_partnerId'), //商户ID
            'merchOrderNo'     => $orderNo,  //订单号
            'orderNo'          => date("YmdHis",time()).mt_rand(10,99).$outOrderNo, 
            'notifyUrl'        => $_notifyUrl,
            'isAccept'         => $isAccept,
            'resolveReason'    => $cancelReason,
            );
                 
        $para_array['sign']   = $standard->signParaArray($para_array);            
        $_api_result = $this->sendPost($_gatWay,$para_array);
        // file_put_contents('E:/log/cqmagento/para_array.log', json_encode($para_array),FILE_APPEND);
        // file_put_contents('E:/log/cqmagento/authorization.log', $_api_result,FILE_APPEND);
        //Mage::log("授权结果:".$_api_result,null,'debug1.log');
        if($_api_result){
            $arr = json_decode($_api_result,true);
            // if($arr['resultCode']=='EXECUTE_SUCCESS'){
            if($arr['status']=='success'){
                if($isAccept=='true'){
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING,Mage::helper('yjpay')->__('Pre authorization operation success,trading success!'),true);
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $order->save(); 
                }else{
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED,Mage::helper('yjpay')->__('Pre authorization operation success,trading failed!'),true);
                    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED);
                    $order->save();  
                }
                echo "Authorization operation Success!";
                return ;
            }else{
                echo "Authorization errors !".$arr['resultMessage'];
                return ;
            }               
        }else{
            echo "Authorization failure!";
            return;
        }
	}

    /**
     * 退款
     */
    public function refoundAction()
    {
    	$_orderNo = $_GET['orderNo'];
        $reReason = $_GET['re']?$_GET['re']:'agree';
    	$order_mode = Mage::getModel('sales/order');
    	$order = $order_mode->load(base64_decode($_orderNo));

        $outOrderNo = $order->getEntityId();
        $orderNo    = $order->getRealOrderId();
    	$standard = Mage::getModel('yjpay/payment');
    	$_gatWay  = $standard->getYjpayUrl();
        $_notifyUrl = $standard->getYjpayNotify();
        $refundAmount = $standard->chageBaseConvert2SessionConvert($order->getPayment()->getBaseAmountOrdered());
        $para_array = Array(
                // 'service'          =>'cardAcquiringRefund',   //服务代码
                'service'          =>'espRefund',   //服务代码
                'partnerId'        =>$standard->getConfigData('merchant_partnerId'), //商户ID
                'orderNo'          =>date("YmdHis",time()).mt_rand(0,9).$outOrderNo,//订单ID号
                'merchOrderNo'       =>$orderNo,  //订单号
                'notifyUrl' => $_notifyUrl,
                'refundAmount'      =>$refundAmount, //订单金额
                'refundReason'  =>$reReason,
                'memo' =>'refound');
        $_sign   = $standard->signParaArray($para_array);//签名  
        $para_array['sign'] = $_sign;         
        $arg = "";
        while (list ($key, $val) = each ($para_array)) {
            $arg.=$key."=".$val."&";
        }
        $arg.="sign=".$_sign;
       // Mage::log("退款申请参数:".$arg,null,'debug1.log');
        $_api_result = $this->sendPost($_gatWay,$para_array);
        $arr = json_decode($_api_result,true);
        if($arr){            
            if($arr['resultCode']=='EXECUTE_SUCCESS'){
                $order->addStatusToHistory($order->getStatus(),
                        Mage::helper('yjpay')->__('Refund operation success,Waiting for Asynchronous Notification'),true);
                $order->setState(Mage_Sales_Model_Order::STATE_CLOSED);
                $order->save();
                echo "Refund operation success!";
                return ;
            }else{
                $order->addStatusToHistory($order->getStatus(),
                        Mage::helper('yjpay')->__('Customer refund failure from Yijifu'),true);
                echo "Refund operation Error!".$arr['resultMessage'];
                return ;
            }       
        }else{
            echo "Refund operation failure!";
                return ;
        }
        
    }
    
    
	/**
	 * 发送请求 
	 */
	function sendPost($url,$params=false)
	{
		//Mage::log("请求".$url."内容".$params,null,"log.log");
		$ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
		$ch = curl_init();
		 if ($ssl){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
        if(is_array($params)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
		$result = curl_exec($ch);
		curl_close($ch);
		//Mage::log("响应结果：".$result,null,"log.log");
		return $result;
	}
	
	public function successAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        //Mage::dispatchEvent('checkout_payment_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }


        public function returnAction()
    {   
        $standard = Mage::getModel('yjpay/payment');    
        $session = Mage::getSingleton('checkout/session');
        //暂不验签
        $orderNo = $this->getOrder()->getRealOrderId();
        $resultCode  = $_GET['resultCode'];
        $success     = $_GET['success'];
        $status      = $_GET['status'];
        $description = $_GET['description'];

        file_put_contents('E:/log/cqmagento/return.log', json_encode($_GET,true),FILE_APPEND);

        $showTitle = 'your order has been received.';
        $showSubTitle = 'thank you for your purchase!';
        $showMesaage = 'We are processing your order and you will soon receive an email with details of the order. Once the order has shipped you will receive another email with a link to track its progress.';
        $session->clear();
        //echo "<br>".$standard->printLog("测试支付结果回跳页面内容：",$session);
        if($status === 'fail'){
            if($style === "redirect"){
                $this->_redirect('checkout/onepage/failure');
            }else{
                $$showTitle = '';
                $showSubTitle = 'an error occurred in the process of payment';
                $showMesaage ='you order # '.$orderNo.' ,<br>description:'.$description.
                '<br>Click <a href="'.Mage::getUrl().'index.php/checkout/cart/">here</a> to continue shopping.';
            }      
        }
        
        if($style === "redirect"){
            $this->_redirect('checkout/onepage/success');
        }else{
            $html .= '<div style="text-align: center;">
                     <div style="font-family:Raleway, Helvetica Neue,Verdana, Arial, sans-serif;margin: 0;    margin-bottom: 0.5em;    color: #636363;  font-size: 12px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">
                        <h1 data-role="page-title">'.$showTitle.'</h1>
                    </div>
                    <h2 style="
    margin: 0;    margin-bottom: 0.5em;    color: #636363;    font-family: Raleway, Helvetica Neue,Verdana, Arial, sans-serif;    font-size: 24px;    font-weight: 400;    font-style: normal;    line-height: 1.2;    text-rendering: optimizeLegibility;    text-transform: uppercase;">'.$showSubTitle.'</h2>
                    <p style="font-family: Helvetica Neue, Verdana, Arial, sans-serif;    color: #636363;    font-size: 14px;    line-height: 1.5;">'.$showMesaage.'</p>
                <div>
                <div class="buttons-set">
                    <button type="button" class="button" style="
    background: #3399cc;    display: inline-block;    padding: 7px 15px;    border: 0;    color: #FFFFFF;    font-size: 13px;    font-weight: normal;    font-family: "Raleway", "Helvetica Neue", Verdana, Arial, sans-serif;    line-height: 19px;    text-align: center;    text-transform: uppercase;    vertical-align: middle;    white-space: nowrap;
" title="'.Mage::helper('core')->quoteEscape($this->__('Continue Shopping')).'" onclick="top.document.location.href=\''.Mage::getUrl().'\'"><span><span>'.$this->__('Continue Shopping') .'</span></span></button>
                </div>';
        echo $html;
        }
        
        
    }
    
	//Test Function
	
	public  function testAction(){
		// $this->loadLayout();
        // $this->renderLayout();
		$standard = Mage::getModel('yjpay/payment');
		$session = Mage::getSingleton('checkout/session');
        echo "<pre>";
		print_r($session);     
	}
	
	public  function testSendEmailAction(){
		echo "测试发送Email:".$this->sendErrorLogByEmail("这是订单信息","这是错误信息内容");
	}

    public  function testRefoundAction(){
        echo "测试退款:";
        $this->_redirect('yjpay/payment/refound');
    }

    public  function testAuthAction(){
        echo "测试退款:";
        $this->_redirect('yjpay/payment/authorization');
    }
}

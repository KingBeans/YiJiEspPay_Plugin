<?php


class YiJi_Yjpay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  		  = 'yjpay_payment';
    protected $_formBlockType = 'yjpay/form_yjpay';
    protected $_infoBlockType = 'yjpay/info_yjpay';
	protected $_gateway       = "https://openapiglobal.yiji.com";
	protected $_serviceType   = "iframe";

    // Yjpay return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';

    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getYjpayUrl()
    {
        $url = $this->getConfigData('pay_gateway');
        if(empty($url)){
        	$url = $this->_gateway;
        }
        return $url;
    }
	/**
     *  Returns Service Type
     *
     *  @return	  string Service Type
     */
    public function getServiceType()
    {
        $type = $this->getConfigData('servceType');
        if(empty($type)){
        	$type = $this->_serviceType;
        }
        return $type;
    }
    /**
     *  Returns Notify Url
     *
     *  @return	  Notify Url
     */
    public function getYjpayNotify()
    {
        $url = Mage::getUrl('yjpay/payment/notify/', array('_secure' => true));
        if(empty($url)){
        	$url = 'https://'.$_SERVER['HTTP_HOST'].'/index.php/yjpay/payment/notify/';
        }
        return $url;
    }
	/**
     *  Return back URL
     *
     *  @return	  string URL
     */
	protected function getReturnURL()
	{
		return Mage::getUrl('yjpay/payment/return', array('_secure' => true));
	}
	/**
	 *  Return URL for Yjpay notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('yjpay/payment/notify/', array('_secure' => true));
	}

	protected function getModifyUrl($init_url)
	{
		$pos = strrpos($init_url,"?");
		if($pos) {
			$modify_url = substr($init_url,0,$pos);
			return $modify_url;
		} else {
			return $init_url;
		}
	}

	/**
	 *  Return URL for Yjpay success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     *  Return URL for Yjpay failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('yjpay/payment/error', array('_secure' => true));
    }



    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('yjpay/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('yjpay/payment/redirect');
    }

    
	/**
     *  组装易极付PCI订单信息支付所需要的参数
     *
     *  @return	  array Array of hidden form fields
     */
    public function getFormFields()
    {
    	$session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();
		$_order = Mage::getModel('sales/order');
		$_order->loadByIncrementId($session->getLastRealOrderId());
		$sales_order = $_order;
		$order = $_order;
        //$sales_order = Mage::getModel('sales/order')->load($order->getEntityId());		//获得更详细信息
		$converted_final_price=$this->chageBaseConvert2SessionConvert($order->getGrandTotal());			//币种金额转换
		$billingaddress     = $order->getBillingAddress();
		$shippingaddress    = $order->getShippingAddress();
		$isvirtual = FALSE;
		if(empty($shippingaddress))
			$isvirtual = ture;
		$payment = $order->getPayment();		//得到支付信息
		$_outOrderNo = $order->getRealOrderId();			//订单号

		$_timezone=date_default_timezone_get();
		date_default_timezone_set('PRC'); //('Africa/Accra');//		 
		$_orderNos = date('YmdHis').mt_rand(10,99).$order->getEntityId();			//订单ID
		date_default_timezone_set($_timezone);
		$_orderNolengths = strlen($_orderNos);


		$_billToState = $billingaddress->getData('region')?$billingaddress->getData('region'):$billingaddress->getData('city');
		$_shipToState = $isvirtual?'CA':($shippingaddress->getData('region')?$shippingaddress->getData('region'):$shippingaddress->getData('city'));
		
		$attachDetails = array (
			'ipAddress' => $this->get_real_ip(),	//IP地址
			'billtoCountry' => $billingaddress->getData('country_id'),//账单地址国家
			'billtoState' => $_billToState,//账单地址州
			'billtoCity' => $billingaddress->getData('city'), //账单地址城市
			'billtoPostalcode' => $billingaddress->getData('postcode'),//账单邮编
			'billtoEmail' => $billingaddress->getData('email'),//账单地址用户邮箱
			
			'billtoFirstname' => $billingaddress->getData('firstname'),//账单用户姓
			'billtoLastname' => $billingaddress->getData('lastname'),//账单用户名
			'billtoPhonenumber' => $billingaddress->getData('telephone'),//账单电话
			'billtoStreet' => $billingaddress->getData('street'),	//账单街道
			'shiptoCity' => $isvirtual?'Irvine':$shippingaddress->getData('city'),//收货地址城市
			'shiptoCountry' => $isvirtual?'US':$shippingaddress->getData('country_id'),//收货地址国家
			'shiptoFirstname' => $isvirtual?'Xu':$shippingaddress->getData('firstname'),//收货人名
			'shiptoLastname' => $isvirtual?'Qi':$shippingaddress->getData('lastname'),//收货人姓
			'shiptoEmail' => $isvirtual?'34930948@qq.com':($shippingaddress->getData('email')?$shippingaddress->getData('email'):$billingAddr['cc_emali']),//收货人邮箱
			'shiptoPhonenumber' => $isvirtual?'9499551380':$shippingaddress->getData('telephone'),//收货人电话
			'shiptoPostalcode' => $isvirtual?'92618':$shippingaddress->getData('postcode'),//收货人地址邮编
			'shiptoState' => $isvirtual?'CA':$_shipToState,//收货人州
			'shiptoStreet' => $isvirtual?'16215 Alton Pkwy, Irvine, California, US':$shippingaddress->getData('street'),//收货人街道地址1
			'logisticsFee' => $isvirtual?'0.0':$this->chageBaseConvert2SessionConvert($payment->getBaseShippingAmount()),//	物流费	m	是	默认为0
			'logisticsMode' => $isvirtual?'EMS':$sales_order->getShippingDescription(),//	物流方式	S	是
			'customerEmail' => $billingaddress->getData('email'),//$payment->getCcEmali(),//		客户email
			'customerPhonenumber' => $isvirtual?$billingaddress->getData('telephone'):$shippingaddress->getData('telephone'),//$payment->getPhoneNumber(),//	客户手机号    
			'merchantEmail' => $this->getConfigData('merchant_email'),  //商户邮箱(收款方)	后台配置
			'merchantName' => $this->getConfigData('merchant_name'), //商户名
			'cardType'	=>'Visa',
			'addressLine1' => '',	//卡地址1
			'addressLine2' => ''	//卡地址2
		);


		$attachDetails	 = json_encode($attachDetails);

		session_start();
		$session_id = session_id();

		// 处理
		$init_ret_url = $this->getReturnURL();
		$ret_url = $this->getModifyUrl($init_ret_url);

		$init_noty_url = $this->getNotifyURL();
		$noty_url = $this->getModifyUrl($init_noty_url);

		// file_put_contents("E:/log/newurl.log", "returnUrl:".$ret_url."notifyUrl:".$noty_url);
		$parameter = array(
		//基本参数
				'service'          	=>'espOrderPay',   //服务代码
				'partnerId'        	=>$this->getConfigData('merchant_partnerId'), //商户ID		
				'orderNo'          	=>$_orderNos,  //订单号
				'merchOrderNo' 		=>$_outOrderNo,
				// 'returnUrl'      	=>$this->getReturnURL(),
				'returnUrl'      	=>$ret_url,
				'notifyUrl'       	=>$noty_url,
				//'notifyUrl'       =>'http://'.$_SERVER['HTTP_HOST'].'/index.php/yjpay/payment/notify/',
				'version'			=> 	'1.0',
				'exVersion'			=>'MageWeb-2.1.2',
				'signType'			=>	'MD5',
			//业务参数
				'goodsInfoList'   =>$this->getWkGoodsInfoListByOrderList($sales_order->getAllItems()),//货物信息
				'orderDetail' => $attachDetails,//订单扩展信息
				'userId'       	   	=>$this->getConfigData('merchant_userId'), //商户ID	
				'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(), //原始订单币种
				'amount'	=> $this->chageBaseConvert2SessionConvert($payment->getBaseAmountOrdered()),//原始订单金额
				'webSite' => $_SERVER['HTTP_HOST'],//所属网站，当前主机HOST
				'deviceFingerprintId' => $session_id,
				'memo' => '备注001',//备注				
				'acquiringType' => $this->getConfigData('payType')?$this->getConfigData('payType'):"CRDIT",//收单类型,CRDIT：信用卡；YANDEX： 网银方式
		);		

		$mysign = $this->signParaArray($parameter);
		$parameter['sign'] = $mysign;

		return $parameter;	
    }

    //获取IP地址
    public function get_real_ip(){
    	$ip=false;
    	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
    		$ip = $_SERVER["HTTP_CLIENT_IP"];
    	}
    	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    		$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
    		if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
    		for ($i = 0; $i < count($ips); $i++) {
    			if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
    				$ip = $ips[$i];
    				break;
    			}
    		}
    	}
    	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
    
    /**
     * 
     * @Title	                    ： getWkGoodsInfoListByOrderList 
     * @Description	          ： 通过订单列表获得WkGoodsInfoList json字符串
     * @return           return_type
     */
    public function getWkGoodsInfoListByOrderList($ordered_items){
    	foreach ($ordered_items as $k=>$item) {
    		$goods_array[$k]['goodsCount']      = intval($item->getQtyOrdered());
    		$_category_name ="";
    		//获得商品所有信息
    		$_product_model = Mage::getModel('catalog/product') ;
    		$_product = $_product_model->load($item->getProductId());
    		if ( $_product instanceof  Mage_Catalog_Model_Product){
    				
    			$goods_array[$k]['goodsNumber']       		= $_product->getEntityId();
    			$goods_array[$k]['goodsName']       		= $_product->getName();
    			$goods_array[$k]['itemSharpUnitPrice']   = $this->chageBaseConvert2SessionConvert($_product->getPrice());
    				
    			//获得商品所属分类
    			$categories = $_product->getCategoryCollection();
    			foreach($categories as $_category_enty) {
    				$_category_model = Mage::getModel('catalog/category');
    				$_category = $_category_model->load($_category_enty->getEntityId());
    				if ($_category instanceof Mage_Catalog_Model_Category){
    					//$_category_name=$_category_name.$_category->getName();
						$_category_name=$_category->getName();
    				}    	
    			}
    		}
    		// $goods_array[$k]['itemSharpProductCode']    = $_category_name;
    		$goods_array[$k]['itemSharpProductCode'] = $_category_name ? $_category_name : 'default';
			//Mage::log("获取分类：".json_encode($goods_array), null,'log.log');
    	}
    	return json_encode($goods_array);
    }
    
	public function sign($prestr) {
		$mysign = md5($prestr);
		return $mysign;
	}
	
	/**
	 * 组装参数和签名
	 */
	public function signParaArray($parameter_array){		
		// file_put_contents("E:/log/cqmagentonew1.log", "请求待签名字符串parameter_array：".json_encode($parameter_array,true),FILE_APPEND);	
		$parameter = $this->para_filter($parameter_array);//过滤
		// file_put_contents("E:/log/cqmagentonew2.log", "请求待签名字符串parameter：".json_encode($parameter,true),FILE_APPEND);	

		$secretKey = $this->getConfigData('secretKey');	
		// file_put_contents("E:/log/cqmagentonew3.log", "请求待签名字符串secretKey：".json_encode($secretKey,true),FILE_APPEND);	

		$sort_array = array();
		$arg = "";
		$sort_array = $this->arg_sort($parameter);//排序
		// file_put_contents("E:/log/cqmagentonew4.log", "请求待签名字符串sort_array：".json_encode($sort_array,true),FILE_APPEND);	

		foreach ($sort_array as $key=>$val){
			$arg.=$key."=".$val."&";
			//$arg.=$key."=".$this->charset_encode($val,"utf-8")."&";
		}
		// file_put_contents("E:/log/cqmagentonew5.log", "请求待签名字符串arg：".json_encode($arg,true),FILE_APPEND);

		$prestr = substr($arg,0,count($arg)-2);	
		// file_put_contents("E:/log/cqmagentonew.log", "请求待签名字符串：".$prestr.$secretKey,FILE_APPEND);	
		Mage::log("请求待签名字符串：".$prestr.$secretKey, null,'debug1.log');	

		return $this->sign($prestr.$secretKey);
	}
    
	/**
	 * 过滤
	 */
	public function para_filter($parameter) {
		$para = array();
		while (list ($key, $val) = each ($parameter)) {
			if($key == "sign")continue;
			else	$para[$key] = $parameter[$key];
		}
		return $para;
	}
	/**
	 * 排序
	 */
	public function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;
	}
	/**
	* 编码转换
	*/
	public function charset_encode($str){
		if(function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($str,"utf-8",'auto');
		}elseif(function_exists("iconv")){
			$output = iconv("utf-8","GBK",$str);
		}else die("sorry, you have no libs support for charset change.");
		return $output;
	} 
	public function charset_encode1($input,$_output_charset ,$_input_charset ="GBK" ) {
		
		$output = "";
		if($_input_charset == $_output_charset || $input ==null) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		
		return $output;
	}	

	/**
	 * 发送错误日志
	 */
	public function sendErrorLogByEmail($error_message){
		$php_info = PHP_VERSION;
		//$magento_version = Mage::getConfig();
		$url = $_SERVER['HTTP_HOST'];
		$time = date("Y-m-d H:i:s",time());
		$content = "<table border='1' cellpadding='0' style='width: 700px;'><tbody>";
		$content .= "<tr><td width='150' valign='top'>错误时间：</td><td>".$time."</td></tr>";
		$content .= "<tr><td width='150' valign='top'>客户机地址：</td><td>".$url."</td></tr>";
		$content .= "<tr><td width='150' valign='top'>错误信息：</td><td><div style='width:550px;'>".$error_message."</div></td></tr>";
		//$content .= "<tr><td width='150' valign='top'>订单信息：</td><td><div style='width:550px;'>".json_encode($order_message)."</div></td></tr>";
		$content .= "<tr><td width='150' valign='top'>Php版本信息：</td><td>".$php_info."</td></tr>";
		//$content .= "<tr><td>Magento信息：</td><td>".$magento_version."</td></tr>";
		$content .= '</tbody></table>';
		
		Mage::log("错误邮件内容:".$content,null,'log1.log');
		
		$smtpm = Mage::getModel('yjpay/MailUtil');
		$to="xweijun@yiji.com";
		$title="mangeto错误日志";
		$webname   =  "magento";
		$cfg_smtp_server     = 'smtp.163.com';//'smtp.qq.com';
		$cfg_smtp_port       = '25';
		$cfg_smtp_usermail = 'yijimagelog@163.com';
		$cfg_smtp_password = 'yijifu';
		$smtpm->smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password); //初始化
		$smtpm->debug = false;
		$mailtitle=$title;  
		$mailbody=$content; 
		$mailtype='HTML';   
		$smtpm->sendmail($to,$webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
	}
   
	/**
	 * Return authorized languages by Yjpay
	 *
	 * @param	none
	 * @return	array
	 */
	protected function _getAuthorizedLanguages()
	{
		$languages = array();
		
        foreach (Mage::getConfig()->getNode('global/payment/yjpay_payment/languages')->asArray() as $data) 
		{
			$languages[$data['code']] = $data['name'];
		}
		return $languages;
	}
	
	/**
	 * Return language code to send to Yjpay
	 *
	 * @param	none
	 * @return	String
	 */
	protected function _getLanguageCode()
	{
		// Store language
		$language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));
		// Authorized Languages
		$authorized_languages = $this->_getAuthorizedLanguages();
		if (count($authorized_languages) === 1) 
		{
			$codes = array_keys($authorized_languages);
			return $codes[0];
		}
		if (array_key_exists($language, $authorized_languages)) 
		{
			return $language;
		}
		// By default we use language selected in store admin
		return $this->getConfigData('language');
	}



    /**
     *  Output failure response and stop the script
     *
     *  @param    none
     *  @return	  void
     */
    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    /**
     *  Return response for Yjpay success payment
     *
     *  @param    none
     *  @return	  string Success response string
     */
    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     *  Return response for Yjpay failure payment
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }
    
 
    public function chageBaseConvert2SessionConvert($price){
		$baseCurrencyCode = Mage::app()->getBaseCurrencyCode();//基础币种
    	$CurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();//当前订单币种
		$Result= Mage::helper('directory')->currencyConvert($price, $baseCurrencyCode, $CurrencyCode);
		//Mage::log($price." ConvertTo: ".$Result,null,"log.log");
    	$Result = ($Result==0)?"0.00":$Result;
		return $Result;
    }

}
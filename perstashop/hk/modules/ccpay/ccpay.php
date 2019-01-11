<?php

class ccpay extends PaymentModule {
	private $_html = '';
	private $_postErrors = array ();
	public function __construct() {

		$this->name = 'ccpay';
		$this->tab = 'payments_gateways';
		$this->version = 2.2;
		$this->author = 'Yjpay';

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		parent::__construct ();

		$this->displayName = $this->l ( 'Credit card payment' );
		$this->description = $this->l ( 'Accept online payments easily and securely with a smarter payment gateway.');
	}

	public function SelectOderstate($name){
		$sql='select  (`id_order_state`)   from  `'._DB_PREFIX_.'order_state_lang`  where (`name`="'.$name.'")' ;
		$id=Db::s($sql);
		foreach ($id as $idorder) {
			return $idorder['id_order_state'];
		}
	}
	public function idorderstate($color){
		$sql='select  (`id_order_state`)   from  `'._DB_PREFIX_.'order_state`  where (`color`="'.$color.'") order by `id_order_state` desc' ;
		$id=Db::s($sql);
		foreach ($id as $idorder) {
			return $idorder['id_order_state'];
		}
	}
	public function InsertOder($color){
		$sql='insert into `'._DB_PREFIX_.'order_state` (`color`) values("'.$color.'")';
		Db::s($sql);
	}
	public function Insertstate($id,$name){
		$sql='insert into `'._DB_PREFIX_.'order_state_lang` (`id_order_state`,`id_lang`,`name`) values("'.$id.'",1,"'.$name.'")';
		Db::s($sql);
	}
	public function Oderstate($name){
		$stateid=$this->SelectOderstate($name);
		if(!$stateid){
			if($name==='success'){
				$color='#32CD33';
			}else if($name==='closed'){
				$color='#8f0622';
			}else if($name==='pending'){
				$color='#4169E3';
			} else if($name==='authorizing'){
				$color='#ff00ff';
			}
				$this->InsertOder($color);
				$id=$this->idorderstate($color);
				$this->Insertstate($id,$name);
				return $id;
			}
		else{
			return $stateid;
		 }
	}
	public function getOrderStateslang(){
		$sql='SELECT COUNT( * )
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name =  "'._DB_PREFIX_.'order_state"
			AND table_schema =  "'._DB_NAME_.'"
			AND COLUMN_NAME = "deleted"';
		$deleted=Db::s($sql);
		foreach ($deleted as $delete) {
			if($delete['COUNT( * )']==0){
			$sql='SELECT *
			FROM `'._DB_PREFIX_.'order_state` os
			INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang`=1)
			 ORDER BY `name` ASC';
		 	return Db::s($sql);
		}else{
			$sql='SELECT *
			FROM `'._DB_PREFIX_.'order_state` os
			INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang`=1)
			 WHERE deleted=0 ORDER BY `name` ASC';
		 return Db::s($sql);
		}}
	}
	public function Option($orderstatus,$config){
		foreach ($orderstatus as $order) {
			if($order['id_order_state']==$config)
			{
				$option.='<option value="'.$order['id_order_state'].'"selected = "selected">'. $order['name'].'</option>';
			}else{
				$option.='<option value="'.$order['id_order_state'].'">'.$order['name'].'</option>';
			}
		}
		return $option;
	}

	// install yjpay and write some default data
	public function install() {
		$successid=$this->Oderstate('success');
		$closedid=$this->Oderstate('closed');
		$pendingid=$this->Oderstate('pending');
        $authorizingid=$this->Oderstate('authorizing');
		if (! parent::install ()
			or ! Configuration::updateValue ( 'CCPAY_SUCCES_ORDER_STATUS',$successid )
			or ! Configuration::updateValue ( 'CCPAY_CLOSED_ORDER_STATUS',$closedid)
			or ! Configuration::updateValue ( 'CCPAY_PENDING_ORDER_STATUS',$pendingid)
            or ! Configuration::updateValue ( 'CCPAY_AUTHORIZING_ORDER_STATUS',$authorizingid)
			or ! Configuration::updateValue ( 'CCPAY_MERCHANTKEY', 'init' )
            or ! Configuration::updateValue ( 'CCPAY_MERCHANTID', 'init' )
			or ! Configuration::updateValue ( 'CCPAY_GATEWAY_DOMAIN','https://api.yiji.com')
  	        // or ! Configuration::updateValue ( 'CCPAY_ENCRYPTION_URL','https://cer.gpayonline.com/encrypt.action?action=doEncrypt')
			or ! Configuration::updateValue ( 'CCPAY_REDIRECT_TYPE','1')
			or ! Configuration::updateValue ( 'CCPAY_MARK_BUTTON_IMG','/gcp/merpay.png')
			or ! $this->registerHook ( 'payment' ) or ! $this->registerHook ( 'paymentReturn' ))
			return false;

		return true;
	}

	public function uninstall() {
		if (! Configuration::deleteByName ( 'CCPAY_MERCHANTKEY' )
			or ! Configuration::deleteByName ( 'CCPAY_SUCCES_ORDER_STATUS')
			or ! Configuration::deleteByName ( 'CCPAY_CLOSED_ORDER_STATUS')
			or ! Configuration::deleteByName ( 'CCPAY_PENDING_ORDER_STATUS')
			or ! Configuration::deleteByName ( 'CCPAY_GATEWAY_DOMAIN')
			or ! Configuration::deleteByName ( 'CCPAY_REDIRECT_TYPE')
			or ! Configuration::deleteByName ( 'CCPAY_MARK_BUTTON_IMG')
            or ! Configuration::deleteByName ( 'CCPAY_AUTHORIZING_ORDER_STATUS')
			or ! Configuration::deleteByName ( 'CCPAY_MERCHANTID')
            // or ! Configuration::deleteByName ( 'CCPAY_ENCRYPTION_URL')
			or ! parent::uninstall ())
			return false;
		return true;
	}

	public function getContent() {
		$this->_html = '<h2>Credit Card Payment</h2>';
		if (isset ( $_POST ['submitcredit_payment'] )) {
			if (empty ( $_POST ['merchantkey'] ))
				$this->_postErrors [] = $this->l ( 'Merchant key can not be empty!' );
			if (! sizeof ( $this->_postErrors )) {
				Configuration::updateValue ( 'CCPAY_MERCHANTKEY', strval ( $_POST ['merchantkey'] ) );
				Configuration::updateValue ( 'CCPAY_SUCCES_ORDER_STATUS', strval ( $_POST ['success'] ) );
				Configuration::updateValue ( 'CCPAY_CLOSED_ORDER_STATUS', strval ( $_POST ['closed'] ) );
				Configuration::updateValue ( 'CCPAY_PENDING_ORDER_STATUS', strval ( $_POST ['pending'] ) );
				Configuration::updateValue ( 'CCPAY_GATEWAY_DOMAIN', strval ( $_POST ['gateway'] ) );
				Configuration::updateValue ( 'CCPAY_REDIRECT_TYPE', strval ( $_POST ['redirect'] ) );
				Configuration::updateValue ( 'CCPAY_MARK_BUTTON_IMG', strval ( $_POST ['img'] ) );
                Configuration::updateValue ( 'CCPAY_AUTHORIZING_ORDER_STATUS', strval ( $_POST ['authorizing'] ) );
				Configuration::updateValue ( 'CCPAY_MERCHANTID', strval ( $_POST ['merchantid'] ) );
                // Configuration::updateValue ( 'CCPAY_ENCRYPTION_URL', strval ( $_POST ['encryptionurl'] ) );
				$languages = Language::getLanguages ();
				$this->displayConf ();
			} else
				$this->displayErrors ();
		}

		$this->displaycredit_payment ();
		$this->displayFormSettings ();
		return $this->_html;
	}

	public function displayConf() {
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l ( 'Confirmation' ) . '" />' . $this->l ( 'Settings updated' ) . '</div>';
	}

	public function displayErrors() {
		$nbErrors = sizeof ( $this->_postErrors );
		$this->_html .= '<div class="alert error"><h3>' . ($nbErrors > 1 ? $this->l ( 'There are' ) : $this->l ( 'There is' )) . ' ' . $nbErrors . ' ' . ($nbErrors > 1 ? $this->l ( 'errors' ) : $this->l ( 'error' )) . '</h3><ol>';
		foreach ( $this->_postErrors as $error )
			$this->_html .= '<li>' . $error . '</li>';
		$this->_html .= '</ol></div>';
	}

	/* about credit_payment and logo */
	public function displaycredit_payment() {
		$this->_html .
		'</p><p style="text-align: center;"></p><div style="clear: right;"></div></div>'
		.'<img src="'.Configuration::get('CCPAY_MARK_BUTTON_IMG').'" style="float:left; margin-right:15px;" /><b>'
		.$this->l ( 'Payment Gateway.' ) . '</b><br /><br />' . '<br />' . '<div style="clear:both;">&nbsp;</div>';
	}

	/* background from setting */
	public function displayFormSettings() {
		$conf = Configuration::getMultiple ( array (
				'CCPAY_MERCHANTKEY',
                'CCPAY_MERCHANTID',
				'CCPAY_SUCCES_ORDER_STATUS',
				'CCPAY_CLOSED_ORDER_STATUS',
				'CCPAY_PENDING_ORDER_STATUS',
                'CCPAY_AUTHORIZING_ORDER_STATUS',
				'CCPAY_GATEWAY_DOMAIN',
				'CCPAY_REDIRECT_TYPE',
				'CCPAY_MARK_BUTTON_IMG',
                // 'CCPAY_ENCRYPTION_URL',
		) );
		$merchantkey = array_key_exists ( 'merchantkey', $_POST ) ? $_POST ['merchantkey'] : (array_key_exists ( 'CCPAY_MERCHANTKEY', $conf ) ? $conf ['CCPAY_MERCHANTKEY'] : '');
        $merchantid = array_key_exists ( 'merchantid', $_POST ) ? $_POST ['merchantid'] : (array_key_exists ( 'CCPAY_MERCHANTID', $conf ) ? $conf ['CCPAY_MERCHANTID'] : '');
        $authorizing = array_key_exists ( 'authorizing', $_POST ) ? $_POST ['authorizing'] : (array_key_exists ( 'CCPAY_AUTHORIZING_ORDER_STATUS', $conf ) ? $conf ['CCPAY_AUTHORIZING_ORDER_STATUS'] : '');
		$success = array_key_exists ( 'success', $_POST ) ? $_POST ['success'] : (array_key_exists ( 'CCPAY_SUCCES_ORDER_STATUS', $conf ) ? $conf ['CCPAY_SUCCES_ORDER_STATUS'] : '');
		$closed = array_key_exists ( 'closed', $_POST ) ? $_POST ['closed'] : (array_key_exists ('CCPAY_CLOSED_ORDER_STATUS', $conf ) ? $conf ['CCPAY_CLOSED_ORDER_STATUS'] : '');
		$pending = array_key_exists ( 'pending', $_POST ) ? $_POST ['pending'] : (array_key_exists ( 'CCPAY_PENDING_ORDER_STATUS', $conf ) ? $conf ['CCPAY_PENDING_ORDER_STATUS'] : '');
		// $encryptionurl = array_key_exists ( 'encryptionurl', $_POST ) ? $_POST ['encryptionurl'] : (array_key_exists ( 'CCPAY_ENCRYPTION_URL', $conf ) ? $conf ['CCPAY_ENCRYPTION_URL'] : '');
		$gateway = array_key_exists ( 'gateway', $_POST ) ? $_POST ['gateway'] : (array_key_exists ( 'CCPAY_GATEWAY_DOMAIN', $conf ) ? $conf ['CCPAY_GATEWAY_DOMAIN'] : '');
		$redirect = array_key_exists ( 'redirect', $_POST ) ? $_POST ['redirect'] : (array_key_exists ( 'CCPAY_REDIRECT_TYPE', $conf ) ? $conf ['CCPAY_REDIRECT_TYPE'] : '');
		$img = array_key_exists ( 'img', $_POST ) ? $_POST ['img'] : (array_key_exists ( 'CCPAY_MARK_BUTTON_IMG', $conf ) ? $conf ['CCPAY_MARK_BUTTON_IMG'] : '');


		$orderstatus=$this->getOrderStateslang();
		$this->_html.= '<form action="' . $_SERVER ['REQUEST_URI']
			. '" method="post" style="clear: both;"><fieldset><legend><img src="../img/admin/contact.gif" />'
			. $this->l ( 'Settings' ) . '</legend>

			<label>Merchant Key</label>
			<div class="margin-form">
			<input type="text" name="merchantkey" value="' . $merchantkey. '" />
			</div>
	     	<label>Merchant Id</label>
			<div class="margin-form">
			<input type="text" name="merchantid" value="' . $merchantid. '" />
			</div>

			<label>Pending Status:</label>
			<div class="margin-form">
			<select name="pending" style="width:156px">
				'.$this->Option($orderstatus,$pending).'
			</select>
			</div>

			<label>Success Status:</label>
			<div class="margin-form">
			<select name="success" style="width:156px" >
			     	  '.$this->Option($orderstatus,$success).'
			</select>
			</div>

			<label>Closed Status:</label>
			<div class="margin-form">
			<select name="closed" style="width:156px" >>
			    	  '.$this->Option($orderstatus,$closed).'
			</select>
			</div>
        	<label>Authorizing Status:</label>
			<div class="margin-form">
			<select name="authorizing" style="width:156px" >>
			    	  '.$this->Option($orderstatus,$authorizing).'
			</select>
			</div>
			<label>Gateway domain</label>
			<div class="margin-form">
			<input type="text" name="gateway" value="' . $gateway. '" />
			</div>


			<label>Redirect type</label>
			<div class="margin-form">
			<input type="text" name="redirect" value="' . $redirect. '" />
			</div>

			<label>Button image</label>
			<div class="margin-form">
			<input type="text" name="img" value="' . $img. '" />
			</div>

			<center><input type="submit" name="submitcredit_payment" value="Update settings" class="button" /></center></fieldset></form><br /><br />';
	}

    //国家转码
    public static function countryCode($country ){
            switch($country)  {
            case "Armenia":
            return "AM";
            case "Canada":
            return "CA";
            default:
            return $country;
            }
    }


    public function getOrder($cart){
    	global $smarty, $cookie;

    	return $odrers =  array(
    		'orderNo' => $this->currentOrder,
    		'currencyCode' => Currency::getCurrencyInstance ( $cart->id_currency )->iso_code,
    		'amountLoc' => round ( $cart->getOrderTotal ( true, 3 ), 2 ),
    		'webSite' => 'https://'.Configuration::get ( 'PS_SHOP_DOMAIN' )
    	);

    }

    ////构建支付提交参数
	public function commitPayment($cart) {

		global $smarty, $cookie;

		$merchantkey = Configuration::get ( 'CCPAY_MERCHANTKEY' );
		$order_state = Configuration::get ( 'CCPAY_PENDING_ORDER_STATUS');

		$address = new Address ( intval ( $cart->id_address_invoice ) );
		$shippingAddress = new Address ( intval ( $cart->id_address_delivery ) );
		$customer = new Customer ( intval ( $cart->id_customer ) );

		$query = [];
        $this->validateOrder ( $cart->id, $order_state, $query['amount'], $this->displayName );
        ////基本参数
		$query['protocol'] ='httpPost';
        $query['service'] ='cardAcquiringCashierPay';
        ///商户号
        $query['partnerId'] =Configuration::get ( 'CCPAY_MERCHANTID' );

        ///订单流水
        $query['orderNo'] = date("YmdHis").rand(100,999) .'|'. (int) $this->currentOrder;
        $query['signType'] ='MD5';
 	    $query['returnUrl'] = 'https://'.Configuration::get ( 'PS_SHOP_DOMAIN' ).'/modules/ccpay/payment_return.php';	//同步返回参数页面
		$query['notifyUrl'] = 'https://'.Configuration::get ( 'PS_SHOP_DOMAIN' ).'/modules/ccpay/payment_result.php';	//异步通知参数页面

        if ($address->id_state)
			$state = new State ( intval ( $address->id_state ) );
		else
			$state = '';
		if ($shippingAddress->id_state)
			$shippingstate = new State ( intval ( $shippingAddress->id_state ) );
		else
			$shippingstate = '';

		//商户号
        $query['userId'] =Configuration::get ( 'CCPAY_MERCHANTID' );
         //currencyCode	币种
        $query['currency'] = Currency::getCurrencyInstance ( $cart->id_currency )->iso_code;
        //orderAmount	交易金额
      	$query['orderAmount'] = round ( $cart->getOrderTotal ( true, 3 ), 2 );
        //webSite	所属网站
        $query['webSite'] = Configuration::get ( 'PS_SHOP_DOMAIN' );
        // 商户外部订单号
        // $query['merchOrderNo'] = date("YmdHis").rand(100,999) . (int) $this->currentOrder;
        $query['merchOrderNo'] = date("YmdHis").rand(100,999) . (int) $this->currentOrder;
        // $query['merchOrderNo'] = 'mno' . date(YmdHis) .rand(1000,9999);
        // 收单类型
        $query['acquiringType'] = 'CRDIT';

        ///IPget_client_ip()
        $attachDetails['ipAddress'] =get_client_ip();
        ///billToCity 账单地址城市
        $attachDetails['billToCity'] =$address->city;
          ///billToCountry 国家 需要转换简码
        $attachDetails['billToCountry'] =self::countryCode($address->country)  ;
         ///billToState 洲 需要转换简码
        $attachDetails['billToState'] =is_object ( $state ) ? $state->iso_code : $address->city;
        /// billToPostalCode 邮编
        $attachDetails['billToPostalCode'] = $address->postcode;
        ///billToEmail 邮箱
        $attachDetails['billToEmail'] = $customer->email;
        ///billToFirstName 姓
        $attachDetails['billToFirstName'] = $address->firstname;
         ///billToFirstName 名
        $attachDetails['billToLastName'] = $address->lastname;
         ///billToPhoneNumber 手机号码
        $attachDetails['billToPhoneNumber'] = empty ( $address->phone_mobile ) ? $address->phone : $address->phone_mobile;
        ///billToStreet1 地址1
        $attachDetails['billToStreet'] = $address->address1;

        ///shipToCity 收货人城市
        $attachDetails['shipToCity'] =$shippingAddress->city;

       // shipToCountry	收货地址国家 需要转换简码
        $attachDetails['shipToCountry'] =self::countryCode($shippingAddress->country);
       //shipToFirstName	收货人姓
        $attachDetails['shipToFirstName'] =$shippingAddress->firstname;
        //shipToLastName	收货人名
        $attachDetails['shipToLastName'] =$shippingAddress->lastname;
       //shipToEmail	收货人邮箱
        $attachDetails['shipToEmail'] = $customer->email;
      //shipToPhoneNumber	收货人电话
        $attachDetails['shipToPhoneNumber'] =empty ( $shippingAddress->phone_mobile ) ? $shippingAddress->phone : $shippingAddress->phone_mobile;
       //shipToPostalCode	收货人邮编
        $attachDetails['shipToPostalCode'] = $shippingAddress->postcode;
      //shipToState	收货人州 需要转换简码
      	$attachDetails['shipToState'] = is_object ( $shippingstate ) ? $shippingstate->iso_code : $shippingAddress->city;
       //shipToStreet1	收货地址一
        $attachDetails['shipToStreet'] = $shippingAddress->address1;
       //logisticsFee
        $attachDetails['logisticsFee'] ='0';
        $attachDetails['logisticsMode'] ='EMS';
       // customerEmail	客户email
        $attachDetails['customerEmail'] = $customer->email;
       //customerPhoneNumber	客户手机号
        $attachDetails['customerPhoneNumber'] = empty ( $address->phone_mobile ) ? $address->phone : $address->phone_mobile;
        //merchantName 商户邮箱
        $attachDetails['merchantName'] =Configuration::get ( 'PS_SHOP_NAME' );
         // merchantEmail 商户名字
        $attachDetails['merchantEmail'] =Configuration::get ( 'PS_SHOP_EMAIL' );
        // 货物贸易订单扩展信息
        $query['attachDetails'] = json_encode($attachDetails,true);

        // wkGoodsInfoList	货物信息 json
		$products = $cart->getProducts ();
        $infoList_string='[';
	    $query['goodsInfoOrders'] ='[';
		foreach ( $products as $key => $val ) {
		    $infoList_string=$infoList_string.'{"goodsNumber":"'.$val['id_product'].'","goodsName":"'.
            $val['name'].'","itemSharpProductCode":"'.$val['name'].'","itemSharpUnitPrice":"'. $val['price_wt']
            .'","goodsCount":"'. $val['cart_quantity'].'"},';
		}
        $infoList_string = substr($infoList_string,0,-1);
		$query['goodsInfoOrders'] =$infoList_string.']';

		ksort($query);
		$fields_string="";
		foreach($query as $key=>&$value) {
			$value= unescape($value);
			$fields_string .= $key.'='.$value.'&';
		}
        $fields_string=substr($fields_string,0,-1);

		$query ['sign']=md5($fields_string.$merchantkey);

		file_put_contents(__DIR__.'/logs/'.date('Ymd').'-orders.log',(int) $this->currentOrder." [ ".date('Y-m-d H:i:s')." REQUSET-DATA]:".json_encode($query,1)."\n",FILE_APPEND);

		return $query;
	}

	// 表单提交订单信息
    public function submitByForm($allOptions,$uri)
    {
    	$html='';
    	$html .= '<html><head><meta http-equiv="Content-Type" content="textml; charset=UTF-8" /></head>
            <body onLoad="document.dinpayForm.submit();">You will be redirected to Yjpay in a few seconds.<form name="dinpayForm" id="dinpayForm" method="POST" action="'.$uri.'" >';
        foreach($allOptions as $k => $v)
        {
            $html .='<input type="hidden" name="'.$k.'" value="'.htmlentities($v,ENT_QUOTES,"UTF-8").'"/>';
        }
        $html .= '</form></body><html>';
        return $html;
    }

	public function commitPayment3D($cart) {

		include_once("./gcp/config.php");
		include_once("./gcp/common.php");

		$conf = Configuration::getMultiple ( array (
				'CCPAY_GATEWAY_DOMAIN',
				'CCPAY_REDIRECT_TYPE',
				'CCPAY_MERCHANTKEY',
		) );

		$pay_url=$conf['CCPAY_GATEWAY_DOMAIN'] ;
		$urlquery['redirectType']=$conf['CCPAY_REDIRECT_TYPE'] ;


		global $smarty, $cookie;

		$address = new Address ( intval ( $cart->id_address_invoice ) ); // id_address_delivery
		$shippingAddress = new Address ( intval ( $cart->id_address_delivery ) );
		$customer = new Customer ( intval ( $cart->id_customer ) );

		$merchantkey = Configuration::get ( 'CCPAY_MERCHANTKEY' );

		$version = $this->version;
		$order_state = Configuration::get ( 'CCPAY_PENDING_ORDER_STATUS');

		$urlquery['apiver'] = CCPAY_VERSION;
		$urlquery['amount'] = round ( $cart->getOrderTotal ( true, 3 ), 2 );
		$urlquery['sfee'] = $cart->getOrderTotal ( true, 5 );
		$urlquery['currency'] = Currency::getCurrencyInstance ( $cart->id_currency )->iso_code;
		/* products data */
		$products = $cart->getProducts ();
		$i = 1;
		foreach ( $products as $key => $val ) {
			$urlquery['pn'.$i] = $val['name'];
			$urlquery['pp'.$i] = $val['price_wt'];
			$urlquery['pq'.$i] = $val['cart_quantity'];
			$i++;
		}
		$this->validateOrder ( $cart->id, $order_state, $urlquery['amount'], $this->displayName );

		$urlquery['orderid'] = $this->currentOrder;
		$urlquery['returnpath'] = '/modules/ccpay/payment_result.php';	//?
		$urlquery['notifypath'] = '/modules/ccpay/payment_result.php';	//?

		$urlquery['payername'] = $address->firstname. ' ' .$address->lastname ;
		$urlquery['baddress'] = $address->address1;
		/* Customer Address */
		if ($address->id_state)
			$state = new State ( intval ( $address->id_state ) );
		else
			$state = '';
		if ($shippingAddress->id_state)
			$shippingstate = new State ( intval ( $shippingAddress->id_state ) );
		else
			$shippingstate = '';

		$urlquery['bcountry'] = $address->country;
		$urlquery['bprovince'] = is_object ( $state ) ? $state->name : '';
		$urlquery['bcity'] = $address->city;
		$urlquery['bemail'] = $customer->email;
		$urlquery['bphone'] = empty ( $address->phone_mobile ) ? $address->phone : $address->phone_mobile;
		$urlquery['bpost'] = $address->postcode;

		$urlquery['dname'] = $shippingAddress->firstname. ' ' .$shippingAddress->lastname ;
		$urlquery['daddress'] = $shippingAddress->address1;
		$urlquery['dcountry'] =  $shippingAddress->country;
		$urlquery['dprovince'] = is_object ( $shippingstate ) ? $shippingstate->name : '';
		$urlquery['dcity'] = $shippingAddress->city;
		$urlquery['demail'] = $customer->email;
		$urlquery['dphone'] = empty ( $shippingAddress->phone_mobile ) ? $shippingAddress->phone : $shippingAddress->phone_mobile;
		$urlquery['dpost'] = $shippingAddress->postcode;

  		$urlquerystr = gcp_getPayQueryString($urlquery,Configuration::get('CCPAY_MERCHANTKEY'));

		return $pay_url. "/gateway".'?'.$urlquerystr;
	}

	public function hookPayment($params) {
		global $smarty;
		if (! $this->active)
			return;

		$smarty->assign('ccpay_img', Configuration::get('CCPAY_MARK_BUTTON_IMG'));

		if(Configuration::get('CCPAY_REDIRECT_TYPE')==3){
			// return $this->display ( __FILE__, 'ccpay_3d.tpl' );
			return $this->display ( __FILE__, 'ccpay_form.tpl' );
		}else{
			return $this->display ( __FILE__, 'ccpay_form.tpl' );
		}
	}

	function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array (), $currency_special = NULL, $dont_touch_amount = false, $secure_key = false, Shop $shop = null) {
		if (! $this->active)
			return;
		parent::validateOrder ( $id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key, $shop );
	}

}

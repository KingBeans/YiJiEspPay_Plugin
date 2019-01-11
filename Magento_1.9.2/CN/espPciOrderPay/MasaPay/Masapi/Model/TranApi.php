<?php

define("MASAPI_VERSION", "1.1.8");


/**
 * MASAPI Transaction Class
 *
 */
class MasaPay_Masapi_Model_TranApi {
	
	// Required for all transactions
	public $key;			// Source key
	public $pin;			// Source pin (optional)
	public $amount;		// the entire amount that will be charged to the customers card 
							// (including tax, shipping, etc)
	public $invoice;		// invoice number.  must be unique.  limited to 10 digits.  use orderid if you need longer. 
	
	// Required for Commercial Card support
	public $ponum;			// Purchase Order Number
	public $tax;			// Tax
	public $nontaxable;	// Order is non taxable
	
	// Amount details (optional)
	public $tip; 			// Tip
	public $shipping;		// Shipping charge
	public $discount; 	// Discount amount (ie gift certificate or coupon code)
	public $subtotal; 	// if subtotal is set, then 
							// subtotal + tip + shipping - discount + tax must equal amount 
							// or the transaction will be declined.  If subtotal is left blank
							// then it will be ignored
	public $currency;		// Currency of $amount
	
	// Required Fields for Card Not Present transacitons (Ecommerce)
	public $card;			// card number, no dashes, no spaces
	public $exp;			// expiration date 4 digits no /
	public $cardholder; 	// name of card holder
	public $street;		// street address
	public $zip;			// zip code
	
	// Fields for Card Present (POS) 
	public $magstripe;  	// mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
	public $cardpresent;   // Must be set to true if processing a card present transaction  (Default is false)
	public $termtype;  	// The type of terminal being used:  Optons are  POS - cash register, StandAlone - self service terminal,  Unattended - ie gas pump, Unkown  (Default:  Unknown)
	public $magsupport;  	// Support for mag stripe reader:   yes, no, contactless, unknown  (default is unknown unless magstripe has been sent)
	public $contactless;  	// Magstripe was read with contactless reader:  yes, no  (default is no)
	public $dukpt;			// DUK/PT for PIN Debit
	public $signature;     // Signature Capture data
		
	// fields required for check transactions
	public $account;		// bank account number
	public $routing;		// bank routing number
	public $ssn;			// social security number
	public $dlnum;			// drivers license number (required if not using ssn)
	public $dlstate;		// drivers license issuing state
	public $checknum;		// Check Number
	public $accounttype;       // Checking or Savings
	public $checkformat;	// Override default check record format
	public $checkimage_front;    // Check front
	public $checkimage_back;		// Check back
	
	
	// Fields required for Secure Vault Payments (Direct Pay)
	public $svpbank;		// ID of cardholders bank
	public $svpreturnurl;	// URL that the bank should return the user to when tran is completed
	public $svpcancelurl; 	// URL that the bank should return the user if they cancel
	
	

	// Option parameters
	public $origauthcode;	// required if running postauth transaction.
	public $command;		// type of command to run; Possible values are: 
						// sale, credit, void, preauth, postauth, check and checkcredit. 
						// Default is sale.
	public $orderid;		// Unique order identifier.  This field can be used to reference 
						// the order for which this transaction corresponds to. This field 
						// can contain up to 64 characters and should be used instead of 
						// UMinvoice when orderids longer that 10 digits are needed.
	public $custid;   // Alpha-numeric id that uniquely identifies the customer.
	public $description;	// description of charge
	public $cvv2;			// cvv2 code
	public $custemail;		// customers email address
	public $custreceipt;	// send customer a receipt
	public $custreceipt_template;	// select receipt template
	public $ignoreduplicate; // prevent the system from detecting and folding duplicates
	public $ip;			// ip address of remote host
	public $testmode;		// test transaction but don't process it
	public $usesandbox;    // use sandbox server instead of production
	public $timeout;       // transaction timeout.  defaults to 45 seconds
	public $gatewayurl;   	// url for the gateway
	public $proxyurl;		// proxy server to use (if required by network)
	public $ignoresslcerterrors;  // Bypasses ssl certificate errors.  It is highly recommended that you do not use this option.  Fix your openssl installation instead!
	public $cabundle;      // manually specify location of root ca bundle (useful of root ca is not in default location)
	public $transport;     // manually select transport to use (curl or stream), by default the library will auto select based on what is available
		
	// Card Authorization - Verified By Visa and Mastercard SecureCode
	public $cardauth;    	// enable card authentication
	public $pares; 		// 
	
	// Third Party Card Authorization
	public $xid;
	public $cavv;
	public $eci;

	// Recurring Billing
	public $recurring;		//  Save transaction as a recurring transaction:  yes/no
	public $schedule;		//  How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.  Default is monthly.
	public $numleft; 		//  The number of times to run. Either a number or * for unlimited.  Default is unlimited.
	public $start;			//  When to start the schedule.  Default is tomorrow.  Must be in YYYYMMDD  format.
	public $end;			//  When to stop running transactions. Default is to run forever.  If both end and numleft are specified, transaction will stop when the ealiest condition is met.
	public $billamount;	//  Optional recurring billing amount.  If not specified, the amount field will be used for future recurring billing payments
	public $billtax;
	public $billsourcekey;
	
	// Billing Fields
	public $billfname;
	public $billlname;
	public $billcompany;
	public $billstreet;
	public $billstreet2;
	public $billcity;
	public $billstate;
	public $billzip;
	public $billcountry;
	public $billphone;
	public $email;
	public $fax;
	public $website;

	// Shipping Fields
	public $delivery;		// type of delivery method ('ship','pickup','download')
	public $shipfname;
	public $shiplname;
	public $shipcompany;
	public $shipstreet;
	public $shipstreet2;
	public $shipcity;
	public $shipstate;
	public $shipzip;
	public $shipcountry;
	public $shipphone;
	
	// Custom Fields
	public $custom1;
	public $custom2;
	public $custom3;
	public $custom4;
	public $custom5;
	public $custom6;
	public $custom7;
	public $custom8;
	public $custom9;
	public $custom10;
	public $custom11;
	public $custom12;
	public $custom13;
	public $custom14;
	public $custom15;
	public $custom16;
	public $custom17;
	public $custom18;
	public $custom19;
	public $custom20;
	

	// Line items  (see addLine)
	public $lineitems;
	
	
	public $comments; // Additional transaction details or comments (free form text field supports up to 65,000 chars)
	
	public $software; // Allows developers to identify their application to the gateway (for troubleshooting purposes)

	
	// response fields
	public $rawresult;		// raw result from gateway
	public $result;		// full result:  Approved, Declined, Error
	public $resultcode; 	// abreviated result code: A D E
	public $authcode;		// authorization code
	public $refnum;		// reference number
	public $batch;		// batch number
	public $avs_result;		// avs result
	public $avs_result_code;		// avs result
	public $avs;  					// obsolete avs result
	public $cvv2_result;		// cvv2 result
	public $cvv2_result_code;		// cvv2 result
	public $vpas_result_code;      // vpas result
	public $isduplicate;      // system identified transaction as a duplicate
	public $convertedamount;  // transaction amount after server has converted it to merchants currency
	public $convertedamountcurrency;  // merchants currency
	public $conversionrate;  // the conversion rate that was used
	public $custnum;  //  gateway assigned customer ref number for recurring billing
	
	// Cardinal Response Fields
	public $acsurl;	// card auth url
	public $pareq;		// card auth request
	public $cctransid; // cardinal transid

	
	// Errors Response Feilds
	public $error; 		// error message if result is an error
	public $errorcode; 	// numerical error code
	public $blank;			// blank response
	public $transporterror; 	// transport error
	
	// Extended Fraud Profiler Fields
	public $session; // session id
	// responses fields
	public $profilerScore;
	public $profilerResponse;
	public $profilerReason;
	public $ccType;
	public $goodsName;
	public $goodsDesc;
	public $realOrderId;
	public $masaRes;
	public $masapayOrderNo;
	public $grandTotalAmount;
	
	function __construct()
	{
		// Set default values.
		$this->command="sale";
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->timeout=45;
		$this->cardpresent=false;
		$this->lineitems = array();
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];
		$this->software="MasaPay_Masapi v" . MASAPI_VERSION;
	}
	
	protected function _log($message, $level = null)
	{
		Mage::log($message, $level, 'masapi.log');
	}
	
	/**
	 * Verify that all required data has been set
	 *
	 * @return string
	 */
	function CheckData()
	{
		
		$this->amount = preg_replace('/[^\d.]+/', '', $this->amount);
		if(!$this->amount) return "Amount is required";
		if(!$this->invoice && !$this->orderid) return "Invoice number or Order ID is required";

		return 0;		
	}
	
	function addLine($sku, $name, $description, $cost, $qty, $taxAmount,$url)
	{
		$this->lineitems[] = array(
				'sku' => $sku,
				'name' => $name,
				'description' => $description,
				'cost' => $cost,
				'taxable' => ($taxAmount > 0) ? 'Y' : 'N',
				'qty' => $qty,
				'url' => $url
		);
	}
	
	public function getIP() {
		if (array_key_exists("HTTP_X_FORWARDED_FOR",$_SERVER))
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else if (array_key_exists("HTTP_CLIENT_IP",$_SERVER))
		$ip = $_SERVER["HTTP_CLIENT_IP"];
		else if (array_key_exists("REMOTE_ADDR",$_SERVER))
		$ip = $_SERVER["REMOTE_ADDR"];
		else if (@getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (@getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
		else if (@getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
		else
		$ip = "Unknown";
		return $ip;
	}
	
	/**
	 * Send transaction to the Masapay Gateway and parse response
	 *
	 * @return boolean
	 */
	function Process()
	{
		if($this->ccType=='VI'){
			$this->ccType = 'VISA';
			return $this->ProcessMasaPay();		
		} 
		if($this->ccType=='MC'){
			$this->ccType = 'MASTER';
			return $this->ProcessMasaPay();
		} 
			if($this->ccType=='JCB'){
			$this->ccType = 'JCB';
			return $this->ProcessMasaPay();
		}	
		return false;
	}
	
	function ProcessMasaPay(){
		
		$tmp=$this->CheckData();
		
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}
		if(!is_array($this->lineitems)) $this->lineitems=array();
		foreach($this->lineitems as $lineitem){
			$this->goodsName .= $lineitem['name']."|";
			$this->goodsDesc .= $lineitem['name'] . "^^" . number_format($lineitem['qty'],0) . "^" . number_format($lineitem['cost'],2) . "^".$lineitem['url']."|";
		}
		
		$this->goodsName = trim($this->goodsName, " |");
		$this->goodsDesc = trim($this->goodsDesc, " |");
		
		$mconfig = Mage::getStoreConfig('payment/masapi');
		$currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
		$orderAmount = $this->grandTotalAmount;
		if(!($currency_code=='JPY' || $currency_code=='KRW')) $orderAmount = round($orderAmount,2) * 100;
		
		//用户信息
		$customerUserInfo = Mage::getSingleton('customer/session')->getCustomer();
		
		$params = array(
		"version"=>"1.6",
		"merchantId"=> $mconfig['masapay_user_id'],
		"charset"=>"utf-8",
		"language"=>"en",
		"signType"=>$mconfig['masapay_sign_type'],
		"merchantOrderNo"=>$this->orderid,
		"goodsName"=>substr($this->goodsName,0,1000),   
		"goodsDesc"=>substr($this->goodsDesc,0,2000),
		"orderExchange"=> "2",
		"currencyCode"=> $currency_code,
		'orderAmount' => $orderAmount,
		"flag3D"=>"N" ,
		"submitTime"=> date('YmdHis'),
		"expiryTime"=> date("YmdHis",strtotime("+1months",strtotime(date('Y-m-d')))),
		"bgUrl"=> Mage::getUrl('masapayapi/masapay/notify/'),
		"ext1"=>$this->masSession,
		"ext2"=> "" ,
		"remark"=> "" ,
		"payMode"=> "10" ,
		"orgCode"=> $this->ccType,
		"cardNumber"=>$this->card,
		"cardHolderFirstName"=>$this->cardholder,
		"cardHolderLastName"=>" ",
		"cardExpirationMonth"=>substr($this->exp,0,2) ,
		"cardExpirationYear"=>"20".substr($this->exp,2,2) ,
		"securityCode"=>$this->cvv2,
		"cardHolderEmail"=>$this->email,
		"cardHolderPhoneNumber"=>"" ,
		"payExt1"=>"" ,
		"payExt2"=>"" ,
		"billName"=>$this->billfname.'//'.$this->billlname,
		"billAdderess"=>$this->billstreet.' '.$this->billstreet2,
		"billPostalCode"=>str_replace( preg_split("/[0-9]/",$this->billzip), null, $this->billzip),
		"billCompany"=>"" ,
		"billCountry"=>$this->billcountry,
		"billState"=>$this->billstate,
		"billCity"=>$this->billcity,
		"billEmail"=>$this->email,
		"billPhoneNumer"=>$this->billphone,
		"shippingName"=>"" ,
		"shippingAdderess"=>"" ,
		"shippingPostalCode"=>"" ,
		"shippingcompany"=>"" ,
		"shippingCountry"=>"" ,
		"shippingState"=>"" ,
		"shippingCity"=>"" ,
		"shippingEmail"=>"" ,
		"shippingPhoneNumer"=>"" ,
		"deviceFingerprintID"=>$this->masSession,
		"payerName"=>"" ,
		"payerMobile"=>"" ,
		"payerEmail"=>"" ,
		"registerUserId"=>$customerUserInfo['entity_id'],
		"registerUserEmail"=>$customerUserInfo['email'],
		"registerTime"=>date('YmdHis',strtotime($customerUserInfo['created_at'])),
		"registerIp"=>"" ,
		"registerTerminal"=>"00" ,
		"orderIp"=>$this->getIP(),
		"orderTerminal"=>"00" ,
		"referer"=>"" ,	
		"ext3"=>"",
		"ext4"=>"",
		"signMsg"=>""
		);
		
		$serviceType = $mconfig['service_type'];
		if('physical'==$serviceType) {
			$params["shippingName"] = $this->shipfname.'//'.$this->shiplname;
			$params["shippingAdderess"] = $this->shipstreet.' '.$this->shipstreet2;
			$params["shippingPostalCode"] = str_replace( preg_split("/[0-9]/",$this->shipzip), null, $this->shipzip);
			$params["shippingcompany"] = "" ;
			$params["shippingCountry"] = $this->shipcountry;
			$params["shippingState"] = $this->shipstate;
			$params["shippingCity"] = $this->shipcity;
			$params["shippingEmail"] = $this->email;
			$params["shippingPhoneNumer"] = $this->shipphone;
		}		
		
		$signStr = "version=".$params["version"]."&"
				."merchantId=" .$params["merchantId"]."&"
				."signType=" .$params["signType"]."&"
				."merchantOrderNo=" .$params["merchantOrderNo"]."&"
				."currencyCode=".$params["currencyCode"]."&"
				."orderAmount=".$params["orderAmount"]."&"
				."submitTime=" .$params["submitTime"]."&"
				."cardNumber=" .$params["cardNumber"]."&"
				."cardExpirationMonth=" .$params["cardExpirationMonth"]."&"
				."cardExpirationYear=" .$params["cardExpirationYear"]."&"
				."securityCode=" .$params["securityCode"]."&"
				."key=".$mconfig['masapay_user_key'];
		
		if($mconfig['masapay_sign_type']=='MD5'){
			$params["signMsg"] = strtoupper(md5($signStr));
		}else{
			$params["signMsg"] = strtoupper(hash("sha256", $signStr));
		}

 		$server_url = $mconfig['sandbox']?"https://open-sandbox.masapay.com/masapi/receiveMerchantOrder.htm":"https://".$mconfig['specificlive'].".masapay.com/masapi/receiveMerchantOrder.htm";
		try {
			if(ini_get("default_socket_timeout") < 120) ini_set("default_socket_timeout", 120);
			$httpClient = new Varien_Http_Client();
			$httpClient->setUri($server_url);
			$httpClient->setConfig(array('timeout' => 30));
			$httpClient->setParameterPost($params);
			$httpClient->setMethod('POST');
			
			$response = $httpClient->request();
			$responseBody = $response->getBody();
			$this->masaRes = json_decode($responseBody);
			
			Mage::log("********acquiring result code of synchronized is : ".$this->masaRes->resultCode."********", null,"Masapay.log" );
			if("00"==$this->masaRes->resultCode||"10"==$this->masaRes->resultCode){
				$this->resultcode = "S";
				$this->masapayOrderNo = $this->masaRes->masapayOrderNo;
				//return true;
			}else if("11"==$this->masaRes->resultCode){
				$this->result="Error";
				$this->resultcode="F";
				$this->error=$this->masaRes->errMsg;
				$this->errorcode=$this->masaRes->errCode;
				$this->masapayOrderNo=$this->masaRes->masapayOrderNo;
				//return false;
			}else{
				Mage::log("********masapay result is unexpected:"." ".$this->masaRes->resultCode."********", null,"Masapay.log" );
				$this->resultcode="R";
			}
		}catch (Exception $e) {
			Mage::log("********exception occurs when requesting, order id :"."  ".$this->orderid." ".$e."********", null,"Masapay.log" );
			$this->resultcode="E";
		}
		
	}

	function xmlentities($string)
	{
		// $string = preg_replace('/[^a-zA-Z0-9 _\-\.\'\r\n]/e', '_uePrivateXMLEntities("$0")', $string);
		$string = preg_replace_callback('/[^a-zA-Z0-9 _\-\.\'\r\n]/', array('self', '_xmlEntitesReplaceCallback'), $string);
		return $string;
	}
	
	static protected function _xmlEntitesReplaceCallback($matches)
	{
		return self::_uePrivateXMLEntities($matches[0]);
	}

	static protected function _uePrivateXMLEntities($char)
	{
		$chars = array(
			128 => '&#8364;',
			130 => '&#8218;',
			131 => '&#402;',
			132 => '&#8222;',
			133 => '&#8230;',
			134 => '&#8224;',
			135 => '&#8225;',
			136 => '&#710;',
			137 => '&#8240;',
			138 => '&#352;',
			139 => '&#8249;',
			140 => '&#338;',
			142 => '&#381;',
			145 => '&#8216;',
			146 => '&#8217;',
			147 => '&#8220;',
			148 => '&#8221;',
			149 => '&#8226;',
			150 => '&#8211;',
			151 => '&#8212;',
			152 => '&#732;',
			153 => '&#8482;',
			154 => '&#353;',
			155 => '&#8250;',
			156 => '&#339;', 
			158 => '&#382;', 
			159 => '&#376;'
		);
		$num = ord($char);
		return (($num > 127 && $num < 160) ? $chars[$num] : "&#".$num.";" );
	}
}

function _uePrivateXMLEntities($num)
{	$chars = array(
    128 => '&#8364;',
    130 => '&#8218;',
    131 => '&#402;', 
    132 => '&#8222;',
    133 => '&#8230;',
    134 => '&#8224;',
    135 => '&#8225;',
    136 => '&#710;', 
    137 => '&#8240;',
    138 => '&#352;', 
    139 => '&#8249;',
    140 => '&#338;', 
    142 => '&#381;', 
    145 => '&#8216;',
    146 => '&#8217;',
    147 => '&#8220;',
    148 => '&#8221;',
    149 => '&#8226;',
    150 => '&#8211;',
    151 => '&#8212;',
    152 => '&#732;', 
    153 => '&#8482;',
    154 => '&#353;', 
    155 => '&#8250;',
    156 => '&#339;', 
    158 => '&#382;', 
    159 => '&#376;');
    $num = ord($num);
    return (($num > 127 && $num < 160) ? $chars[$num] : "&#".$num.";" );
}

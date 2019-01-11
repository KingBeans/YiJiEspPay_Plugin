<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/3
 * Time: 11:32
 */
class CHECKOUT_YIJIESPPAY extends ISC_CHECKOUT_PROVIDER
{
	private $_card_types = array(
		'VISA' => array(
			'type' => 'Visa',
			'regexp' => '^4[0-9]{15,18}$',
			'requiresCVV2' => true
		),
		'JCB' => array(
			'type' => 'JCB',
			'regexp' => '^35(2[8-9]|[3-8][0-9])[0-9]{12}$',
			'requiresCVV2' => true
		),
        'MasterCard' =>array(
            'type' => 'MasterCard',
            'regexp' => '^(5[1-5]\d{2})[\s\-]?(\d{4})[\s\-]?(\d{4})[\s\-]?(\d{4})$',
            'requiresCVV2' => true
        )
	);

	protected $paymentType = PAYMENT_PROVIDER_OFFLINE;

	/**
	 * @var boolean Does this provider support orders from more than one vendor?
	 */
	protected $supportsVendorPurchases = true;

	/**
	 * @var boolean Does this provider support shipping to multiple addresses?
	 */
	protected $supportsMultiShipping = true;

	/*
		Does this payment provider require SSL?
	*/
	protected $requiresSSL = true;

	/*
		Checkout class constructor
	*/
	public function __construct()
	{
		// Setup the required variables for the manual credit card module
		parent::__construct();
		$this->_name = GetLang('YiJiPayName');
        $this->_image = "yijipay.jpg";
        $this->_description = GetLang('YiJiPayDesc');
        $this->_help = sprintf(GetLang('YiJiPayHelp'), $GLOBALS['ShopPathSSL']);
       $this->_height = 0;
    }

	/*
	 * Check if this checkout module can be enabled or not.
	 *
	 * @return boolean True if this module is supported on this install, false if not.
	 */
	public function IsSupported()
	{
		if(!function_exists("mcrypt_encrypt")) {
			$this->SetError(GetLang('YiJiPayErrorNoMCrypt'));
		}
		else if(!GetConfig('UseSSL')) {
			$this->SetError(GetLang('YiJiPayNoSSLError'));
		}
//        var_dump($this->HasErrors());
		if($this->HasErrors()) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Custom variables for the checkout module. Custom variables are stored in the following format:
	* array(variable_id, variable_name, variable_type, help_text, default_value, required, [variable_options], [multi_select], [multi_select_height])
	* variable_type types are: text,number,password,radio,dropdown
	* variable_options is used when the variable type is radio or dropdown and is a name/value array.
	*/
	public function SetCustomVars()
	{
        $this->_variables['displayname'] = array(
            "name" => "Display Name",
            "type" => "textbox",
            "help" => GetLang('DisplayNameHelp'),
            "default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['gateway'] = array(
            "name" => "Gateway Url",
            "type" => "textbox",
            "help" => GetLang('YiJiPayGatewayUrlHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['webSite'] = array(
            "name" => "Web Site",
            "type" => "textbox",
            "help" => "您的网站链接，请跟上主机名；列如：www.google.com；www为主机名，google.com为域名。",
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['partnerId'] = array(
            "name" => "Partner Id",
            "type" => "textbox",
            "help" => GetLang('YiJiPayPartnerIdHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['userId'] = array(
            "name" => "User Id",
            "type" => "textbox",
            "help" => GetLang('YiJiPayUserIdHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );

        $this->_variables['secretKey'] = array(
            "name" => "File Password",
            "type" => "textbox",
            "help" => GetLang('YiJiPayFilePasswordHelp'),
            //"default" => $this->GetName(),
            "required" => true
        );
        $this->_variables['debugMode'] = array(
            "name" => "Debug mode",
            "type" => "dropdown",
            "help" => "choose the gateway online or test",
            "options"=>array(
                'true '=>'true',
                'false'=>'false'
            ),
            "required" => true
        );
        $this->_variables['acquiringType'] = array(
            "name" => "Acquiring Type",
            "type" => "dropdown",
            "help" => "收单类型：信用卡方式、网银方式，例如：CRDIT：信用卡；YANDEX： 网银",
            "options"=>array(
                '信用卡 '=>'CRDIT',
                '网银'=>'YANDEX'
            ),
            "required" => true
        );


        $this->_variables['jumpMode'] = array(
            "name" => "Jump Mode",
            "type" => "dropdown",
            "help" => GetLang('YiJiPayFilePasswordHelp'),
            //"default" => $this->GetName(),
            "options"=>array(
                'Internal '=>0,
                'Jump'=>1
            ),
            "required" => false
        );

        $this->_variables['Language'] = array(
            "name" => "payment language",
            "type" => "dropdown",
            "help" => GetLang('payment language'),
            //"default" => $this->GetName(),
            "options"=>array(
                'English'=>'en',
                'Japanese'=>'ja',
                'Deutsch'=>'de',
                'El español'=>'es',
                'Français'=>'fr'
            ),
            "required" => false
        );

        $this->_variables['Currency'] = array(
            "name" => "payment language",
            "type" => "dropdown",
            "help" => 'pelace select Currency',
            //"default" => $this->GetName(),
            "options"=>array(
                'RMB'=>'CNY',
                'United States dollar'=>'USD',
                'Canadian dollar'=>'CAD',
                'Hong Kong dollars'=>'HKD',
                'Europe dollars'=>'EUR',
                'United Kingdom dollars'=>'GBP',
                'Japan\'s dollars'=>'JPY',
            ),
            "required" => false
        );

		$this->_variables['displayname'] = array(
			"name" => "Display Name",
			"type" => "textbox",
			"help" => GetLang('DisplayNameHelp'),
			"default" => $this->GetName(),
			"required" => true
		);

//		$acceptedTypes = array();
//
//		foreach($this->_card_types as $type => $options) {
//			$acceptedTypes[$options['type']] = $type;
//		}
//		$defaultCardTypes = implode(",", array_keys($this->_card_types));
//
//		$this->_variables['acceptedcards'] = array(
//			"name" => "Accepted Card Types",
//			"type" => "dropdown",
//			"help" => GetLang('YiJiPayAcceptedCardTypesHelp'),
//			"default" => $defaultCardTypes,
//			"required" => true,
//			"options" => $acceptedTypes,
//			"multiselect" => true,
//			'multiselectheight' => 12
//		);
	}

	/**
	* ShowPaymentForm
	* Show a payment form for this particular gateway if there is one.
	* This is useful for gateways that require things like credit card details
	* to be submitted and then processed on the site.
	*/
	public function ShowPaymentForm()
	{
		$GLOBALS['YJMonths'] = "";
		$GLOBALS['YJYears'] = "";
		$GLOBALS['YJIssueDateMonths'] = $GLOBALS['YYIssueDateYears'] = '';
		$yj_type = "";

		// Get the credit card types
		if(isset($_POST['yj_cctype'])) {
			$yj_type = $_POST['yj_cctype'];
		}

		$GLOBALS['YJTypes'] = $this->_GetYJTypes($yj_type);

		for ($i = 1; $i <= 12; $i++) {
			$stamp = mktime(0, 0, 0, $i, 15, date("Y"));

			$i = str_pad($i, 2, "0", STR_PAD_LEFT);

			if (isset($_POST['yj_expm']) && $_POST['yj_ccexpm'] == $i) {
				$sel = 'selected="selected"';
			} else {
				$sel = "";
			}

			if(isset($_POST['yj_issuedatem']) && $_POST['yj_issuedatem'] == $i) {
				$issueSel = 'selected="selected"';
			}
			else {
				$issueSel = '';
			}

			$GLOBALS['YJMonths'] .= sprintf("<option %s value='%s'>%s</option>", $sel, $i, date("M", $stamp));
			$GLOBALS['YJIssueDateMonths'] .= sprintf("<option %s value='%s'>%s</option>", $issueSel, $i, date("M", $stamp));
		}

		for($i = date("Y"); $i <= date("Y")+10; $i++) {
			if(isset($_POST['yj_ccexpy']) && $_POST['yj_ccexpy'] == isc_substr($i, 2, 2)) {
				$sel = 'selected="selected"';
			}
			else {
				$sel = "";
			}
			$GLOBALS['YJYears'] .= sprintf("<option %s value='%s'>%s</option>", $sel, isc_substr($i, 2, 2), $i);
		}

		for($i = date("Y"); $i > date("Y")-5; --$i) {
			if(isset($_POST['yj_issuedatey']) && $_POST['yj_issuedatey'] == isc_substr($i, 2, 2)) {
				$sel = 'selected="selected"';
			}
			else {
				$sel = "";
			}
			$GLOBALS['YYIssueDateYears'] .= "<option value=\"".$i."\" ".$sel.">".$i."</option>";
		}

		// Grab the billing details for the order
		$billingDetails = $this->GetBillingDetails();

		$GLOBALS['YJName'] = isc_html_escape($billingDetails['ordbillfirstname'].' '.$billingDetails['ordbilllastname']);

		// Format the amount that's going to be going through the gateway
		$GLOBALS['OrderAmount'] = CurrencyConvertFormatPrice($this->GetGatewayAmount());

		// Was there an error validating the payment? If so, pre-fill the form fields with the already-submitted values
		if($this->HasErrors()) {
			$fields = array(
				"YJName" => 'yj_name',
				"YJNum" => 'yj_ccno',
				"YJIssueNo" => 'yj_issueno',
			);
			foreach($fields as $global => $post) {
				if(isset($_POST[$post])) {
					$GLOBALS[$global] = isc_html_escape($_POST[$post]);
				}
			}

			$yj_error = implode("<br />", $this->GetErrors());
			$GLOBALS['YJErrorMessage'] = $yj_error;
		}
		else {
			// Hide the error message box
			$GLOBALS['HideYYError'] = "none";
		}

		// Collect their details to send through to eWay
		$GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate("yijipay");
//        var_dump($GLOBALS['ISC_CLASS_TEMPLATE']);
		return $GLOBALS['ISC_CLASS_TEMPLATE']->ParseTemplate(true);
	}

	/**
	* ProcessPaymentForm
	* Process and validate input from a payment form for this particular
	* gateway.
	*
	* @return boolean True if valid details and payment has been processed. False if not.
	*/
	public function ProcessPaymentForm()
	{
        try{
            $requset_data = array();
            $orders = $this->GetOrders();
            list(,$order) = each($orders);
            $requset_url = $this->GetValue('gateway');

            $billingDetails = $this->GetBillingDetails();
            $requset_data = $this->getServiceInfo($order,$_POST,$billingDetails);
            $requset_data['goodsInfoList'] = json_encode($this->getGoodsInfoList());
            $requset_data['orderDetail'] = json_encode($this->getOrderDetail($order,$_POST));;
            $requset_data['sign'] = $this->getSignString($requset_data);

            $result = $this->curlRequest($requset_url,$requset_data);
            return $this->doReault($result);

        }catch (ErrorException $exception){
            file_put_contents(dirname(__FILE__) ."/logs/".$_POST['merchOrderNo']."_read.txt","[".date('Y-m-d H:i:s')." OrderInfo ]   ".json_encode($exception)."\n\n\n",FILE_APPEND);
            $this->SetError(GetLang('YiJiPaySystemError'));
            return false;
        }
	}

	/**
	* _CCEncrypt
	* Encrypt the credit card number before it's stored in the database
	*
	* @param Int $CCNo The credit card number
	* @return String The encrypted card number
	*/
	private function _CCEncrypt($CCNo)
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$crypt = mcrypt_encrypt(MCRYPT_BLOWFISH, GetConfig('EncryptionToken'), $CCNo, MCRYPT_MODE_ECB, $iv);
		$crypt = base64_encode($crypt);
		return $crypt;
	}

	private function _CCDecrypt($CCEnc)
	{
		$CCEnc = base64_decode($CCEnc);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypt = mcrypt_decrypt(MCRYPT_BLOWFISH, GetConfig('EncryptionToken'), $CCEnc, MCRYPT_MODE_ECB, $iv);
		$decrypt = rtrim($decrypt, "\0");
		return $decrypt;
	}

	/**
	* _GetCCTypes
	* Get a list of credit card types and return them as options
	*
	* @param String $Selected The selected card type if the form was already posted
	* @return String
	*/
	private function _GetYJTypes($Selected="")
	{
		$options = "";

		// Get the enabled credit card types
		$card_types = $this->GetValue("acceptedcards");

		if(!is_array($card_types)) {
			if($card_types != '') {
				$card_types = array($card_types);
			}
			else {
				$card_types = array_keys($this->_card_types);
			}
		}

		// sort the cards alphabetically
		uasort($card_types, array($this, 'compare_cards'));

		foreach($card_types as $type) {
			$card = $this->_card_types[$type];
			if($Selected == $type) {
				$sel = "selected=\"selected\"";
			}
			else {
				$sel = "";
			}

			$class = '';
			if($this->CardTypeRequiresCVV2($type)) {
				$class .= ' requiresCVV2';
			}

			if($this->CardTypeHasIssueNo($type)) {
				$class .= ' hasIssueNo';
			}

			if($this->CardTypeHasIssueDate($type)) {
				$class .= ' hasIssueDate';
			}

			$options .= sprintf("<option id='YJType_%s' class='%s' value='%s' %s>%s</option>", $type, $class, $type, $sel, $card['type']);
		}

		return $options;
	}

	/**
	 * Check if a particular credit card type requires a CVV2/CSV code.
	 *
	 * @param string The type of the credit card to check.
	 * @return boolean True if a CVV2 code is required.
	 */
	private function CardTypeRequiresCVV2($type)
	{
		if(isset($this->_card_types[$type]['requiresCVV2']) && $this->_card_types[$type]['requiresCVV2']) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Check if a particular credit card type can contain an issue code.
	 *
	 * @param string The type of the credit card to check.
	 * @return boolean True if an issue code.
	 */
	private function CardTypeHasIssueNo($type)
	{
		if(isset($this->_card_types[$type]['hasIssueNo']) && $this->_card_types[$type]['hasIssueNo']) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Check if a particular credit card type can contain an issue date.
	 *
	 * @param string The type of the credit card to check.
	 * @return boolean True if an issue date is allowable.
	 */
	private function CardTypeHasIssueDate($type)
	{
		if(isset($this->_card_types[$type]['hasIssueDate']) && $this->_card_types[$type]['hasIssueDate']) {
			return true;
		}
		else {
			return false;
		}
	}

	public function handleRemoteAdminRequest()
	{
		if (empty($_POST['orderId'])) {
			exit;
		}

		$order = getOrder($_POST['orderId']);
		$extraInfo = @unserialize($order['extrainfo']);
		if (empty($order) && !is_array($extraInfo)) {
			exit;
		}

		unset($extraInfo['yj_ccno']);
		unset($extraInfo['yj_cvv2']);
		unset($extraInfo['yj_name']);
		unset($extraInfo['yj_ccaddress']);
		unset($extraInfo['yj_cczip']);
		unset($extraInfo['yj_cctype']);
		unset($extraInfo['yj_ccexpm']);
		unset($extraInfo['yj_ccexpy']);

		if(isset($extraInfo['yj_issueno'])) {
			unset($extraInfo['yj_issueno']);
		}

		if(isset($extraInfo['yj_issuedatey'])) {
			unset($extraInfo['yj_issuedatey']);
			unset($extraInfo['yj_issuedatem']);
			unset($extraInfo['yj_issuedated']);
		}

		$updatedOrder = array(
			"extrainfo" => serialize($extraInfo)
		);
		$GLOBALS['ISC_CLASS_DB']->UpdateQuery("orders", $updatedOrder, "orderid='".$order['orderid']."'");
		echo 1;
	}

	/**
	* DipslayPaymentDetails
	* Show any additional payment settings that this payment provider may have
	* saved in to the orders table. This is shown on the orders quick view page.
	*
	* @param array The array of order information.
	* @return string Any additional data this payment provider may want to show
	*/
	public function DisplayPaymentDetails($order)
	{
		if($order['extrainfo'] == '') {
			return '';
		}

		$extraInfo = @unserialize($order['extrainfo']);

		if(!isset($extraInfo['yj_ccno'])) {
			return '';
		}

		$ccNo = $this->_CCDecrypt($extraInfo['yj_ccno']);

		$issueDetails = '';
		if(isset($extraInfo['yj_issueno'])) {
			$issueDetails = '<tr>
				<td class="text" valign="top">'.GetLang('CCManualCreditCardIssueNo').':</td>
				<td class="text">'.$this->_CCDecrypt($extraInfo['yj_issueno']).'</td>
			</tr>';
		}

		if(isset($extraInfo['yj_issuedatey'])) {
			$issueDetails .= '<tr>
				<td class="text" valign="top">'.GetLang('CCManualIssueDate').':</td>
				<td class="text">'.$extraInfo['yj_issuedatem'].'/'.$extraInfo['yj_issuedatey'].'</td>
			</tr>';
		}

		$details = '
			<script type="text/javascript">
			function ClearCreditCardDetails(orderid) {
				$.ajax({
					url: "remote.php?remoteSection=orders&w=checkoutModuleAction",
					data: {
						module: "creditcardmanually",
						orderId: orderid,
					},
					type: "post",
					success: function() {
						$("#CCDetails_"+orderid).remove()
					}
				});
			}
			</script>
			<div id="CCDetails_'.$order['orderid'].'">
				<br />
				<h5>'.GetLang('CreditCardDetails').'</h5>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="text" valign="top" width="120">'.GetLang('CCManualCardHoldersName').':</td>
					<td class="text">'.$extraInfo['yj_name'].'</td>
				</tr>
				<tr>
					<td class="text" valign="top">'.GetLang('CCManualCreditCardType').':</td>
					<td class="text">'.$extraInfo['yj_cctype'].'</td>
				</tr>
				<tr>
					<td class="text" valign="top">'.GetLang('CCManualCreditCardNo').':</td>
					<td class="text">'.$ccNo.'</td>
				</tr>
					'.$issueDetails.'
				<tr>
					<td class="text" valign="top">'.GetLang('CCManualExpirationDate').':</td>
					<td class="text">'.$extraInfo['yj_ccexpm'].'/'.$extraInfo['yj_ccexpy'].'</td>
//				</tr>
				<tr>
					<td class="text" colspan="2" align="right"><input type="button" class="SmallButton" value="'.GetLang('CCManualClearDetails').'" onclick="ClearCreditCardDetails('.$order['orderid'].');" />&nbsp;&nbsp;</td>
				</tr>
				</table>
			</div>
		';
		return $details;
	}

	/**
	 * Return a list of any manual payment fields that should be shown when creating/editing
	 * an order via the control panel, if any.
	 *
	 * @param array An array containing the details of existing values, if any.
	 * @return array An array of manual payment fields.
	 */
	public function GetManualPaymentFields($existingOrder=array())
	{
		$existingDetails = array(
			'yj_name' => '',
			'yj_cctype' => '',
			'yj_ccexpm' => '',
			'yj_ccexpy' => '',
			'yj_ccno' => '',
			'yj_issueno' => '',
			'yj_issuedatem' => '',
			'yj_issuedatey' => ''
		);

		if(isset($existingOrder['extrainfo']) && $existingOrder['extrainfo'] != '') {
			$extraInfo = @unserialize($existingOrder['extrainfo']);
			$existingDetails = array_merge($existingDetails, $extraInfo);
			if($existingDetails['yj_ccno']) {
				$existingDetails['yj_ccno'] = $this->_CCDecrypt($existingDetails['yj_ccno']);
			}
		}
		else if(isset($existingOrder['paymentMethod'][$this->GetId()])) {
			$existingDetails = array_merge($existingDetails, $this->GetId());
		}

		$monthOptions = '';
		$issueMonthOptions = '<option value="">&nbsp;</option>';
		for($i = 1; $i <= 12; $i++) {
			$stamp = mktime(0, 0, 0, $i, 15, date("Y"));
			$i = str_pad($i, 2, "0", STR_PAD_LEFT);

			$sel = '';
			if($existingDetails['yj_ccexpm'] == $i) {
				$sel = 'selected="selected"';
			}
			$monthOptions .= '<option value="'.$i.'" '.$sel.'>'.date('M', $stamp).'</option>';

			$sel = '';
			if($existingDetails['yj_issuedatem'] == $i) {
				$sel = 'selected="selected"';
			}
			$issueMonthOptions .= '<option value="'.$i.'" '.$sel.'>'.date('M', $stamp).'</option>';
		}

		$yearOptions = '';
		for($i = date("Y"); $i <= date("Y")+10; $i++) {
			$sel = '';
			$value = isc_substr($i, 2, 2);
			if($value == $existingDetails['yj_ccexpy']) {
				$sel = 'selected="selected"';
			}
			$yearOptions .= '<option value="'.$value.'" '.$sel.'>'.$i.'</option>';
		}

		$issueYearOptions = '<option value="">&nbsp;</option>';
		for($i = date("Y"); $i > date("Y")-5; --$i) {
			$sel = '';
			$value = isc_substr($i, 2, 2);
			if($value == $existingDetails['yj_issuedatey']) {
				$sel = 'selected="selected"';
			}
			$issueYearOptions .= '<option value="'.$value.'" '.$sel.'>'.$i.'</option>';
		}

		// the stored cc type is the descriptive name, need to get key from the types array
		$cctype = "";
		if ($existingDetails['yj_cctype']) {
			foreach ($this->_card_types as $key => $type) {
				if ($type['type'] == $existingDetails['yj_cctype']) {
					$cctype = $key;
					break;
				}
			}
		}
        //此处需要修改
		$cardOptions = $this->_GetYJTypes($cctype);
		$fields = array(
			'yj_name' => array(
				'type' => 'text',
				'title' => GetLang('CCManualCardHoldersName'),
				'value' => $existingDetails['yj_name'],
				'required' => true
			),
			'yj_cctype' => array(
				'type' => 'select',
				'title' => GetLang('CCManualCreditCardType'),
				'options' => $cardOptions,
				'onchange' => "
					if(\$(this).find('option:selected').is('.requiresCVV2')) {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_cvv2').show();
					}
					else {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_cvv2').hide();
					}

					if(\$(this).find('option:selected').is('.hasIssueNo')) {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_issueno').show();
					}
					else {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_issueno').hide();
					}

					if(\$(this).find('option:selected').is('.hasIssueDate')) {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_issuedate').show();
					}
					else {
						\$(this).parents('.paymentMethodForm').find('.Field_yj_issuedate').hide();
					}
				",
				'required' => true
			),
			'yj_ccno' => array(
				'type' => 'text',
				'title' => GetLang('CCManualCreditCardNo'),
				'value' => $existingDetails['yj_ccno'],
				'required' => true
			),
			'yj_expiry' => array(
				'type' => 'html',
				'title' => GetLang('CCManualExpirationDate'),
				'html' => '
					<select name="paymentField[checkout_creditcardmanually][yj_ccexpm]">'.$monthOptions.'</select>
					&nbsp;
					<select name="paymentField[checkout_creditcardmanually][yj_ccexpy]">'.$yearOptions.'</select>
				',
				'required' => true
			),
			'yj_issueno' => array(
				'type' => 'text',
				'title' => GetLang('CCManualCreditCardIssueNo'),
				'value' => $existingDetails['yj_issueno'],
				'required' => true
			),
			'yj_issuedate' => array(
				'type' => 'html',
				'title' => GetLang('CCManualIssueDate'),
				'html' => '
					<select name="paymentField[checkout_creditcardmanually][yj_issuedatem]">'.$issueMonthOptions.'</select>
					&nbsp;
					<select name="paymentField[checkout_creditcardmanually][yj_issuedatey]">'.$issueYearOptions.'</select>
				',
				'required' => true
			)
		);

		return $fields;
	}

	/**
	 * Save the manual payment fields for this checkout provider.
	 *
	 * @param array The information about the order.
	 * @param array An array of fields for this module that were passed back.
	 */
	public function ProcessManualPayment($order, $data)
	{
		$cctype = $data['yj_cctype'];
		$cardVars = array(
			'yj_name'		=> $data['yj_name'],
			'yj_cctype'		=> $this->_card_types[$cctype]['type'],
			'yj_ccno'		=> $this->_CCEncrypt($data['yj_ccno']),
			'yj_ccexpm'		=> $data['yj_ccexpm'],
			'yj_ccexpy'		=> $data['yj_ccexpy']
		);

		if($this->CardTypeHasIssueNo($cctype)) {
			$cardVars['yj_issueno'] = $this->_CCEncrypt($data['yj_issueno']);
		}

		if($this->CardTypeHasIssueDate($cctype)) {
			$cardVars['yj_issuedatem'] = (int)$data['yj_issuedatem'];
			$cardVars['yj_issuedatey'] = (int)$data['yj_issuedatey'];
		}

		if($order['extrainfo'] != "") {
			$extraInfo = @unserialize($order['extrainfo']);
			if(is_array($extraInfo)) {
				$extraInfo = @array_merge($extraInfo, $cardVars);
			}
		}
		else {
			$extraInfo = serialize($cardVars);
		}

		$updatedOrder = array(
			"extrainfo" => serialize($extraInfo)
		);
		$GLOBALS['ISC_CLASS_DB']->UpdateQuery("orders", $updatedOrder, "orderid='".(int)$order['orderid']. "'");
		return array(
			'result' => true,
			'amount' => $order['total_inc_tax'],
		);
	}

	private function getServiceInfo($order , $post , $billingDetails){
        $CVV = $post['yj_cvv2'];
        $cardNo = $post['yj_ccno'];
        $expirationDate = $post['yj_ccexpy'].$post['yj_ccexpm'];
        $orderNo = date('YmdHis').mt_rand(1000000,9999999);
        $requset_data = array();
        $requset_data['service'] = 'espPciOrderPay';
        $requset_data['version'] = '3.0';
        $requset_data['partnerId'] = $this->GetValue('partnerId');
        $requset_data['userId'] = $this->GetValue('userId');
        $requset_data['signType'] = 'RSA';
        $requset_data['orderNo'] = $orderNo;
        $requset_data['webSite'] = $this->GetValue('webSite');
        $requset_data['merchOrderNo'] = $this->GetCombinedOrderId();
        $requset_data['currency'] = GetCurrencyCodeByID($this->GetCurrency());
        $requset_data['amount'] = number_format($this->GetGatewayAmount() * $order['ordcurrencyexchangerate'],2,'.','');
        $requset_data['acquiringType'] = $this->GetValue('acquiringType');
        $requset_data['cardNo'] = $this->publicKeyEncryption($cardNo.$orderNo);
        $requset_data['cvv'] = $this->publicKeyEncryption($CVV.$orderNo);
        $requset_data['expirationDate'] = $expirationDate;
        $requset_data['cardHolderFirstName'] = $billingDetails['ordbillfirstname'];
        $requset_data['cardHolderLastName'] = $billingDetails['ordbilllastname'];

//        session_start();
        $requset_data['deviceFingerprintId'] = session_id();
        $requset_data['returnUrl'] = GetConfig('ShopPathSSL').'/yiji_return.php';
        $requset_data['notifyUrl'] = GetConfig('ShopPathSSL').'/yiji_return.php';
        $requset_data['context'] = '{"action":"gateway_ping","provider":"'.$this->GetId().'"}';
        $requset_data['language'] = $this->GetValue('Language');
        return $requset_data;
    }

    /**
     * @return array
     * 获取商品列表
     */
    private function getGoodsInfoList(){
        $get_order_products = " SELECT `ordprodid`,`ordprodname`,`ordprodqty`,`base_price` FROM [|PREFIX|]order_products WHERE orderorderid = ".$this->GetCombinedOrderId();

        $result = $GLOBALS['ISC_CLASS_DB']->Query($get_order_products);
        $result_data = array();
        while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
            $result_data[] = $row;
        }
        $orderGoodsInfo = array();
        $logisticsFee = 0;
        foreach ($result_data as $k=>$v){
            $orderGoodsInfo[$k]['goodsNumber'] = $v['ordprodid'];
            $orderGoodsInfo[$k]['goodsName'] = $v['ordprodname'];
            $orderGoodsInfo[$k]['goodsCount'] = $v['ordprodqty'];
            $orderGoodsInfo[$k]['itemSharpProductcode'] = mt_rand(1,100);
            $orderGoodsInfo[$k]['itemSharpUnitPrice'] = $v['base_price'];
            //$logisticsFee = $v['shipping_cost_inc_tax'];
        }

        return $orderGoodsInfo;
    }

    /**
     * @return array
     * 获取订单详情
     */
    private function getOrderDetail($order,$post){
        $get_order_logistics = 'SELECT `base_cost`,`method` FROM [|PREFIX|]order_shipping WHERE order_id = '.$this->GetCombinedOrderId();
        $order_logistics_result = $GLOBALS['ISC_CLASS_DB']->Query($get_order_logistics);
        $order_logistics_data = array();
        while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($order_logistics_result)){
            $order_logistics_data[] = $row;
        }

        $billingDetails = $this->GetBillingDetails();
        $ShippingAddress = $this->getShippingAddress();
        $orderDetail = array(
            'ipAddress'=>$this->getIp(),
//            'ipAddress'=>'113.204.226.234',
            'billtoCountry'=>$billingDetails['ordbillcountrycode'],
            'billtoState'=>$billingDetails['ordbillstate'] ? $billingDetails['ordbillstate'] : $billingDetails['ordbillcountrycode'],
            'billtoCity'=>$billingDetails['ordbillsuburb'],
            'billtoPostalcode'=>$billingDetails['ordbillzip'],
            'billtoEmail'=>$billingDetails['ordbillemail'],
            'billtoFirstname'=>$billingDetails['ordbillfirstname'],
            'billtoLastname'=>$billingDetails['ordbilllastname'],
            'billtoPhonenumber'=>$billingDetails['ordbillphone'],
            'billtoStreet'=>$billingDetails['ordbillstreet1'],
            'shiptoCity'=>$ShippingAddress['city'],
            'shiptoCountry'=>$ShippingAddress['country_iso2'],
            'shiptoFirstname'=>$ShippingAddress['first_name'],
            'shiptoLastname'=>$ShippingAddress['last_name'],
            'shiptoEmail'=>$billingDetails['ordbillemail'],
            'shiptoPhonenumber'=>$ShippingAddress['phone'],
            'shiptoPostalcode'=>$ShippingAddress['zip'],
            'shiptoState'=>$ShippingAddress['state'] ? $ShippingAddress['state'] : $ShippingAddress['country_iso2'],
            'shiptoStreet'=>$ShippingAddress['address_1'],
            #物流费
//            'logisticsFee'=>$order_logistics_data[0]['base_cost'],
            'logisticsFee'=>number_format($order_logistics_data[0]['base_cost'] * $order['ordcurrencyexchangerate'],2,'.',''),
            #物流方式
            'logisticsMode'=>$order_logistics_data[0]['method'],
            'customerEmail'=>$billingDetails['ordbillemail'],
            'customerPhonenumber'=>$ShippingAddress['phone'],
            'cardType' => $this->getCardTypeByCardNum($post['yj_ccno'])
        );
        return $orderDetail;
    }


    /**
     * 获取用户的IP地址
     */
    function getIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }else if(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }else{
            $cip = "127.0.0.1";
        }
        return $cip;
    }

    private function compare_cards($a, $b)
    {
        $a = $this->_card_types[$a];
        $b = $this->_card_types[$b];

        return strcasecmp($a['type'], $b['type']);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     * curl方式请求
     */
    private function curlRequest($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    /**
     * @param $data
     * @return string
     * cardNo/CVV encryption
     */
    function publicKeyEncryption($data){
        if ($this->GetValue('debugMode') === 'true'){
            $public_key_path =  __DIR__.'/yjf-cert-2048.pem';
        }else{
            $public_key_path =  __DIR__.'/yjf-online-2048.pem';
        }
        file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[pem_path_".date('Y-m-d H:i:s')." ](publicPath):\n"."publicPath:".$public_key_path."\n\n",FILE_APPEND);
        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));
        openssl_public_encrypt(str_pad($data, 256, "\0", STR_PAD_LEFT), $encryptedData, $public_key, OPENSSL_NO_PADDING);
        return base64_encode($encryptedData);
    }

    /**
    * 更具参数获取sign加密值这里主要使用RSA算法加密
    */
    public function getSignString(array $items){
        ksort($items);
        $signString = '';
        foreach ($items as $k => $v){
//            $signString .= '&'.$k.'='.$v;
            $signString.= $k.'='.$v.'&';
        }

        $signSrc = trim(substr($signString, 0, -1));
        $pfxPath = __DIR__.'/'.$items['partnerId'].'.pfx';
        $keyPass = $this->GetValue('secretKey');
        $pkcs12 = file_get_contents($pfxPath);
        if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
            $privateKey = $certs['pkey'];
            $signedMsg = "";
            if (openssl_sign($signSrc, $signedMsg, $privateKey)) {
                $sign = base64_encode($signedMsg);
            } else {
                return '加密失败';
            }
        } else {
            return '文件解析失败';
        }
        file_put_contents(dirname(__FILE__) . "/logs/read.txt","[".date('Y-m-d H:i:s')." 代签名字符串 ]   ".$signSrc.' \r\n     this->GetId():'.$this->GetId(),FILE_APPEND);
        return $sign;
//        return md5($signString.$this->GetValue('secretKey'));
    }

    /**
     * 校验信用卡卡号是否有效
     *
     * @param
     *        	$cardNum
     * @return String
     */
    private function getCardTypeByCardNum($cardNum) {
        $cardType = "";
        $left = substr ( $cardNum, 0, 2 );
        if ($left >= 40 && $left <= 49) {
            $cardType = "Visa";
        } else if ($left >= 50 && $left <= 59) {
            $cardType = "MasterCard";
        } else if ($left == 35) {
            $cardType = "JCB";
        }else{
            $this->SetError(GetLang('CCManualBadCardType'));
            return false;
        }
        return $cardType;
    }

    private function doReault($result){

//        return true;
        $resultObj = json_decode($result);
        //处理返回
        file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"\r\n".date('Y-m-d H:i:s ')."异步请求参数:".json_encode($_POST),FILE_APPEND);
        if ($resultObj->success){
            $order_info = GetOrder($resultObj->merchOrderNo);
            $extra  = array("tradeNo"=>$resultObj->merchOrderNo,"orderNo"=>$resultObj->merchOrderNo,"orderAmount"=>$order_info['total_inc_tax'],"orderCurrency"=>$this->GetValue('Currency'),"orderStatus"=>'');
            $newTransaction = array(
                'providerid' => $this->GetId(),
                'transactiondate' => time(),
                'transactionid' => $this->GetCombinedOrderId(),
                'orderid' => array_keys($this->GetOrders()),
                'message' => '',
                'status' => '',
                'amount' => $order_info['total_inc_tax'],
                'extrainfo' => array()
            );
            # 2、获取当前订单状态
            $transaction = GetClass('ISC_TRANSACTION');
            $order_status = ORDER_STATUS_AWAITING_PAYMENT;
            if($resultObj->resultCode == 'EXECUTE_SUCCESS'){
                # 3、查看订单回调的订单状态
                switch ($resultObj->status){
                    case 'processing':
                        $extra['orderStatus'] = ORDER_STATUS_AWAITING_SHIPMENT;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('YiJiPayProcessing'), $extra);
                        $newTransaction['status'] = TRANS_STATUS_COMPLETED;
                        break;
                    case 'success':
                        $extra['orderStatus'] = ORDER_STATUS_AWAITING_SHIPMENT;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('YiJiPaySuccess'), json_encode($extra));
                        $newTransaction['status'] = TRANS_STATUS_COMPLETED;
                        $order_status = ORDER_STATUS_AWAITING_SHIPMENT;
                        $this->SetPaymentStatus(PAYMENT_STATUS_PAID);
                        EmptyCartAndKillCheckout();
                        break;
                    case 'fail':
                        $order_status = ORDER_STATUS_DECLINED;
                        $newTransaction['status'] = TRANS_STATUS_FAILED;
                        $extra['orderStatus'] = ORDER_STATUS_DECLINED;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemError(array('payment', $this->GetName()), GetLang('YiJiPayFail'), $extra);
                        break;
                    case 'authorizing':
                        $order_status = ORDER_STATUS_AWAITING_FULFILLMENT;
                        $extra['orderStatus'] = ORDER_STATUS_AWAITING_FULFILLMENT;
                        $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('YiJiPaySuccess'), $extra);
                        EmptyCartAndKillCheckout();
                        break;

                }

                $newTransaction['message'] = $result;

                $transactionId = $transaction->Create($newTransaction);
                $orderInfo = array($resultObj->merchOrderNo=>GetOrder($resultObj->merchOrderNo));
                foreach ($orderInfo as $orderId => $order) {
                  UpdateOrderStatus($orderId, $order_status);

                }
//                $this->SetPaymentStatus(PAYMENT_STATUS_PENDING);
                return true;
            }
            $this->SetError(GetLang('YiJiPayPaymentfail'));
            return false;
        }
        $this->SetError(GetLang('YiJiPaySystemError'));
        return false;
    }
    /**
    * 预授权操作
    */
    public function authorizingAction($orderNo,$resolveReason,$isAccept = 'true'){
        //$get_orderinfo = " SELECT `ordpayproviderid` FROM [|PREFIX|]orders WHERE orderid  = ".$orderNo;

        //$result = $GLOBALS['ISC_CLASS_DB']->Query($get_orderinfo);
        //$result_data = array();
        //while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)){
         //   $result_data[] = $row;
        //}
        //var_dump($result_data);exit;
        $request_data = array();
        $request_data['merchOrderNo'] = $orderNo;
        $request_data['isAccept'] = $isAccept;
        $request_data['resolveReason'] = $resolveReason;

        $request_data['service'] = 'espOrderJudgment';
        $request_data['version'] = '1.0';
        $request_data['partnerId'] = $this->GetValue('partnerId');
        $request_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $request_data['signType'] = 'RSA' ;
        $request_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
        $requset_data['context'] = '{"action":"gateway_ping","provider":"'.$this->GetId().'"}';
        $requset_data['notifyUrl'] = GetConfig('ShopPathSSL').'/yiji_return.php';
//        $request_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();
        $signType = $this->getSignString($request_data);
        $request_data['sign'] = $signType;
        //var_dump($request_data);

        $resource = $this->vpost($this->GetValue('gateway'),$request_data);
        $resource_data = json_decode($resource,1);

        if($resource_data['resultCode'] == 'EXECUTE_SUCCESS'){
            $updatedOrder = array();
            if($resource_data['status'] == 'success' && $isAccept == 'true'){
                $updatedOrder['ordpaymentstatus']  = 'success';
                $updatedOrder['ordstatus']  = 9;
            }

            if($resource_data['status'] == 'success' && $isAccept == 'false'){
                $updatedOrder['ordpaymentstatus']  = 'fail';
                $updatedOrder['ordstatus']  = 5;
            }

            $result = $GLOBALS['ISC_CLASS_DB']->UpdateQuery('orders', $updatedOrder, "orderid='".(int)$orderNo."'");
            return $result ? array('status'=>true) : array('status'=>false) ;
        }else{
            return array('status'=>false);
        }

    }

    /**
    * 退款操作
    *
    */
    public function refundAction($OrderNo,$refundAmount,$refundReason){
        $request_data['merchOrderNo'] = $OrderNo;
        $request_data['originalOrderNo'] = $OrderNo;
        $request_data['refundAmount'] = $refundAmount;
        $request_data['refundReason'] = $refundReason;

        $request_data['service'] = 'espRefund';
        $request_data['version'] = '1.0';
        $request_data['partnerId'] = $this->GetValue('partnerId');
        $request_data['orderNo'] = date('YmdHis').mt_rand(1000000,9999999);
        $request_data['signType'] = 'RSA' ;
        $request_data['returnUrl'] = GetConfig('ShopPathSSL').'/finishorder.php';
        $requset_data['notifyUrl'] = GetConfig('ShopPathSSL').'/yiji_return.php';
//        $request_data['notifyUrl'] = GetConfig('ShopPathSSL').'/checkout.php?action=gateway_ping&provider='.$this->GetId();
        $signType = $this->getSignString($request_data);
        $request_data['sign'] = $signType;
        echo $this->vpost($this->GetValue('gateway'),$request_data);
    }
}

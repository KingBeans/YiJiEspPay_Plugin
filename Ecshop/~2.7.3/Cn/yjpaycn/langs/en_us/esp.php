<?php

global $_LANG;


$_LANG['yjpaycn'] 		= 'YJF';
$_LANG['yjpaycn_desc'] 	= 'Yijifu Techology CO,LTD is a company that offers international credit card payment service , certifatied by QSP(Visa) .';
$_LANG['yjpaycn_submit'] 	= 'Use YJF for payment';


$_LANG['yjpaycn_cfg_merchant_name'] 	= 'Merchant_name';//
$_LANG['yjpaycn_cfg_merchant_email'] 	= 'Merchant email';//
$_LANG['yjpaycn_cfg_partner_id'] 		= 'Partner id';//
$_LANG['yjpaycn_cfg_secret_key'] 		= 'Security code';//

$_LANG['yjpaycn_cfg_debug']		= 'Debug mode';//
$_LANG['yjpaycn_cfg_debug_range'] = array(0 => 'No','1' => 'Yes');//No Yes

$_LANG['yjpaycn_cfg_currency'] = 'Currency';// 
$_LANG['yjpaycn_cfg_currency_range'] = array(
    'CNY' => 'YUAN',//
    'USD' => 'DOLLAR',//
    'JPY' => 'YEN'//
);

// aquiring type
$_LANG['yjpaycn_cfg_payment_type'] = 'Payment Type';// 
$_LANG['yjpaycn_cfg_payment_type_range'] = array(
    'CRDIT'  => 'Credit Card',//
    'YANDEX' => 'E-Bank',
);


$_LANG['yjpaycn_errors'] = array(
	'order_empty' 		=> 'Payment has completed or has not used YJF for payment.',//
	'number_empty' 		=> 'Credit card number cannot be empty',//
	'holder_empty' 		=> 'Cardholder name cannot be empty',//
	'expired_empty' 	=> 'Date of expiry cannot be empty',// 
	'cvv_empty'			=> 'Code cannnot be empty',//
	
	'country_empty'		=> 'Country cannot be empty',//
	'state_empty'		=> 'State cannot be empty',//
	'city_empty'		=> 'City cannot be empty',//
	'address_empty'		=> 'Address cannot be empty',//
	'zipcode_empty' 	=> 'Zip code cannot be empty',//
	'email_empty'		=> 'Email cannot be empty',//
	'phone_empty'		=> 'Phone cannot be empty',//
		
	'number_invalid'	=> 'Card No.is invalid',//
	'email_format'		=> 'Email format is not correct',// 
	'cvv_format'		=> 'CVV format is not correct',// 
	
	'refund'			=> 'Payment has not been completed or has been refunded.'// 
);


$_LANG['yjpaycn_page'] = array(
	'page_title' 	=> 'YJF-Crossborder payment Platform',//
	
	'order_total' 	=> 'Total price:',//
    'order_no'		=> 'Order number:',//
	
    'card_title' 	=> 'Card information',//
    'card_info' 	=> 'Card Type Available:',//
	
    'number' 		=> '<span class="required">*</span>Card Number:',//
    'holder' 		=> '<span class="required">*</span>Card Holder:',//
    'expired_date' 	=> '<span class="required">*</span>Expire date:',//
    'security_code' => '<span class="required">*</span>Security code:',//
	
    'billing_title' => 'Billing Address',
    'country' 		=> '<span class="required">*</span>Country:',//
    'state' 		=> '<span class="required">*</span>State:',//
    'city' 			=> '<span class="required">*</span>City:',//
    'address' 		=> '<span class="required">*</span>Address:',//
	
    'post_code' 	=> '<span class="required">*</span>Zip code:',//
    'email' 		=> '<span class="required">*</span>Email:',//
    'phone_number' 	=> '<span class="required">*</span>Phone number:',//

    'submit' => 'Submit'//
);

$_LANG['yjpaycn_refund'] = array(
	'empty' 		=> 'Wrong order or this order has been refunded',//
	'page_title'	=> 'Refund',//
	'summary'		=> 'According to bank settlement law,if order has been made the same day before 0 o\'clock ,we just accepet cancel order ,refund is not permitted.',//
	'amountLoc'		=> 'Amount',//
	'tradeTime'		=> 'Trade time',//
	'usableRefundMoney' => 'Refunded',//
	'refund_money'		=> 'Refund money',//
	'note'				=> 'Note',//
	
	'refund_button'		=> 'Refund',//
	'cancel_button'		=> 'Cancel',//
	'reback'			=> 'Back',//
	
	'refund_money_empty'  => 'Refund money cannot be empty',//
	'refund_money_format' => 'Refund money format is not correct'//
);


$_LANG['yjpaycn_authorize'] = array(
	'empty' 		=> 'Wrong order or this order has been authorized',//
	'page_title'	=> 'Authorize',//
	'summary'		=> 'Be aware of payment risks ,please confirm to finish this order',//
	'amountLoc'		=> 'Amount',//
	'tradeTime'		=> 'Trade time',//
	'note'			=> 'Note',//
	
	'allow_button'		=> 'Accept',//
	'deny_button'		=> 'Refuse',//
	'reback'			=> 'Retrun',//
	
	'authorize_empty'  => 'Authorize cannot be empty'//
);

$_LANG['yjpaycn_pay_ret_title'] = <<<EOF
    Your order has been received
EOF;

$_LANG['yjpaycn_wait_notify'] = <<<EOF
	Payment has been submitted,please wait.<script type="text/javascript" src="yjpaycn.php?order_sn=%s"></script>
EOF;

$_LANG['yjpaycn_pay_success'] = <<<EOF
	Payment success: <a href="yjpaycn.php?order_sn=%s&act=refund">Refund</a>
EOF;

$_LANG['user_yjpaycn_success_title'] = <<<EOF
    Thank you for your purchase!We are processing your order and you will soon receive an email with details of the order. Once the order has shipped you will receive another email with a link to track its progress.
EOF;

$_LANG['yjpaycn_pay_fail'] = <<<EOF
	Payment fail.
EOF;

$_LANG['yjpaycn_wait_authorize'] = <<<EOF
	This payment contains some risks ,authorization needed.(%s): <a href="yjpaycn.php?order_sn=%s&act=authorize">authorization</a>
EOF;

$_LANG['yjpaycn_refund_success'] = <<<EOF
	<span style="color:#EE0000;">Refund(%s):%s</span>
EOF;

$_LANG['yjpaycn_refund_fail'] = <<<EOF
	<span style="color:#EE0000;">Refund fail:%s</span>
EOF;


$_LANG['yjpaycn_authorize_success'] = <<<EOF
	<span style="color:#EE0000;">Authorization processing(%s):%s</span>
EOF;

$_LANG['yjpaycn_authorize_fail'] = <<<EOF
	Authorize fail:%s
EOF;

$_LANG['yjpaycn_countries'] = array(
    'ALB' => 'Albania',
    'DZA' => 'Algeria',
    'AFG' => 'Afghanistan',
    'ARG' => 'Argentina',
    'ARE' => 'United Arab Emirates',
    'ABW' => 'Aruba',
    'OMN' => 'Oman',
    'AZE' => 'Azerbaijan',
    'EGY' => 'Egypt',
    'ETH' => 'Ethiopia',
    'IRL' => 'Ireland',
    'EST' => 'Estonia',
    'AND' => 'Andorra',
    'AGO' => 'Angola',
    'AIA' => 'Anguilla',
    'ATG' => 'Antigua and Barbuda',
    'AUT' => 'Austria',
    'AUS' => 'Australia',
    'MAC' => 'Macau',
    'BRB' => 'Barbados',
    'PNG' => 'Papua New Guinea',
    'BHS' => 'Bahamas',
    'PAK' => 'Pakistan',
    'PRY' => 'Paraguay',
    'PSE' => 'Palestine',
    'BHR' => 'Bahrain',
    'PAN' => 'Panama',
    'BRA' => 'Brazil',
    'BLR' => 'Belarus',
    'BMU' => 'Bermuda',
    'BGR' => 'Bulgaria',
    'MNP' => 'Northern Marianas',
    'PLW' => 'Palau',
    'BEN' => 'Benin',
    'BEL' => 'Belgium',
    'ISL' => 'Iceland',
    'PRI' => 'Puerto Rico',
    'POL' => 'Poland',
    'BOL' => 'Bolivia',
    'BIH' => 'Bosnia and Herzegovina',
    'BWA' => 'Botswana',
    'BLZ' => 'Belize',
    'BTN' => 'Bhutan',
    'BFA' => 'Burkina Faso',
    'BDI' => 'Burundi',
    'BVT' => 'Bouvet Island',
    'PRK' => 'Korea,Democratic People\'s Republic of',
    'GNQ' => 'Equatorial Guinea',
    'DNK' => 'Denmark',
    'DEU' => 'Germany',
    'TMP' => 'East Timor',
    'TGO' => 'Togo',
    'DOM' => 'Dominican Republic',
    'DMA' => 'Dominica',
    'RUS' => 'Russia Federation',
    'ECU' => 'Ecuador',
    'ERI' => 'Eritrea',
    'FRA' => 'France',
    'FRO' => 'Faroe Islands',
    'PYF' => 'French Polynesia',
    'GUF' => 'French Guiana',
    'ATF' => 'French Southern Territo - ries',
    'VAT' => 'Vatican',
    'PHL' => 'Philippines',
    'FJI' => 'Fiji',
    'FIN' => 'Finland',
    'CPV' => 'Cape Verde',
    'GMB' => 'Gambia',
    'COD' => 'Congo, the Democratic Republic of the',
    'COG' => 'Congo',
    'COL' => 'Colombia',
    'CRI' => 'Costa Rica',
    'GRD' => 'Grenada',
    'GRL' => 'Greenland',
    'GEO' => 'Georgia',
    'CUB' => 'Cuba',
    'GLP' => 'Guadeloupe',
    'GUM' => 'Guam',
    'GUY' => 'Guyana',
    'KAZ' => 'Kazakhstan',
    'HTI' => 'Haiti',
    'KOR' => 'Korea,Republic of',
    'NLD' => 'Netherlands',
    'ANT' => 'Netherlands Antilles',
    'HMD' => 'Heard islands and Mc Donald Islands',
    'HND' => 'Honduras',
    'KIR' => 'Kiribati',
    'DJI' => 'Djibouti',
    'KGZ' => 'Kyrgyzstan',
    'GIN' => 'Guinea',
    'GNB' => 'Guine - bissau',
    'CAN' => 'Canada',
    'GHA' => 'Ghana',
    'GAB' => 'Gabon',
    'KHM' => 'Cambodia',
    'CZE' => 'Czech Repoublic',
    'ZWE' => 'Zimbabwe',
    'CMR' => 'Cameroon',
    'QAT' => 'Qatar',
    'CYM' => 'Cayman Islands',
    'CCK' => 'Cocos(Keeling) Islands',
    'COM' => 'Comoros',
    'CIV' => 'Cote d\'Ivoire',
    'KWT' => 'Kuwait',
    'HRV' => 'Croatia',
    'KEN' => 'Kenya',
    'COK' => 'Cook Islands',
    'LVA' => 'Latvia',
    'LSO' => 'Lesotho',
    'LAO' => 'Lao',
    'LBN' => 'Lebanon',
    'LBR' => 'Liberia',
    'LBY' => 'Libya',
    'LTU' => 'Lithuania',
    'LIE' => 'Liechtenstein',
    'REU' => 'Reunion',
    'LUX' => 'Luxembourg',
    'RWA' => 'Rwanda',
    'ROM' => 'Romania',
    'MDG' => 'Madagascar',
    'MLT' => 'Malta',
    'MDV' => 'Maldives',
    'FLK' => 'Falkland Islands',
    'MWI' => 'Malawi',
    'MYS' => 'Malaysia',
    'MLI' => 'Mali',
    'MKD' => 'MACEDONIA,THE FORMER YUGOSLAV REPUBLIC OF',
    'MHL' => 'Marshall Islands',
    'MTQ' => 'Martinique',
    'MYT' => 'Mayotte',
    'MUS' => 'Mauritius',
    'MRT' => 'Mauritania',
    'USA' => 'United States',
    'ASM' => 'American Samoa',
    'UMI' => 'United States Minor Outlying Islands',
    'VIR' => 'United States Virgin Is - lands',
    'MNG' => 'Mongolia',
    'MSR' => 'Montserrat',
    'BGD' => 'Bangladesh',
    'PER' => 'Peru',
    'FSM' => 'Micronesia，Federated states of',
    'MMR' => 'Myanmar',
    'MDA' => 'Moldova',
    'MAR' => 'Morocco',
    'MCO' => 'Monaco',
    'MOZ' => 'Mozambique',
    'MEX' => 'Mexico',
    'NAM' => 'Namibia',
    'ZAF' => 'South Africa',
    'ATA' => 'Antarctica',
    'SGS' => 'South Georgia and South Sandwich Islands',
    'NRU' => 'Nauru',
    'NPL' => 'Nepal',
    'NIC' => 'Nicaragua',
    'NER' => 'Niger',
    'NGA' => 'Nigeria',
    'NIU' => 'Niue',
    'NOR' => 'Norway',
    'NFK' => 'Norfolk Island',
    'PCN' => 'Pitcairn',
    'PRT' => 'Portugal',
    'JPN' => 'Japan',
    'SWE' => 'Sweden',
    'CHE' => 'Switzerland',
    'SLV' => 'El Salvador',
    'SLE' => 'Sierra leone',
    'SEN' => 'Senegal',
    'CYP' => 'Cyprus',
    'SYC' => 'Seychells',
    'SAU' => 'Saudi Arabia',
    'CXR' => 'Christmas Island',
    'STP' => 'Sao Tome and Principe',
    'SHN' => 'Saint helena',
    'KNA' => 'Saint Kitts and nevis',
    'LCA' => 'Saint lucia',
    'SMR' => 'San Marion',
    'SPM' => 'Saint Pierre and Miquelon',
    'VCT' => 'Saint Vincent and the Grenadines',
    'LKA' => 'Sri Lanka',
    'SVK' => 'Slovakia',
    'SVN' => 'Slovenia',
    'SJM' => 'Svalbard and jan Mayen Islands',
    'SWZ' => 'Swaziland',
    'SDN' => 'Sudan',
    'SUR' => 'Suriname',
    'SOM' => 'Somalia',
    'SLB' => 'Solomon Islands',
    'TJK' => 'Tajikistan',
    'THA' => 'Thailand',
    'TZA' => 'Tanzania',
    'TON' => 'Tonga',
    'TCA' => 'Turks and Caicos Islands',
    'TTO' => 'Trinidad and Tobago',
    'TUN' => 'Tunisia',
    'TUV' => 'Tuvalu',
    'TUR' => 'Turkey',
    'TKM' => 'Turkmenistan',
    'TKL' => 'Tokelau',
    'WLF' => 'Wallis and Futuna',
    'VUT' => 'Vanuatu',
    'GTM' => 'Guatemala',
    'VEN' => 'Venezuela',
    'BRN' => 'Brunei Darussalam',
    'UGA' => 'Uganda',
    'UKR' => 'Ukraine',
    'URY' => 'Uruguay',
    'UZB' => 'Uzbekistan',
    'ESP' => 'Spain',
    'ESH' => 'Western Sahara',
    'WSM' => 'Samoa',
    'GRC' => 'Greece',
    'HKG' => 'Hong Kong',
    'SGP' => 'Singapore',
    'NCL' => 'New Caledonia',
    'NZL' => 'New Zealand',
    'HUN' => 'Hungary',
    'SYR' => 'Syria',
    'JAM' => 'Jamaica',
    'ARM' => 'Armenia',
    'YEM' => 'Yemen',
    'IRQ' => 'Iraq',
    'IRN' => 'Iran',
    'ISR' => 'Israel',
    'ITA' => 'Italy',
    'IND' => 'India',
    'IDN' => 'Indonesia',
    'GBR' => 'United Kingdom',
    'VGB' => 'British Virgin Islands',
    'IOT' => 'British indian Ocean Territory',
    'JOR' => 'Jordan',
    'VNM' => 'Viet Nam',
    'ZMB' => 'Zambia',
    'TCD' => 'Chad',
    'GIB' => 'Gibraltar',
    'CHL' => 'Chile',
    'CAF' => 'Central Africa',
    'CHN' => 'China',
    'TWN' => 'Taiwan,China',
    'SRB' => 'SERBIA',
    'MNE' => 'MONTENEGRO'
);

$_LANG['yjpaycn_ccode'] = array_flip($_LANG['yjpaycn_countries']);
<?php
/**
Plugin Name: yiji-esp-pay-cn
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: 主要实现易极付外卡收单功能
Version: 0.0.1
Author: manarch
Author URI: https://apidoc.yiji.com
*/

define("CI_WC_ALI_PATH", plugins_url('', __FILE__));
define("CI_WC_PATH", plugin_dir_path(__FILE__));
add_action('plugins_loaded', 'yjpayesp_gateway_init', 0);

function yjpayesp_gateway_init (){
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_YiJiEspPayByCn extends WC_Payment_Gateway{
        protected $payment_review = null;
        protected $transactionId = null;
        protected $transactionErrorMessage = null;
        protected $update_static = false;

        public function __construct(){
            global $woocommerce;
            $this->id = 'yijiesppaybycn';
            $this->icon = apply_filters('woocommerce_yijiesppaycn_icon', plugins_url('images/logo.jpg', __FILE__));
            $this->has_fields = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->partnerId = $this->get_option('partnerId');
            $this->userId = $this->get_option('userId');
            $this->signKey = $this->get_option('signKey');
            $this->url = $this->get_option('url');
            $this->embedded = $this->get_option('embedded');
            $this->language = $this->get_option('language');
            $this->debug = strcmp($this->get_option('debug'), 'yes') == 0;
            array_push($this->supports,'refunds');
//            file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$this->partnerId."_waitSignString ](全局变量):\n".json_encode($this)."\n\n",FILE_APPEND);
            add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array(&$this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_'.$this->id, array(&$this, 'thankyou_page'));
            add_action( 'woocommerce_api_wc_yijiesppaybycn', array( $this, 'check_response' ) );

    }

    public function process_refund( $order_id, $amount = null, $reason = ''){
            $order = new WC_Order ($order_id );

            if(!$order){
                return new WP_Error( 'invalid_order','order id has error' );
            }

             $responece = $this->refund_action( $order->id,$amount,$reason);

            return $responece;
    }

    public function refund_action($merchOrderNo,$refundAmount,$refundReason){
            $requst_data = array();
            $requst_data['service'] = 'espRefund';
            $requst_data['version'] = '1.0';
            $requst_data['partnerId'] = trim($this->partnerId);
            $requst_data['orderNo'] = date('YmdHis') . mt_rand(100000, 999999);
            $requst_data['signType'] = 'RSA';
            $requst_data['merchOrderNo'] = $merchOrderNo;
            $requst_data['originalOrderNo'] = $merchOrderNo;
            $requst_data['refundAmount'] = sprintf("%.2f",$refundAmount);
            $requst_data['refundReason'] = $refundReason;
            $requst_data['sign'] = $this->getSignString($requst_data);
            $data = $this->vpost($this->url,$requst_data);
            $response_data = json_decode($data,1);

            if($response_data['resultCode']=='EXECUTE_SUCCESS' && $response_data['status']=='success'){
                $order = new WC_Order ($merchOrderNo);
                return $order->update_status('refunded',_('refunded',$this->id)) ? true : false ;
            }
            return false;
        }

        public function init_form_fields(){
            global $woocommerce;
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable',$this->id) ,
                    'type' => 'checkbox',
                    'label' => __('Enable OnlinePay Payment', $this->id) ,
                    'default' => 'no'
                ),
                'debug' => array(
                    'title' => __('Sandbox Mode', $this->id),
                    'type' => 'checkbox',
                    'label' => __('Enable Sandbox Mode', $this->id),
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __('Title',$this->id) ,
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.',$this->id) ,
                    'default' => __('YiJiEspPayByCn', $this->id)
                ),
                'description' => array(
                    'title' => __('Payment Description',$this->id) ,
                    'type' => 'textarea',
                    'default' => __('Pago en tarjeta crédito y débito, saltará a una pasarela de pago. Recomendamos que se paga en un terminal privado y entornos seguros.', $this->id),
                    'css' => 'width:400px'
                ),
                'partnerId' => array(
                    'title' => __('PartnerId.',$this->id) ,
                    'type' => 'text',
                    'description' => __('Please enter the merchant No.',$this->id) ,
                    'css' => 'width:400px'
                ),
                'userId' => array(
                    'title' => __('UserId.',$this->id) ,
                    'type' => 'text',
                    'description' => __('Please enter the gateway No.',$this->id) ,
                    'css' => 'width:400px'
                ),
                'signKey' => array(
                    'title' => __('File Password',$this->id) ,
                    'type' => 'text',
                    'description' => __('Please enter the security key',$this->id) ,
                    'css' => 'width:400px'
                ) ,
                'url' => array(
                    'title' => __('Submit Url',$this->id) ,
                    'type' => 'text',
                    'label' => __('Submit Url.', $this->id) ,
                    'description' => __('Please enter the Submit Url.',$this->id) ,
                    'default' => 'https://openapi.yijifu.net/gateway.html',
                    'css' => 'width:400px'
                ),
                'language'=>array(
                    'title' => __('languge',$this->id),
                    'type' => 'select',
                    'description' => __('Please select checkout counter show languge.',$this->id) ,
                    'options'=>array(
                        'en'=>'English',
                        'jp'=>'Japanese',
                        'de'=>'Deutsch',
                        'es'=>'El español',
                        'fr'=>'Français'
                    )
                )
            );

        }

        public function get_yijiesppaycn_args($order){
          global $woocommerce, $wpdb;
          $request_data = array();
          // 订单信息

          # 商户订单号
          $request_data['merchOrderNo'] = $order->id;
          # 订单价
          $request_data['amount'] = (float)$order->get_total();
          session_start();
          $request_data['deviceFingerprintId'] = session_id();
          # 币种
          $request_data['currency'] = get_option('woocommerce_currency');
          # 物流费
          //$shipFee = $order->get_fees();

          // 公共请求参数
          # 服务名
          $request_data['service'] = 'espPciOrderPay';
          # 请求订单号
          $request_data['orderNo'] = date('YmdHis') . mt_rand(100000, 999999);
          # 商户ID
          $request_data['partnerId'] = trim($this->partnerId);
          # 商户的用户ID
          $request_data['userId'] = trim($this->userId);
          # 商户密钥
          //$request_data['signKey'] = trim($this->signKey);
          # 服务版本
          $request_data['version'] = '3.0';
          $request_data['webSite'] = $_SERVER['HTTP_HOST'];//strstr(home_url(),'//');//trim(home_url(),'http://');
//            $request_data['webSite'] = 'www.dblai.com';
          $request_data['protocol'] = 'httpGet';
          # 收单类型
          $request_data['acquiringType'] = 'CRDIT';
          # 同步跳转地址
//          $request_data['returnUrl'] = $this->get_return_url($order);
            $request_data['returnUrl'] = 'http://wordpress.yjf.net/?page_id=6';
          # 异步回调地址
          $request_data['notifyUrl'] = WC()->api_request_url( 'WC_YiJiEspPayByCn' );
//            $request_data['notifyUrl'] = 'http://wordpress.yjf.net';

            # 卡号
            $request_data['cardNo'] = $this->encrypt($_POST['billing_credircard'].$request_data['orderNo'] ,$this->debug);
            # cvv三字码
            $request_data['cvv'] = $this->encrypt($_POST['billing_ccvnumber'].$request_data['orderNo'] ,$this->debug);
            # 名
            $request_data['cardHolderFirstName'] = trim($order->billing_first_name);
            # 姓
            $request_data['cardHolderLastName'] = trim($order->billing_last_name);
            # 过期日期 月年
            $request_data['expirationDate'] = sprintf('%s%s', substr($_POST['billing_expdateyear'],-2), $_POST['billing_expdatemonth']);

          // 产品信息
          $request_data['goodsInfoList'] = $this->getGoodsInfoListByOrderList($order->get_items());
          //file_put_contents(dirname(__FILE__).'/read.txt',json_encode($request_data)."\n",FILE_APPEND);
          $billtoState = empty($order->billing_state) ? trim($order->billing_city) : $order->billing_state;
          //file_put_contents(dirname(__FILE__).'/read.txt',json_encode($request_data)."\n",FILE_APPEND);
          $attachDetails_data = array(
            'ipAddress'=>$_SERVER["HTTP_CLIENT_IP"] ? $_SERVER["HTTP_CLIENT_IP"] :'127.0.0.1',
            'billtoCountry'=>$order->billing_country,
            'billtoState'=> $billtoState,
            'billtoCity'=>trim($order->billing_city),
            'billtoPostalcode'=>trim($order->billing_postcode),
            'billtoEmail'=>trim($order->billing_email),
            'billtoFirstname'=> trim($order->billing_first_name),
            'billtoLastname'=>trim($order->billing_last_name),
            'billtoPhonenumber'=>trim($order->billing_phone),
            'billtoStreet'=>trim($order->billing_address_1 . ' ' . $order->billing_address_2),
              'cardType' => trim($_POST['billing_cardtype']),
            'shiptoCity'=>trim($order->shipping_city ? $order->shipping_city : $order->billing_city),
            'shiptoCountry'=>trim($order->shipping_country ? $order->shipping_country : $order->billing_country),
            'shiptoFirstname'=>trim($order->shipping_first_name ? $order->shipping_first_name : $order->billing_first_name),
            'shiptoLastname'=>trim($order->shipping_last_name ? $order->shipping_last_name : $order->billing_last_name),
            'shiptoEmail'=>trim($order->billing_email),
            'shiptoPhonenumber'=>trim($order->billing_phone),
            'shiptoPostalcode'=>trim($order->shipping_postcode ? $order->shipping_postcode : $order->billing_postcode),
            'shiptoState'=>trim($order->shipping_state ? $order->shipping_state : $billtoState),
            'shiptoStreet'=>trim(($order->shipping_address_1 || $order->shipping_address_1) ? ($order->shipping_address_1 . ' ' . $order->shipping_address_2) : $order->billing_address_1 . ' ' . $order->billing_address_2),

            # 物流费
            'logisticsFee'=>$order->order_shipping,
            'logisticsMode'=>$order->shipping_method,//$order->method_id,
            'customerEmail'=>trim($order->billing_email),
            'customerPhonenumber'=>trim($order->billing_phone),
          );

          $request_data['orderDetail'] = json_encode($attachDetails_data);
          $request_data['language'] =  trim($this->language);
          $request_data['signType'] = 'RSA';
          $request_data['sign'] = $this->getSignString($request_data);
          return $request_data;
        }

        /*
     * Validates the fields specified in the payment_fields() function.
     */
        public function validate_fields() {
            global $woocommerce;
//            file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".'POST'."_waitSignString ](cvv cardNo加密):\n".json_encode($_POST)."\n\n",FILE_APPEND);

            if (!$this->debug ){
                if (!$this->is_valid_card_number($_POST['billing_credircard'])){
                    wc_add_notice(__('Credit card number you entered is invalid.', 'woocommerce'), 'error');
                }
                if (!$this->is_valid_card_type($_POST['billing_cardtype'])){
                    wc_add_notice(__('Card type is not valid.', 'woocommerce'), 'error');
                }
                if (!$this->is_valid_expiry($_POST['billing_expdatemonth'], $_POST['billing_expdateyear'])){
                    wc_add_notice(__('Card expiration date is not valid.', 'woocommerce'), 'error');
                }
                if (!$this->is_valid_cvv_number($_POST['billing_ccvnumber'])){
                    wc_add_notice(__('Card verification number (CVV) is not valid. You can find this number on your credit card.', 'woocommerce'), 'error');
                }
            }
        }

        /*
         * Render the credit card fields on the checkout page
         */
        public function payment_fields() {
            $billing_credircard = isset($_REQUEST['billing_credircard'])? esc_attr($_REQUEST['billing_credircard']) : '';
            ?>
            <p class="form-row validate-required">
                <label>Card Number <span class="required">*</span></label>
                <input class="input-text" type="text" size="19" maxlength="19" name="billing_credircard" value="<?php echo $billing_credircard; ?>" />
            </p>
            <p class="form-row form-row-first">
                <label>Card Type <span class="required">*</span></label>
                <select name="billing_cardtype" >
                    <option value="Visa" selected="selected">Visa</option>
                    <option value="MasterCard">MasterCard</option>
                    <option value="JCB">JCB</option>
                    <!-- <option value="Amex">American Express</option> -->
                </select>
            </p>
            <div class="clear"></div>
            <p class="form-row form-row-first">
                <label>Expiration Date <span class="required">*</span></label>
                <select name="billing_expdatemonth">
                    <option value=01>01</option>
                    <option value=02>02</option>
                    <option value=03>03</option>
                    <option value=04>04</option>
                    <option value=05>05</option>
                    <option value=06>06</option>
                    <option value=07>07</option>
                    <option value=08>08</option>
                    <option value=09>09</option>
                    <option value=10>10</option>
                    <option value=11>11</option>
                    <option value=12>12</option>
                </select>
                <select name="billing_expdateyear">
                    <?php
                    $today = (int)date('Y', time());
                    for($i = 0; $i < 8; $i++)
                    {
                        ?>
                        <option value="<?php echo $today; ?>"><?php echo $today; ?></option>
                        <?php
                        $today++;
                    }
                    ?>
                </select>
            </p>
            <div class="clear"></div>
            <p class="form-row form-row-first validate-required">
                <label>Card Verification Number (CVV) <span class="required">*</span></label>
                <input class="input-text" type="text" size="4" maxlength="4" name="billing_ccvnumber" value="" />
            </p>
            <?php if ($this->securitycodehint){
                $cvv_hint_img = WC_PP_PRO_ADDON_URL.'/images/card-security-code-hint.png';
                $cvv_hint_img = apply_filters('wcpprog-cvv-image-hint-src', $cvv_hint_img);
                echo '<div class="wcppro-security-code-hint-section">';
                echo '<img src="'.$cvv_hint_img.'" />';
                echo '</div>';
            }
            ?>
            <div class="clear"></div>

            <?php
        }

        public function admin_options() {

            ?>
            <h3><?php  _e('YiJiEspPayByCn', $this->id); ?></h3>
            <p> <?php  _e('Allows cheque payments. Why would you take cheques in this day and age? Well you probably wouldn\'t but it does allow you to make test purchases for testing order emails and the \'success\' pages etc.', $this->id); ?></p>
            <table class=\"form-table\">
                <?php $this->generate_settings_html(); ?>
            </table>

            <?php
        }

        public function thankyou_page() {
            $order = new WC_Order($_REQUEST['merchOrderNo']);
            $msg = '';
            $color = '';
            switch ($order->post_status){
                case "wc-failed": #支付失败
                    $msg = 'the order has fail , pelace reSubmit Order';
                    $color = 'red';
                    break;

                case "on-hold": # 预售权
                    $msg = 'We are processing this order';
                    $color = 'yellow';
                    break;
                case "wc-processing": # 支付成功
                    $msg = 'We will give you delivery';
                    $color = 'green';
                    break;
                case "":
                    $msg = 'sorry have error';
                    $color = 'red';
                   break;
            }

            echo "<div style='color:$color'>$msg</div><script type='application/javascript'>if (window.frames.length != parent.frames.length) {
            window.top.location.href = '';
        }</script>";
            exit;
            //if ($this->description) echo wpautop(wptexturize($this->description));
        }

        public function requirement_checks() {
            //echo "11>>>";
        }

        public function check_request_sign(array $items ){
            $sign = $items['sign'];
            unset($items['sign']);
            $verifyResult = false;
            $waitingForSign = '';
            foreach ($items as $key => $value) {
                if('sign'===$key){
                    continue;
                }
                $waitingForSign .= ($key . '=' . $value . '&');
            }
            $waitingForSign = trim(substr($waitingForSign, 0, -1));
            if (!$this->debug){
                $cerPath = __DIR__.'/yiji.online.cer';
            }else {
                $cerPath = __DIR__.'/yiji.snet.cer';
            }

            $certificateCAcerContent = file_get_contents($cerPath);
            $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
                .chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL)
                .'-----END CERTIFICATE-----'.PHP_EOL;
            $pubkeyid = openssl_get_publickey($certificateCApemContent);
            $signValue = base64_decode($sign);
            if($pubkeyid){
                // state whether signature is okay or not
                $verifyResult = openssl_verify($waitingForSign, $signValue, $pubkeyid);
                openssl_free_key($pubkeyid);
            }
            return $verifyResult;
        }

        public function check_response(){

            //file_put_contents(dirname(__FILE__).'/read.txt',"[ REQUEST_".$_REQUEST['merchOrderNo']." ]:\n".json_encode($_REQUEST)."\n\n",FILE_APPEND);

            if($this->check_request_sign($_REQUEST)){
                if($_REQUEST['resultCode'] == 'EXECUTE_SUCCESS'){

                    $order = new WC_Order($_REQUEST['merchOrderNo']);
                    $update_static = false;
                    switch ($_REQUEST['status']){
                        case 'success':
                            $order->add_order_note(__('The payment is successful !', 'yiji-esp-pay-hk'));
                            //订单完成处理
                            $order->payment_complete();
                            //清空购物车
//                            $woocommerce->cart->empty_cart();
                            $update_static = $order->update_status('completed', __($_REQUEST['status'],$this->id));
                            break;
                        case 'fail':
                            $update_static = $order->update_status('failed', __($_REQUEST['status'],$this->id));
                            file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$_REQUEST['merchOrderNo']." requset_time:".date('Y-m-d H:i:s')." request_ip:".$this->get_real_ip()." ](付款失败):\n".json_encode($_REQUEST)."\n\n",FILE_APPEND);
                            break;
                        case 'authorizing':
                            $update_static = $order->update_status('on-hold', __($_REQUEST['status'],$this->id));
                            break;
                        case 'processing':
                            $update_static = $order->update_status('processing', __($_REQUEST['status'],$this->id));
                            break;
                    }
                    $update_static ? exit("success") : exit("fail");

                }

                //file_put_contents(dirname(__FILE__).'/read.txt',"[ REQUEST_".$_REQUEST['merchOrderNo']." ]:\n".json_encode($_REQUEST)."\n\n",FILE_APPEND);
                exit("success");
            }else{
                file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$_REQUEST['merchOrderNo']." requset_time:".date('Y-m-d H:i:s')." request_ip:".$this->get_real_ip()." ](签名失败):\n".json_encode($_REQUEST)."\n\n",FILE_APPEND);
                exit("fail");
            }
        }

        public function process_payment( $order_id ) {
            global $woocommerce;

            $order = new WC_Order($order_id);

//            $order->update_status('pending', __('Awaiting cheque payment', 'woothemes'));

            $requset_data = $this->get_yijiesppaycn_args($order);

            ksort($requset_data);

            $order->add_order_note( __('Customer was redirected to Yijipay', 'woothemes') );
            if ($requset_data AND $this->verify_yijipay_payment($requset_data)) {
                if($this->payment_review === 1){
                    $order->add_order_note(sprintf("Order processing, waiting for processing completed"));
                }elseif ($this->payment_review === 2){
                    $order->add_order_note(sprintf("Credit Card Payment need authorizing"));
                }else{
                    $this->do_order_complete_tasks();
                }
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            } else {
                //$this->mark_as_failed_payment();
                wc_add_notice(__('(Transaction Error) something is wrong.'.$this->transactionErrorMessage, 'woocommerce'),'','error');
            }

        }

        public function getSignString($items = array()){

            ksort($items);

            $waitSignString = $sign = '';

            foreach($items as $key => $value){
                if(empty($value)||$value==="")
                    unset($items[$key]);
                else
                    $waitSignString .= ($key . '=' . $value . '&');
            }


//            file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$items['merchOrderNo']."_waitSignString ](代签名):\n".$waitSignString."\n\n",FILE_APPEND);

            if ($items['signType'] === 'RSA'){
                $waitingForSign = trim(substr($waitSignString, 0, -1));
                $pfxPath = __DIR__.'/'.$items['partnerId'].'.pfx';
                $keyPass = $this->signKey;
                $pkcs12 = file_get_contents($pfxPath);
//                file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$items['merchOrderNo']."_waitSignString ](加密):\n"."signkey:".$this->signKey."  pfxPath:".$pfxPath."\n\n",FILE_APPEND);
                if (openssl_pkcs12_read($pkcs12, $certs, $keyPass)) {
                    $privateKey = $certs['pkey'];
                    $signedMsg = "";
                    if (openssl_sign($waitingForSign, $signedMsg, $privateKey)) {
                        $sign = base64_encode($signedMsg);
                    } else {
                        $sign = '加密失败';
                    }
                }
            }elseif($items['signType'] === 'MD5'){
                $sign = md5($waitSignString.$this->signKey);
            }
            return $sign;

        }


        function getGoodsInfoListByOrderList($ordered_items) {
            $k = 0;
            foreach ($ordered_items as $item) {
                // 获得商品所有信息
                $goods_array[$k]['goodsCount'] = $item['qty'];
                $goods_array[$k]['goodsNumber'] = $item['product_id'];
                $goods_array[$k]['goodsName'] = $item['name'];
                $goods_array[$k]['itemSharpUnitPrice'] = $item['line_subtotal'];
                $goods_array[$k]['itemSharpProductCode'] = $item['name'];
                $k++;
            }
            return json_encode($goods_array);
        }

        protected function verify_yijipay_payment($gatewayRequestData) {
            $order = new WC_Order($gatewayRequestData['merchOrderNo']);
            global $woocommerce;
            $request = array(
                'method' => 'POST',
                'timeout' => 45,
                'blocking' => true,
                'sslverify' => $this->debug ? false : true,
                'body' => $gatewayRequestData
            );

            $response = wp_remote_post($this->url, $request);
            $fn1 = dirname(__FILE__)."/logs/";
//            file_put_contents($fn1."response.log-".date('Y-m-d'),"\r\n".date('Y-m-d H:i:s ')." HTTP_JSON:".$response['body'],FILE_APPEND);

            if (!is_wp_error($response)) {
//                file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 1验证响应is_wp_error :false",FILE_APPEND);
                $resultArray = array();
                $resultArray = json_decode($response['body'],true);
                $resultCode = $resultArray['resultCode'];
                $resultMessage = $resultArray['resultMessage'];
                $success = $resultArray['success'];
                $status = $resultArray['status'];

                if( $resultArray['success'] == true){
//                    file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 2验证响应success:".$success,FILE_APPEND);
                    //支付成功 更订单状态 processing 增加历史记录
                    if($resultCode === "EXECUTE_SUCCESS" && $status === "success"){
//                        file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 3验证响应code:".$status,FILE_APPEND);
                        $order->add_order_note(__('The payment is successful !', 'yiji-esp-pay-hk'));
                        //订单完成处理
                        $order->payment_complete();
                        //清空购物车
//                        $woocommerce->cart->empty_cart();
                        $this->update_static = $order->update_status('completed', __($_REQUEST['status'],$this->id));

                        $order->add_order_note( __('YijiPay payment success, Waiting for asynchronous notification', 'woothemes') );

                        //支付处理中 更订单状态 payment_review 增加历史记录
                    }else if($resultCode === "EXECUTE_SUCCESS" && $status === "processing"){
//                        file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 4验证响应code:".$status,FILE_APPEND);
                        $this->payment_review = 1;
                        $this->transactionErrorMessage = $errCodeCtx = $resultArray['ErrCodeCtx'];   //描述
                        $order->add_order_note( __('This order is being processed', 'woothemes') );

                        //支付授权 更订单状态 payment_review 增加历史记录
                    }else if ($resultCode === "EXECUTE_SUCCESS" && $status === "authorizing"){
//                        file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 5验证响应code:".$status,FILE_APPEND);
                        $this->update_static = $order->update_status('on-hold', __($_REQUEST['status'],$this->id));
                        $this->payment_review = 2;
                        $order->add_order_note( __('Customer payment is complete, but there is a risk, need authorization', 'woothemes') );
                    }
                    $this->transactionId = $resultArray['outOrderNo'];
                    return true;
                }else{//支付失败 业务处理
//                    file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 6验证响应success:".$success,FILE_APPEND);
                    if($resultCode === "ANALYZERESULT_REJECT" || $resultCode =="APPLY_CARD_PAY_FAIL"){
//                        file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 7验证响应resultCode:".$resultCode,FILE_APPEND);
                        $this->update_static = $order->update_status('failed', __($_REQUEST['status'],$this->id));
                        $order->add_order_note( __('YijiPay Credit Card payment failed :'.$resultMessage, 'woothemes') );
                        //其它异常处理
                    }else{
                        $this->update_static = $order->update_status('failed', __($_REQUEST['status'],$this->id));
                        $order->add_order_note( __('YijiPay Credit Card payment failed :'.$resultMessage, 'woothemes') );
                    }
                    //$this->transactionErrorMessage = $erroMessage = $parsedResponse['L_LONGMESSAGE0'];
                    $this->transactionErrorMessage = $erroMessage = $resultMessage;
                    wc_add_notice("<h2 style='color:#ffffff' >Payment Failed</h2>".$resultCode.":".$erroMessage,'error');
                    return false;
                }

            } else {
                // Uncomment to view the http error
                //$erroMessage = print_r($response->errors, true);
                $erroMessage = 'Something went wrong while performing your request. Please contact website administrator to report this problem.';
                $error_string = $response->get_error_message();
//                file_put_contents($fn1."debug.log","\r\n".date('Y-m-d H:i:s ')." 8验证响应error_string:".$error_string,FILE_APPEND);
                wc_add_notice("<h2 style='color:#ffffff' >Something error</h2>".$error_string.":".$erroMessage,'error');
                return false;
            }
        }

        // 获取IP地址
        function get_real_ip() {
            $ip = false;
            if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
                if ($ip) {
                    array_unshift($ips, $ip);
                    $ip = FALSE;
                }
                for ($i = 0; $i < count($ips); $i++) {
                    if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                        $ip = $ips[$i];
                        break;
                    }
                }
            }
            return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
        }

        /**
         * cvv cardNo加密
         * @param $entity 待加密串
         * @param $test 是否debugMode
         * @return 签约结果
         */
        function encrypt($entity,$test = true){
            $encrypted = '';
            $public_key_path = $test? __DIR__ . "/yjf-cert-2048.pem" :__DIR__ . "/yjf-online-2048.pem";
//            file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".'加密的orderno'."_waitSignString ](cvv cardNo加密):\n".$entity."\n\n",FILE_APPEND);

            $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));

            openssl_public_encrypt(str_pad($entity, 256, "\0", STR_PAD_LEFT), $encrypted, $public_key, OPENSSL_NO_PADDING);

            return base64_encode($encrypted);
        }

        function is_valid_card_number($toCheck) {
            if (!is_numeric($toCheck))
                return false;

            $number = preg_replace('/[^0-9]+/', '', $toCheck);
            $strlen = strlen($number);
            $sum = 0;

            if ($strlen < 13)
                return false;

            for ($i = 0; $i < $strlen; $i++) {
                $digit = substr($number, $strlen - $i - 1, 1);
                if ($i % 2 == 1) {
                    $sub_total = $digit * 2;
                    if ($sub_total > 9) {
                        $sub_total = 1 + ($sub_total - 10);
                    }
                } else {
                    $sub_total = $digit;
                }
                $sum += $sub_total;
            }

            if ($sum > 0 AND $sum % 10 == 0)
                return true;

            return false;
        }

        function is_valid_card_type($toCheck) {
            $acceptable_cards = array(
                "Visa",
                "MasterCard",
                "JCB"
            );
            return $toCheck AND in_array($toCheck, $acceptable_cards);
        }

        function is_valid_expiry($month, $year) {
            $now = time();
            $thisYear = (int) date('Y', $now);
            $thisMonth = (int) date('m', $now);

            if (is_numeric($year) && is_numeric($month)) {
                $thisDate = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
                $expireDate = mktime(0, 0, 0, $month, 1, $year);

                return $thisDate <= $expireDate;
            }

            return false;
        }

        function is_valid_cvv_number($toCheck) {
            $length = strlen($toCheck);
            return is_numeric($toCheck) AND $length > 2 AND $length < 5;
        }

        function do_order_complete_tasks() {
            global $woocommerce;
            /*
                    if ($this->order->status == 'completed')
                        return;

                    $this->order->payment_complete();
                    $woocommerce->cart->empty_cart();

                    $this->order->add_order_note(
                            sprintf("Credit Card payment completed with orderNo of '%s'", $this->transactionId)
                    );
            */
            unset($_SESSION['order_awaiting_payment']);
        }

        function vpost($url,$data){ // 模拟提交数据函数
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

    }

    function woocommerce_YiJiEspPayByCn_add_gateway($methods) {
        $methods[] = 'WC_YiJiEspPayByCn';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'woocommerce_YiJiEspPayByCn_add_gateway');
}

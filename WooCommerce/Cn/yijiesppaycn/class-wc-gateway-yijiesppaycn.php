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
            array_push($this->supports,'refunds');

            add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array(&$this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_'.$this->id, array(&$this, 'thankyou_page'));
            add_action( 'woocommerce_api_wc_yijiesppaybycn', array( $this, 'check_response' ) );
            add_action( 'woocommerce_receipt_yijiesppaybycn', array( $this, 'receipt_page' ) );

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
            $requst_data['service'] = 'cardAcquiringRefund';
            $requst_data['version'] = '1.0';
            $requst_data['partnerId'] = trim($this->partnerId);
            $requst_data['orderNo'] = date('YmdHis') . mt_rand(100000, 999999);
            $requst_data['signType'] = 'MD5';
            $requst_data['merchOrderNo'] = $merchOrderNo;
            $requst_data['originalMerchOrderNo'] = $merchOrderNo;
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
                'embedded' => array(
                    'title' => __('Embedded/jump',$this->id) ,
                    'type' => 'checkbox',
                    'label' => __('Embedded Payment', $this->id) ,
                    'default' => 'yes'
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
                    'title' => __('Security Key',$this->id) ,
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
          $request_data['service'] = 'espOrderPay';
          # 请求订单号
          $request_data['orderNo'] = date('YmdHis') . mt_rand(100000, 999999);
          # 商户ID
          $request_data['partnerId'] = trim($this->partnerId);
          # 商户的用户ID
          $request_data['userId'] = trim($this->userId);
          # 商户密钥
          //$request_data['signKey'] = trim($this->signKey);
          # 服务版本
          $request_data['version'] = '1.0';
          $request_data['webSite'] = $_SERVER['HTTP_HOST'];//strstr(home_url(),'//');//trim(home_url(),'http://');
          $request_data['protocol'] = 'httpGet';
          # 收单类型
          $request_data['acquiringType'] = 'CRDIT';
          # 同步跳转地址
          $request_data['returnUrl'] = $this->get_return_url($order);
          # 异步回调地址
          $request_data['notifyUrl'] = WC()->api_request_url( 'WC_YiJiEspPayByCn' );

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

            'shiptoCity'=>trim($order->shipping_city),
            'shiptoCountry'=>trim($order->shipping_country),
            'shiptoFirstname'=>trim($order->shipping_first_name),
            'shiptoLastname'=>trim($order->shipping_last_name),
            'shiptoEmail'=>trim($order->billing_email),
            'shiptoPhonenumber'=>trim($order->billing_phone),
            'shiptoPostalcode'=>trim($order->shipping_postcode),
            'shiptoState'=>trim($order->shipping_state),
            'shiptoStreet'=>trim($order->shipping_address_1 . ' ' . $order->shipping_address_2),

            # 物流费
            'logisticsFee'=>$order->order_shipping,
            'logisticsMode'=>$order->shipping_method,//$order->method_id,
            'customerEmail'=>trim($order->billing_email),
            'customerPhonenumber'=>trim($order->billing_phone),
          );
            //file_put_contents(dirname(__FILE__).'/read.txt',json_encode($attachDetails_data)."\n",FILE_APPEND);
          $request_data['orderDetail'] = json_encode($attachDetails_data);
          $request_data['language'] =  trim($this->language);
          $request_data['signType'] = 'MD5';
          $request_data['sign'] = $this->getSignString($request_data);
          //ksort($request_data);
          //file_put_contents(dirname(__FILE__).'/read.txt',json_encode($request_data)."\n",FILE_APPEND);
          return $request_data;
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


        public function payment_fields() {
            if ($this->description) echo wpautop(wptexturize($this->description));
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
            return $sign == $this->getSignString($items);
        }

        public function check_response(){

            //file_put_contents(dirname(__FILE__).'/read.txt',"[ REQUEST_".$_REQUEST['merchOrderNo']." ]:\n".json_encode($_REQUEST)."\n\n",FILE_APPEND);

            if($this->check_request_sign($_REQUEST)){
                if($_REQUEST['resultCode'] == 'EXECUTE_SUCCESS'){

                    $order = new WC_Order($_REQUEST['merchOrderNo']);
                    $update_static = false;
                    switch ($_REQUEST['status']){
                        case 'success':
                            $update_static = $order->update_status('processing', __($_REQUEST['status'],$this->id));
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

            $order->update_status('pending', __('Awaiting cheque payment', 'woothemes'));

            $requset_data = $this->get_yijiesppaycn_args($order);
            ksort($requset_data);


            set_transient( 'yijiesppaybycn_next_url', $this->url.'?'.http_build_query($requset_data,'','&') );

            $redirect = $order->get_checkout_payment_url( true );

            return array(
                'result' 	=> 'success',
                'redirect'	=> $redirect
            );

        }

        public function generate_yijiesppaybycn_form( $order_id ){
            global $woocommerce;
            $order = new WC_Order($order_id);
            $requset_data = $this->get_yijiesppaycn_args($order);
            ksort($requset_data);
            $string = '';
            $target = $this->embedded == 'yes' ? ' target="iframe_a"': '' ;
            if($this->embedded == 'yes'){
                return '<iframe src="'.esc_url( get_transient('yijiesppaybycn_next_url') ).'" name="sagepayserver_payment_form" width="100%" height="550px" style="border: 0px;"></iframe>';
            }
                //$redirect = add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('woocommerce_pay_page_id'))));
                $string .= '<form action="'.esc_url( get_transient('yijiesppaybycn_next_url') ).'" method="post" id="yjpaybyform" '.$target.' >';

                foreach ($requset_data as $k => $v ){
                    $string .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
                }

                $string .= '</form>';
                wc_enqueue_js('
					jQuery("#yjpaybyform").submit();
				');
            return $string;
        }

        public function receipt_page( $order ){
            global $woocommerce;

            echo '<p>'.__('Thank you Please pay.', 'woothemes').'</p>';

            echo $this->generate_yijiesppaybycn_form($order);
        }

        public function getSignString($items = array()){

          ksort($items);

          $waitSignString = '';

          foreach($items as $key => $value){
              $waitSignString .= '&'.$key.'='.$value;
          }

          $waitSignString = trim($waitSignString,'&');
          file_put_contents(dirname(__FILE__).'/logs/'.date('Ymd').'log.txt',"[NOTIFY_REQUEST_ORDER_BY_".$items['merchOrderNo']."_waitSignString ](代签名):\n".$waitSignString.$this->signKey."\n\n",FILE_APPEND);

            return md5($waitSignString.$this->signKey);

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

<?php
/*
** 签名规则
** 签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，同时将商家支付密钥key放在最后参与签名，
** 组成规则如下：
** 参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值nkey值
*/
    function yjfpay_signature($data,$YJF_MERCHANT_CKEY) {
        # sort for key
        ksort($data);
        $signSrc="";
        foreach($data as $k=>$v)
        {
            if(empty($v)||$v==="")
                unset($data[$k]);
            else
                $signSrc.= $k.'='.$v.'&';
        }

        $signSrc = trim($signSrc, '&').$YJF_MERCHANT_CKEY;

        if($data['signType']==="MD5")
            return md5($signSrc);
    }
//
    function array_key_pop(&$array, $key, $default = false) {
        # if isset key value
        if (isset($array[$key])) {
            $default = $array[$key];
        }

        unset($array[$key]);
        return $default;
    }
//
//    //获取IP地址
    function get_real_ip(){
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

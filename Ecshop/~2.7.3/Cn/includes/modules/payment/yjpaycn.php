<?php

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$lang = ROOT_PATH . 'yjpaycn/langs/' . $GLOBALS['_CFG']['lang'] . '/esp.php';
if (is_file($lang))
{
    include_once($lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'yjpaycn_desc';
	
    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';

    /* 支付费用，由配送决定 */
    $modules[$i]['pay_fee'] = '0';

    /* 作者 */
    $modules[$i]['author'] = 'YIJI TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'https://www.yiji.com/';
	
    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';
	
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'yjpaycn_cfg_merchant_name', 'type' => 'text', 'value' => ''),
        array('name' => 'yjpaycn_cfg_merchant_email','type' => 'text', 'value' => ''),
		
        array('name' => 'yjpaycn_cfg_partner_id', 'type' => 'text', 'value' => ''),
        array('name' => 'yjpaycn_cfg_secret_key','type' => 'text', 'value' => ''),
		
        array('name' => 'yjpaycn_cfg_currency','type' => 'select','value' => 'CNY'),
        array('name' => 'yjpaycn_cfg_debug', 'type' => 'select', 'value' => '0'),
        
        array('name' => 'yjpaycn_cfg_payment_type', 'type' => 'select', 'value' => 'CRDIT'),
    );
	
    return;
}

class yjpaycn
{
    function yjpaycn()
    {
    }

    function __construct()
    {
        $this->yjpaycn();
    }

    /**
     * 提交函数
     */
    function get_code($order, $payment)
    {
        $button = <<<EOF
            <div style="text-align:center">
                <input type="button" onclick="window.open('yjpaycn.php?order_sn={$order['order_sn']}');" 
					value="{$GLOBALS['_LANG']['yjpaycn_submit']}" />
            </div>
EOF;
        return $button;
    }
	
    /**
     * 处理函数
     */
    function respond()
    {
        return true;
    }
}





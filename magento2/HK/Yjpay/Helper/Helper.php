<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/10/18
 * Time: 17:13
 */

namespace Magento\Yjpay\Helper;

use Magento\Framework\App\Helper\Context;
// use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;

class Helper extends AbstractHelper
{

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    public function getSign(array $data,$serc){

        foreach ($data as $k => $v){
            if( !isset($v) ){
                unset($data[$k]);
            }
        }

        ksort($data);

        $string = '';

        foreach ( $data as $key => $value ){
            $string .= $key.'='.$value.'&';
        }

        $string = trim($string,'&').$serc;

        file_put_contents(__DIR__.'/../logs/'.$data['merchOrderNo'].'.log','SignOrder: '.$string."\n\n",FILE_APPEND);

        return md5($string);

    }

}
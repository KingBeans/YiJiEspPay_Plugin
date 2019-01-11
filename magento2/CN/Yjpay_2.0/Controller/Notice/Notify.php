<?php
/**
 * Created by PhpStorm.
 * User: manarch
 * Date: 2017/10/17
 * Time: 14:36
 */

namespace Magento\Yjpay\Controller\Notice;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Yjpay\Helper\Helper;

class Notify extends Action
{
    protected $yjpH;

    public function __construct(Context $context , Helper $yjpH)
    {
        $this->yjpH = $yjpH;
        parent::__construct($context);
    }

    public function execute()
    {

        $_post = filter_input_array(INPUT_POST);
        $order_id = $_post['merchOrderNo'];
        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log','notifyUrl: '.json_encode($_post)."\n\n",FILE_APPEND);
        $Yjpay = $this->getYjpay();



        $sign = $_post['sign'];

        $localSign = $this->yjpH->getSign($_post,$Yjpay->getYjpayData('secretKey'),$Yjpay->getYjpayData('sandbox'));

        if($localSign == $sign){
            $status = $_post['status'];

            $orderObj = $this->getOrder($order_id);

            $successStatus = $Yjpay->getYjpayData('successOrderStatus');

            $failStatus = $Yjpay->getYjpayData('failOrderStatus');

            $authorizingStatus = $Yjpay->getYjpayData('authorizingOrderStatus');

            $processingStatus = $Yjpay->getYjpayData('processingOrderStatus');

            switch ($status){
                case 'success' :
                    $statusStr = $successStatus;
                    break;
                case 'fail':
                    $statusStr = $failStatus;
                    break;
                case 'authorizing':
                    $statusStr = $authorizingStatus;
                    break;
                case 'processing':
                    $statusStr = $processingStatus;
                    break;
                case 'authorizingTrue':
                    $statusStr = $successStatus;
                    break;
                case 'authorizingFail':
                    $statusStr = $failStatus;
                    break;
            }

            $orderObj->addStatusToHistory($statusStr,$_post['description'])->save();
            echo 'success';
        }else{
            echo 'fail';
        }

    }

    protected function getYjpay(){
        return $this->_objectManager->get('Magento\Yjp0ay\Model\Yjpay');
    }

    protected function getOrder($order_id){
        return $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
    }
}
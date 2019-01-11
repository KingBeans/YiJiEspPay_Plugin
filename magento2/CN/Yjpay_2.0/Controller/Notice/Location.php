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

class Location extends Action
{
    protected $yjpH;

    public function __construct(Context $context , Helper $yjpH)
    {
        $this->yjpH = $yjpH;
        parent::__construct($context);
    }

    public function execute(){

        $_get = filter_input_array(INPUT_GET);
        $order_id = $_get['merchOrderNo'];
        file_put_contents(__DIR__.'/../../logs/'.$order_id.'.log','returnUrl: '.json_encode($_get)."\n\n",FILE_APPEND);
        $Yjpay = $this->getYjpay();

        $sign = $_get['sign'];

        $localSign = $this->yjpH->getSign($_get,$Yjpay->getYjpayData('secretKey'),$Yjpay->getYjpayData('sandbox'));

        if($localSign == $sign){

            $status = $_get['status'];

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
            }
            $orderObj->addStatusToHistory($statusStr,$_get['description'])->save();
            $this->messageManager->addSuccessMessage($_get['description']);


            $this->getResponse()->setRedirect('/sales/order/view/order_id/'.$order_id.'/');

        }else{
            $this->messageManager->addErrorMessage('this order return notice have error');
            $this->getResponse()->setRedirect('/sales/order/view/order_id/'.$order_id.'/');
        }


    }

    protected function getYjpay(){
        return $this->_objectManager->get('Magento\Yjpay\Model\Yjpay');
    }

    protected function getOrder($order_id){
        return $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
    }



}
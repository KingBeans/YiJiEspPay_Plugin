<?php

error_reporting(E_ERROR | E_PARSE);
chdir(dirname(__FILE__).'/../../');
include ( './config/config.inc.php');

 
echo header("Content-Type: text/html; charset=utf-8");
 
class EspOrderCore 
{
    public function getPayOrder()
	{
        $orderid=$_GET['outOrderNo']; 
	    $query['protocol'] ='httpPost';
        $query['service'] ='espOrderQuery';
        ///商户号
        $query['partnerId'] =Configuration::get ( 'CCPAY_MERCHANTID' );	
        ///订单流水
        $query['orderNo'] = 'yjf'.date("YmdHis").rand(1000,9999);
        $query['signType'] ='MD5';  
        $query['outOrderNo'] =$orderid;
         $query['pageNo'] =1;
       	ksort($query);	
        
        $fields_string=  http_build_query($query);
        //对应key
       	$merchantkey =  Configuration::get ('CCPAY_MERCHANTKEY'); 
        
		$query ['sign']=md5($fields_string.$merchantkey); 
        ///提交URL
        $url =Configuration::get ( 'CCPAY_GATEWAY_DOMAIN' ); 
	    $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($query));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_NOBODY, 0);
	$response = curl_exec($ch); 
  
	curl_close($ch);
     
     
    if($response=="")
	echo("<h2 class='page-heading'>Payment Error ! Response Error</h2>"); 
    $result = json_decode($response,true);
    
    $success=$result['success'];
    $return='';
    if($success)
    {
        if($result['count']==0)
        {
            $return= @'<tr>
									<td class="list-empty hidden-print" colspan="6">
										<div class="list-empty-msg">
											<i class="icon-warning-sign list-empty-icon"></i>
											 No payment methods are available 
										</div>
									</td>
								</tr>';
        }
        else
        {
            $tradeNo= $result['espOrderInfoList'][0]['outOrderNo'];
            $amountLoc= $result['espOrderInfoList'][0]['amountLoc'];
            $tradeTime= $result['espOrderInfoList'][0]['tradeTime'];
            $orderStatus= $result['espOrderInfoList'][0]['orderStatus'];
            $cardType= $result['espOrderInfoList'][0]['cardType'];
            
            $return= @'<table  width="800px" border="0" cellspacing="0" cellpadding="0">
							<thead>
								<tr>
									<td  width="200px"><span  >Date</span></th>
									<td><span  >Payment method</span></th>
									<td><span  >Transaction ID</span></th>
									<td><span  >Amount</span></th>
									<td><span  >Order Status</span></th>
									<td></th>
								</tr>
							</thead>
							<tbody> 	<tr> <td>'.$tradeTime.'</td>	<td>'.$cardType.'</td>'.
									'<td>'.$tradeNo.'</td>'.
									'<td>'.$amountLoc.'</td>'
									.'<td>'.
								      $orderStatus
									.'</td>'
									.@'<td class="actions">  	</td> </tr>	</tbody>
						</table>';
        }
    }
    else
    {
        $return= @'<tr>
									<td class="list-empty hidden-print" colspan="6">
										<div class="list-empty-msg">
											<i class="icon-warning-sign list-empty-icon"></i>
											 No payment methods are available 
										</div>
									</td>
								</tr>';
    }
    echo $return;
    }
 }
 EspOrderCore::getPayOrder();

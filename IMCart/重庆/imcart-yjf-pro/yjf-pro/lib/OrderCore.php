<?
// Don't Change This file 
class OrderCore
{
	public static function getOrder($itemno)
	{
		$result = array();
		if(!$itemno)
		{
			$result['code'] = 500 ;
			return $result ;
		}
		$params = array();
		$params['itemno'] = $itemno ;
		$params['page_size'] = 1 ;
		$params['page_no'] = 1 ;
		$url = "http://" . API_DOMAIN . "/index.php?m=api&c=erpApi&a=GetOrderList" ;
		$res = trim(self::openapi_curl_get($url, $params)) ;
		
		if($res)
		{
			$obj = json_decode($res,true);
			if($obj['code'])
			{
				$result['code'] = $obj['code'] ;
				return $result ;
			}
			if($obj['orderlist'][0])
			{
				$result['code'] = 200 ;
				$result['data'] = $obj['orderlist'][0] ;
				return $result ;
			}
			else
			{
				$result['code'] = 404 ;
				return $result ;
			}
		}
		else
		{
			$result['code'] = 501 ;
			return $result ;
		}
	}

	public static function orderSuccess($itemno,$param,$type='server',$redirect_type=0)
	{
		$url = "http://" . REDIRECT_DOMAIN . "/pay_result_iframe_".$type.".html?itemno=" . $itemno . "&trade_no=" . $param['trade_no'] . "&amount=" . $param['amount'] . "&currency_code=" . $param['currency_code'] . "&pay_status=201&redirect_type=" . $redirect_type . "&sign=" . md5($itemno."201") ;
		//echo $url ;
		if($type=='server')
		{
			self::getRemoteData($url);
		}
		else
		{
			self::redirect($url);
		}
	}

	public static function orderFailure($itemno,$param,$type='server',$redirect_type=0)
	{
		$url = "http://" . REDIRECT_DOMAIN . "/pay_result_iframe_".$type.".html?itemno=" . $itemno  . "&pay_status=101&redirect_type=" . $redirect_type . "&msg=" . urlencode($param['msg']) . "&sign=" . md5($itemno."101") ;
		if($type=='server')
		{
			self::getRemoteData($url);
		}
		else
		{
			self::redirect($url);
		}
	}

	public static function orderChecking($itemno,$param,$type='server',$redirect_type=0)
	{
		$url = "http://" . REDIRECT_DOMAIN . "/pay_result_iframe_".$type.".html?itemno=" . $itemno . "&pay_status=102&redirect_type=" . $redirect_type . "&sign=" . md5($itemno."102") ;
		if($type=='server')
		{
			self::getRemoteData($url);
		}
		else
		{
			self::redirect($url);
		}
	}

	public static function  generateSign($params)
	{
		$api_signkey = md5(md5(API_KEY.API_SECRET));
		//所有请求参数按照字母先后顺序排序
		ksort($params);
		//定义字符串开始 结尾所包括的字符串
		$stringToBeSigned = API_KEY;
		//把所有参数名和参数值串在一起
		foreach ($params as $k => $v)
		{
		  $stringToBeSigned .= "$k$v";
		}
		unset($k, $v);
		//把venderKey夹在字符串的两端
		$stringToBeSigned .= $api_signkey;
		//使用MD5进行加密，再转化成大写
		return strtoupper(md5($stringToBeSigned));
	}

	public static function redirect($url)
	{
		echo "<script>";
		echo "location.href='$url';";
		echo "</script>";
		die();	
	}

	public static function openapi_curl_get($url, $params=array()) 
	{
		$api_signkey = md5(md5(API_KEY.API_SECRET));
		$params['api_signkey'] = $api_signkey;
		$params['timestamp']  = date('Y-m-d H:i:s', time());
		$params['v'] = 'V1.2.8';
		$sign = self::generateSign ($params);
		$params['sign'] = $sign;
		$ch  = curl_init ();
		$a   = array(" ");
		$b   = array("%20");
		$url = str_replace($a, $b, $url);
		@curl_setopt($ch, CURLOPT_URL, $url); 
		@curl_setopt($ch,CURLOPT_HEADER,0);   
		@curl_setopt($ch,CURLOPT_TIMEOUT,30);   
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		if(count($params)>0)
		{
			@curl_setopt($ch, CURLOPT_POST, 1 );
			@curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		$data = @curl_exec( $ch );
		curl_close ( $ch );      
		return $data;
	}
	
	public static function getRemoteData($url,$charsetfrom="",$charsetto="",$timeout=60, $post_data=array())
	{
		$ch  = @curl_init();
		$a   = array(" ");
		$b   = array("%20");
		$url = str_replace($a, $b, $url);
		@curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		@curl_setopt($ch,CURLOPT_HEADER,0);   
		@curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);   
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
        if(count($post_data)>0)
        {
            @curl_setopt($ch, CURLOPT_POST, 1 );
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
		//在需要用户检测的网页里需要增加下面两行 
		//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
		//curl_setopt($ch, CURLOPT_USERPWD, US_NAME.":".US_PWD); 
		$contents = @curl_exec($ch); 
		@curl_close($ch); 
		if( $charsetfrom!="" && $charsetto!="" )
		{
			$contents = iconv($charsetfrom, $charsetto,$contents);
		}
		return $contents; 
	}
}
?>
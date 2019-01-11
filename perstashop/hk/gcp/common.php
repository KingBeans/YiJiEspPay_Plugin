<?php

function gcp_getSignByArray($array,$hashkey)
{
  unset($array['sign']);
  return md5(http_build_query($array,"","&",PHP_QUERY_RFC3986).$hashkey);
}

function gcp_getSignByQueryString($string,$hashkey)
{
  $array= explode("&sign=", $string);
  return md5($array[0].$hashkey);
}

function gcp_checkSign($var,$hashkey)
{
  if(is_array($var)){
    $osign = $var['sign'];
    $csign = gcp_getSignByArray($var,$hashkey);
  }else{
    $array = explode("&sign=", $var);
    $osign = $array[1];
    $csign = gcp_getSignByQueryString($var,$hashkey);
  }

  return $osign == $csign;
}

function gcp_getPayQueryString($arrayr,$hashkey)
{
  $return="";
  foreach ($arrayr as $field => $value) {
    $return .= '&'.$field.'='.rawurlencode(unescape($value));
  }
  $return = ltrim($return, '&');
  $return .= '&sign='.gcp_getSignByQueryString($return,$hashkey);
  return $return;
}

function gcp_Sync($syncurl,$hashkey)
{
  $domain=$_SERVER["HTTP_REFERER"];
  $domainArr=explode("/", $domain);
  $query="domain=".$domainArr[0].'//'.$domainArr[2]."&timeTick=".time();
  $url = $syncurl."?".$query."&sign=".gcp_getSignByQueryString($query,$hashkey);
  $file = file_get_contents($url);
  if($file!='true')
    return false;
  else
    return true;
}


function gcp_checkInput($value)
{
  if (get_magic_quotes_gpc())
  {
    $value = stripslashes($value);
  }
  if (!is_numeric($value))
  {
    $value = mysql_real_escape_string($value);
  }
  return $value;
}


function gcp_result($variables){
  $json = json_encode($variables);
  header ( "gcp-result:".$json );
  exit ($json);
}

function unescape($str) {
    $str = rawurldecode($str);
    preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U",$str,$r);
    $ar = $r[0];
    //print_r($ar);
    foreach($ar as $k=>$v) {
        if(substr($v,0,2) == "%u"){
            $ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,-4)));
        }
        elseif(substr($v,0,3) == "&#x"){
            $ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,3,-1)));
        }
        elseif(substr($v,0,2) == "&#") {
             
            $ar[$k] = iconv("UCS-2BE","UTF-8",pack("n",substr($v,2,-1)));
        }
    }
    return join("",$ar);
}

function get_server_domain() {   
    $domain = 'http';   
    if (getenv("HTTPS") == "on") 
      $domain .= "s";     
    $domain .= "://"; 
    $domain .= $_SERVER["HTTP_HOST"];

    return $domain;
}

function get_client_ip() {   
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      $pos    =   array_search('unknown',$arr);
      if(false !== $pos) unset($arr[$pos]);
      $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
      $ip     =   $_SERVER['REMOTE_ADDR'];
    }else
      $ip = "Unknow";

    return $ip;
}

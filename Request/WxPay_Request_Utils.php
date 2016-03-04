<?php

class WxPay_Request_Utils
{
	public static function getIp()
	{
		foreach(array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $key)
		{
			if(isset($_SERVER[$key]))
			{
				return $_SERVER[$key];
			}
		}
		return '';
	}

	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}

	public static function sign($appKey, $params)
	{
		ksort($params);
		$buff = "";
		foreach ($params as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");

		$string = $buff ."&key=".$appKey;
		return strtoupper(md5($string));
	}

	public static function getSignedXml($appKey, $params)
	{
		$params['sign'] = self::sign($appKey, $params);
		$xml = "<xml>";
    	foreach ($params as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}

	public static function log($type, $msg)
	{

	}
}
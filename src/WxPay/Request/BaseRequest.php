<?php

/**
 * Class WxPay_Request_BaseRequest
 *
 * @property WxPay_Account $account 微信账号信息
 * @property array $requiredParams 必选参数
 */
abstract class WxPay_Request_BaseRequest
{
	public $account;

	public $url = '';
	public $params = array();
	public $requiredParams = array();

	public $useCert = false;

	public function __construct($account)
	{
		$this->account = $account;
	}

	/**
	 * @param $params
	 * @return bool | array
	 */
	public function request($params)
	{
		$this->params = $params;
		try
		{
			if($this->beforeRequest())
			{
				$response = $this->doRequest();
				$this->afterRequest($response);
				return $response;
			}
		}catch(Exception $ex)
		{
			if(WXPAY_DEBUG)
			{
				WxPay_Request_Utils::log('请求异常', $ex->getMessage(), $this->params);
			}
		}
		return false;
	}

	protected abstract function doRequest();

	protected function beforeRequest()
	{
		foreach($this->requiredParams as $param)
		{
			if(!isset($this->params[$param]))
			{
				return false;
			}
		}
		return true;
	}

	protected function afterRequest($response)
	{

	}

	protected function appendInfo()
	{
		$this->params['appid'] = $this->account->appId;
		$this->params['mch_id'] = $this->account->mchId;
		$this->params['nonce_str'] = WxPay_Request_Utils::getNonceStr();
		return $this;
	}

	protected function xmlCurl()
	{
		$xml = WxPay_Request_Utils::getSignedXml($this->account->appKey, $this->params);
		$response = $this->curl($this->url, $xml);
		$params = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		if(!is_array($params))
		{
			throw new Exception('返回值无法解析');
		}

		$sign = WxPay_Request_Utils::sign($this->account->appKey, $params);
		if( !isset($params['sign']) || $sign != $params['sign'])
		{
			throw new Exception('返回数据的签名错误');
		}
		return $params;
	}

	protected function curl($url, $xml)
	{
		//初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, WXPAY_TIMEOUT);
		
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		if($this->useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, __DIR__.'/'.$this->account->sslCert);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, __DIR__.'/'.$this->account->sslKey);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
        $data = curl_exec($ch);

		if(WXPAY_DEBUG)
		{
			WxPay_Request_Utils::log('请求结果', $data);
		}

		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new Exception("curl出错，错误码:$error");
		}
	}
}
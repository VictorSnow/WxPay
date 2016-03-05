<?php

class WxPay_Request_UnifiedOrder extends WxPay_Request_BaseRequest
{
	public $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

	public $requiredParams = array(
		'out_trade_no', 'body', 'total_fee', 'trade_type', 'notify_url'
	);


	protected function jsSign($prepayId)
	{
		$params = array(
			'timeStamp' => time(),
			'nonceStr' => WxPay_Request_Utils::getNonceStr(),
			'package' => 'prepay_id='.$prepayId,
			'signType' => 'MD5',
			'appId' => $this->account->appId
		);
		$sign = WxPay_Request_Utils::sign($this->account->appKey, $params);
		$params['sign'] = $sign;
		return $params;
	}

	protected function appSign($prepayId)
	{
		$params = array(
			'appid' => $this->account->appId,
			'partnerid' => $this->account->mchId,
			'prepayid' => $prepayId,
			'package' => 'Sign=WXPay',
			'noncestr' => WxPay_Request_Utils::getNonceStr(),
			'timestamp' => time()
		);
		$sign = WxPay_Request_Utils::sign($this->account->appKey, $params);
		$params['sign'] = $sign;
		return $params;
	}


	protected function doRequest()
	{
		if(!is_numeric($this->params['total_fee']) || $this->params['total_fee'] <=0)
		{
			throw new \Exception('统一下单金额错误');
		}

		if(strlen($this->params['out_trade_no']) > 32)
		{
			throw new \Exception('商户系统内部的订单号需要小于32个字符');
		}

		if($this->params['trade_type'] == WXPAY_TYPE_JSAPI && !isset($this->params['openid']))
		{
			throw new \Exception('JSAPI模式下需要openid参数');
		}

		if($this->params['trade_type'] == WXPAY_TYPE_NATIVE && !isset($this->params['product_id']))
		{
			throw new \Exception('NATIVE模式下需要producct_id参数');
		}

		$this->params['spbill_create_ip'] = WxPay_Request_Utils::getIp();

		$response = $this->appendInfo()->xmlCurl();
		if($response['return_code'] != 'SUCCESS' || $response['result_code'] != 'SUCCESS')
		{
			return false;
		}

		$prepayId = $response['prepay_id'];
		if($this->params['trade_type'] == WXPAY_TYPE_JSAPI)
		{
			return $this->jsSign($prepayId);
		}
		elseif($this->params['trade_type'] == WXPAY_TYPE_APP)
		{
			return $this->appSign($prepayId);
		}

		return false;
	}
}
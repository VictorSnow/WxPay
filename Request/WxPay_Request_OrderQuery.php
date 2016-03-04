<?php

class WxPay_Request_OrderQuery extends WxPay_Request_BaseRequest
{
	public $url = 'https://api.mch.weixin.qq.com/pay/orderquery';

	protected function doRequest()
	{
		if(!isset($this->params['out_trade_no']) || !isset($this->params['transaction_id']))
		{
			throw new Exception('out_trade_no, transaction_id至少需要一个');
		}
		$response = $this->appendInfo()->xmlCurl();
		return $response;
	}
}
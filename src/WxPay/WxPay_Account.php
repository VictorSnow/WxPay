<?php

include_once __DIR__.'/Request/WxPay_Request_Utils.php';
include_once __DIR__.'/Request/WxPay_Request_BaseRequest.php';

defined('WXPAY_DEBUG')  or define('WXPAY_DEBUG', false);
defined('WXPAY_TIMEOUT') or  define('WXPAY_TIMEOUT', 30);

define('WXPAY_TYPE_NATIVE', 'NATIVE');
define('WXPAY_TYPE_JSAPI', 'JSAPI');
define('WXPAY_TYPE_APP', 'APP');
define('WXPAY_TYPE_MICROPAY', 'MICROPAY');

class WxPay_Account
{
	public $appId = '';
	public $mchId = '';
	public $appKey = '';
	public $appSecret = '';

	public $sslKey = '';
	public $sslCert = '';

	public static $accountSettings = array(
		'default' => array(
			'appId' => '',
			'mchId' => '',
			'appKey' => '',
			'appSecret' => ''
		)
	);

	public function __construct($settings)
	{
		foreach($settings as $key => $value)
		{
			$this->{$key} = $value;
		}
	}

	/**
	 * support WxPay_Account::getAccount('default')->orderQuery
	 * @param $request
	 * @return WxPay_Request_BaseRequest
	 */
	public function __get($request)
	{
		$className = 'WxPay_Request_'.ucfirst($request);
		$requestClassPath = __DIR__.DIRECTORY_SEPARATOR.'Request'.DIRECTORY_SEPARATOR.$className.'.php';

		if(file_exists($requestClassPath))
		{
			include_once $requestClassPath;
			/**
			 * @var WxPay_Request_BaseRequest $request
			 */
			$request = new $className($this);
			return $request;
		}
		return null;
	}

	public static function init($accountSettings)
	{
		self::$accountSettings = $accountSettings;
	}

	public static function getAccount($type = '')
	{
		if(empty($type) || !isset(self::$accountSettings[$type]))
		{
			$type = 'default';
		}

		$settings = self::$accountSettings[$type];
		return new WxPay_Account($settings);
	}
}
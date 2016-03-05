<?php

define('WXPAY_DEBUG', true);

include __DIR__.'/../src/WxPay/Account.php';

class BaseTest extends PHPUnit_Framework_TestCase
{
    protected $account;

    public function __construct()
    {
        $accountSettings = include __DIR__.'/account.php';
        WxPay_Account::init($accountSettings);
        $this->account = WxPay_Account::getAccount();
        parent::__construct();
    }
}
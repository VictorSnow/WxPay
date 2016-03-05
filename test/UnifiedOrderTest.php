<?php

class UnifiedOrderTest extends BaseTest
{
    public function testUnified()
    {
        $request = $this->account->unifiedOrder;
        $params = $request->request(array(
            'trade_type' => 'APP',
            'out_trade_no' => 'testVictor_1457147632q5ei6mmsyb',
            'body' => '测试',
            'total_fee' => 1,
            'notify_url' => 'http://www.51xuanshi.com'
        ));
        $this->assertNotEquals($params, false);
        $this->assertArrayHasKey('prepayid', $params);
    }
}
<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class SmsesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test',
        'message' => 'API test message'
    );

    public function testGet()
    {
        $apiContext = $this->getContext('smses');
        $response   = $apiContext->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('smses');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }


    public function testCreateAndDelete()
    {
        $smsApi   = $this->getContext('smses');
        $response = $smsApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the sms
        $response = $smsApi->delete($response['sms']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $smsApi   = $this->getContext('smses');
        $response = $smsApi->edit(10000, $this->testPayload);

        //there should be an error as the sms shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $smsApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $smsApi->edit(
            $response['sms']['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the sms
        $response = $smsApi->delete($response['sms']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $smsApi = $this->getContext('smses');
        $response    = $smsApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the sms
        $response = $smsApi->delete($response['sms']['id']);
        $this->assertErrors($response);
    }
}

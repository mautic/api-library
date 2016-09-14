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
    public function testGet()
    {
        $apiContext = $this->getContext('smses');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('smses');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }


    public function testCreateAndDelete()
    {
        $smsApi = $this->getContext('smses');
        $sms    = $smsApi->create(
            array(
                'name' => 'test',
                'message' => 'API test message'
            )
        );

        $message = isset($sms['error']) ? $sms['error']['message'] : '';
        $this->assertFalse(isset($sms['error']), $message);

        //now delete the sms
        $result = $smsApi->delete($sms['sms']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $smsApi = $this->getContext('smses');
        $sms    = $smsApi->edit(
            10000,
            array(
                'name' => 'test',
                'message' => 'API test message'
            )
        );

        //there should be an error as the sms shouldn't exist
        $this->assertTrue(isset($sms['error']), $sms['error']['message']);

        $sms = $smsApi->create(
            array(
                'name' => 'test',
                'message' => 'API test message'
            )
        );

        $message = isset($sms['error']) ? $sms['error']['message'] : '';
        $this->assertFalse(isset($sms['error']), $message);

        $sms = $smsApi->edit(
            $sms['sms']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($sms['error']) ? $sms['error']['message'] : '';
        $this->assertFalse(isset($sms['error']), $message);

        //now delete the sms
        $result = $smsApi->delete($sms['sms']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $smsApi = $this->getContext('smses');
        $sms    = $smsApi->edit(
            10000,
            array(
                'name' => 'test',
                'message' => 'API test message',
                // following cannot be null
                'language' => 'en',
                'isPublished' => 1
            ),
            true
        );

        $message = isset($sms['error']) ? $sms['error']['message'] : '';
        $this->assertFalse(isset($sms['error']), $message);

        //now delete the sms
        $result = $smsApi->delete($sms['sms']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

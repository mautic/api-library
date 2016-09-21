<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class NotificationsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test',
        'heading' => 'API test heading',
        'message' => 'API test message'
    );

    public function testGet()
    {
        $apiContext = $this->getContext('notifications');
        $response   = $apiContext->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('notifications');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $notificationApi = $this->getContext('notifications');
        $response        = $notificationApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the notification
        $response = $notificationApi->delete($response['notification']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $notificationApi = $this->getContext('notifications');
        $response        = $notificationApi->edit(10000, $this->testPayload);

        //there should be an error as the notification shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $notificationApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $notificationApi->edit(
            $response['notification']['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the notification
        $response = $notificationApi->delete($response['notification']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $notificationApi = $this->getContext('notifications');
        $response        = $notificationApi->edit(10000, $this->testPayload,true);
        $this->assertErrors($response);

        //now delete the notification
        $response = $notificationApi->delete($response['notification']['id']);
        $this->assertErrors($response);
    }
}

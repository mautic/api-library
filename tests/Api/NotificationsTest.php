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
    public function testGet()
    {
        $apiContext = $this->getContext('notifications');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('notifications');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $notificationApi = $this->getContext('notifications');
        $notification    = $notificationApi->create(
            array(
                'name' => 'test',
                'heading' => 'API test heading',
                'message' => 'API test message'
            )
        );

        $message = isset($notification['error']) ? $notification['error']['message'] : '';
        $this->assertFalse(isset($notification['error']), $message);

        //now delete the notification
        $result = $notificationApi->delete($notification['notification']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $notificationApi = $this->getContext('notifications');
        $notification    = $notificationApi->edit(
            10000,
            array(
                'name' => 'test',
                'heading' => 'API test heading',
                'message' => 'API test message'
            )
        );

        //there should be an error as the notification shouldn't exist
        $this->assertTrue(isset($notification['error']), $notification['error']['message']);

        $notification = $notificationApi->create(
            array(
                'name' => 'test',
                'heading' => 'API test heading',
                'message' => 'API test message'
            )
        );

        $message = isset($notification['error']) ? $notification['error']['message'] : '';
        $this->assertFalse(isset($notification['error']), $message);

        $notification = $notificationApi->edit(
            $notification['notification']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($notification['error']) ? $notification['error']['message'] : '';
        $this->assertFalse(isset($notification['error']), $message);

        //now delete the notification
        $result = $notificationApi->delete($notification['notification']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $notificationApi = $this->getContext('notifications');
        $notification    = $notificationApi->edit(
            10000,
            array(
                'name' => 'test',
                'heading' => 'API test heading',
                'message' => 'API test message'
            ),
            true
        );

        $message = isset($notification['error']) ? $notification['error']['message'] : '';
        $this->assertFalse(isset($notification['error']), $message);

        //now delete the notification
        $result = $notificationApi->delete($notification['notification']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class EmailsTest extends MauticApiTestCase
{
    public function testGet()
    {
        $emailApi = $this->getContext('emails');
        $email    = $emailApi->get(1);

        $message = isset($email['error']) ? $email['error']['message'] : '';
        $this->assertFalse(isset($email['error']), $message);
    }

    public function testGetList()
    {
        $emailApi = $this->getContext('emails');
        $emails   = $emailApi->getList();

        $message = isset($emails['error']) ? $emails['error']['message'] : '';
        $this->assertFalse(isset($emails['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $emailApi = $this->getContext('emails');
        $email    = $emailApi->create(
            array(
                'name' => 'test',
                'body' => 'test'
            )
        );

        $message = isset($email['error']) ? $email['error']['message'] : '';
        $this->assertFalse(isset($email['error']), $message);

        //now delete the email
        $result = $emailApi->delete($email['email']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $emailApi = $this->getContext('emails');
        $email    = $emailApi->edit(
            10000,
            array(
                'name' => 'test',
                'body' => 'test'
            )
        );

        //there should be an error as the email shouldn't exist
        $this->assertTrue(isset($email['error']), $email['error']['message']);

        $email = $emailApi->create(
            array(
                'name' => 'test',
                'body' => 'test'
            )
        );

        $message = isset($email['error']) ? $email['error']['message'] : '';
        $this->assertFalse(isset($email['error']), $message);

        $email = $emailApi->edit(
            $email['email']['id'],
            array(
                'name' => 'test2',
                'body' => 'test2'
            )
        );

        $message = isset($email['error']) ? $email['error']['message'] : '';
        $this->assertFalse(isset($email['error']), $message);
    }

    public function testEditPatch()
    {
        $emailApi = $this->getContext('emails');
        $email    = $emailApi->edit(
            10000,
            array(
                'name' => 'test',
                'body' => 'test',
                // following cannot be null
                'isPublished' => 1,
                'language' => 'en'
            ),
            true
        );

        $message = isset($email['error']) ? $email['error']['message'] : '';
        $this->assertFalse(isset($email['error']), $message);
    }
}

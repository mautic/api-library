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
    protected $testPayload = array(
        'name' => 'test',
        'body' => 'test'
    );

    public function testGet()
    {
        $emailApi = $this->getContext('emails');
        $response = $emailApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $emailApi = $this->getContext('emails');
        $response = $emailApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $emailApi = $this->getContext('emails');
        $response = $emailApi->create($this->testPayload);

        $this->assertErrors($response);

        //now delete the email
        $response = $emailApi->delete($response['email']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $emailApi = $this->getContext('emails');
        $response = $emailApi->edit(10000, $this->testPayload);

        //there should be an error as the email shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $emailApi->create($this->testPayload);

        $this->assertErrors($response);

        $response = $emailApi->edit(
            $response['email']['id'],
            array(
                'name' => 'test2',
                'body' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the email
        $response = $emailApi->delete($response['email']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $emailApi = $this->getContext('emails');
        $response = $emailApi->edit(10000, $this->testPayload, true);

        $this->assertErrors($response);

        //now delete the email
        $response = $emailApi->delete($response['email']['id']);
        $this->assertErrors($response);
    }
}

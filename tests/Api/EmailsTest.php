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
        'subject' => 'test',
        'customHtml' => '<h1>test</h1>'
    );

    protected $context = 'emails';

    protected $itemName = 'email';

    public function testGet()
    {
        $emailApi = $this->getContext($this->context);
        $response = $emailApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $emailApi = $this->getContext($this->context);
        $response = $emailApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $emailApi = $this->getContext($this->context);
        $response = $emailApi->edit(10000, $this->testPayload);

        //there should be an error as the email shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $emailApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $emailApi->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2',
                'body' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the email
        $response = $emailApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $emailApi = $this->getContext($this->context);
        $response = $emailApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the email
        $response = $emailApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}

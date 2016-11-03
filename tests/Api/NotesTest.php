<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class NotesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'text' => 'Contact note created via API request',
        'type' => 'general',
    );

    protected $context = 'notes';

    protected $itemName = 'note';

    protected $skipPayloadAssertion = array('lead');

    public function setUp() {
        // Create a contact for test
        $apiContext = $this->getContext('contacts');
        $response = $apiContext->create(array('firstname' => 'Note API test'));
        $this->assertErrors($response);
        $this->testPayload['lead'] = $response['contact']['id'];
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList();
        $this->assertErrors($response);
        $this->assertTrue(isset($response[$this->context]));
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $updatePayload = array(
            'text' => 'test2',
        );

        $response = $apiContext->edit($response[$this->itemName]['id'], $updatePayload);
        $this->assertPayload($response, $updatePayload);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}

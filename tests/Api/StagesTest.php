<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class StagesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test'
    );

    protected $context = 'stages';

    protected $itemName = 'stage';

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

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
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the stage shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        $update = array(
            'name' => 'test'
        );

        $response = $apiContext->edit($response[$this->itemName]['id'], $update);
        $this->assertPayload($response, $update);

        //now delete the stage
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the stage
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response = $contactsContext->create(array('firstname' => 'API stages test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create stage
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $stage = $response[$this->itemName];

        // Add contact to the stage
        $response = $apiContext->addContact($stage['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Remove the contact from the stage
        $response = $apiContext->removeContact($stage['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Delete the contact and the stage
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $apiContext->delete($stage['id']);
        $this->assertErrors($response);
    }
}

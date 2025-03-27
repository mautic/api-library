<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class NotesTest extends MauticApiTestCase
{
    protected $skipPayloadAssertion = ['lead'];

    public function setUp(): void
    {
        $this->api         = $this->getContext('notes');
        $this->testPayload = [
            'text' => 'Contact note created via API request',
            'type' => 'general',
        ];

        // Create a contact for test
        $contactApi = $this->getContext('contacts');
        $response   = $contactApi->create(['firstname' => 'Note API test']);
        $this->assertErrors($response);
        $this->testPayload['lead'] = $response['contact']['id'];
    }

    public function tearDown(): void
    {
        // Delete a contact from test
        $this->api = $this->getContext('contacts');
        $response  = $this->api->delete($this->testPayload['lead']);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        // Test get contact notes endpoint
        $contactContext = $this->getContext('contacts');
        $responseNotes  = $contactContext->getContactNotes($this->testPayload['lead']);
        $this->assertErrors($responseNotes);
        $this->assertEquals($responseNotes['total'], 1);
        $this->assertFalse(empty($responseNotes['notes']));

        $response = $this->api->get($response[$this->api->itemName()]['id']);
        $this->assertPayload($response);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $editTo = [
            'text' => 'test2',
        ];
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }
}

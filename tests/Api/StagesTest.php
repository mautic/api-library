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

class StagesTest extends MauticApiTestCase
{
    private static $nextWeight;

    public function setUp(): void
    {
        $this->api         = $this->getContext('stages');
        $this->testPayload = [
            'name' => 'test',
        ];
    }

    protected function getTestPayload(): array
    {
        return [
            'name'   => sprintf('test %s', uniqid('', true)),
            'weight' => $this->getNextWeight(),
        ];
    }

    private function getNextWeight(): int
    {
        if (null === self::$nextWeight) {
            $response   = $this->api->getList('', 0, 1, 'weight', 'DESC');
            $firstStage = $response[$this->api->listName()][0] ?? [];
            $maxWeight  = isset($firstStage['weight']) ? (int) $firstStage['weight'] : 0;

            self::$nextWeight = $maxWeight + 1;
        }

        return self::$nextWeight++;
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
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = [
            'name' => 'test2',
        ];
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response        = $contactsContext->create(['firstname' => 'API stages test']);
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create stage
        $payload  = $this->getTestPayload();
        $response = $this->api->create($payload);
        $this->assertPayload($response, $payload);
        $stage = $response[$this->api->itemName()];

        // Add contact to the stage
        $response = $this->api->addContact($stage['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Remove the contact from the stage
        $response = $this->api->removeContact($stage['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Delete the contact and the stage
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $this->api->delete($stage['id']);
        $this->assertErrors($response);
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }
}

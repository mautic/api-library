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

use Mautic\Api\Segments;

class SegmentsTest extends MauticApiTestCase
{
    /**
     * @var Segments
     */
    protected $api;

    public function setUp(): void
    {
        $this->api         = $this->getContext('segments');
        $this->testPayload = [
            'name'        => 'List all GMail contacts',
            'description' => 'Created via API Library unit tests',
            'isGlobal'    => true,
            'filters'     => [
                [
                    'glue'       => 'and',
                    'field'      => 'email',
                    'object'     => 'lead',
                    'type'       => 'email',
                    'properties' => [
                        'filter'   => '*@gmail.com',
                    ],
                    'operator'   => 'like',
                ],
            ],
        ];
    }

    public function testGetList()
    {
        $response = $this->api->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        // Create some items first
        $itemIds  = [];
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->api->itemName()]['id'];
        $response  = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->api->itemName()]['id'];

        $search = 'ids:'.implode(',', $itemIds);

        $response = $this->api->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response['lists'] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $this->api->delete($item['id']);
            $this->assertErrors($response);
        }
    }

    public function testGetListMinimal()
    {
        $response = $this->api->getList('', 0, 0, '', 'ASC', false, true);
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        // Test Create
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $this->api->get($response[$this->api->itemName()]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $response = $this->api->edit(10000, $this->testPayload);

        // there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($response['errors'][0]), $response['errors'][0]['message']);

        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $update = [
            'name' => 'test2',
        ];

        $response = $this->api->edit($response[$this->api->itemName()]['id'], $update);
        $this->assertPayload($response, $update);

        // now delete the segment
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $response = $this->api->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        // now delete the segment
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactApi = $this->getContext('contacts');
        $response   = $contactApi->create(['firstname' => 'API segments test']);
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create segment
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $segment = $response[$this->api->itemName()];

        // Add the contact to the segment
        $response   = $this->api->addContact($segment['id'], $contact['id']);
        $this->assertErrors($response);

        // Test get contact segments API endpoint
        $response = $contactApi->getContactSegments($contact['id']);
        $this->assertErrors($response);
        $this->assertEquals($response['total'], 1);
        $this->assertFalse(empty($response['lists']));

        // Remove the contact from the segment
        $response = $this->api->removeContact($segment['id'], $contact['id']);
        $this->assertErrors($response);

        // Delete the contact and the segment
        $response = $contactApi->delete($contact['id']);
        $this->assertErrors($response);
        $response = $this->api->delete($segment['id']);
        $this->assertErrors($response);
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints(null, function ($response, &$batch, $action) {
            switch ($action) {
                // Add extra values to the batch after create, as the API returns them in the response.
                // This is probably related to https://github.com/mautic/mautic/pull/8649
                case 'create':
                    foreach ($batch as &$item) {
                        $item['filters'][0] += ['filter' => '*@gmail.com', 'display' => null];
                    }
                    break;
            }
        });
    }
}

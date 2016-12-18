<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class SegmentsTest extends MauticApiTestCase
{
    public function setUp() {
        $this->api = $this->getContext('segments');
        $this->testPayload = array(
            'name' => 'API test'
        );
    }

    public function testGetList()
    {
        $response = $this->api->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        // Create some items first
        $itemIds = array();
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->api->itemName()]['id'];
        $response = $this->api->create($this->testPayload);
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
        $response = $this->api->getList('', 0,  0, '', 'ASC', false, true);
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

        //there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $update = array(
            'name' => 'test2'
        );

        $response = $this->api->edit($response[$this->api->itemName()]['id'], $update);
        $this->assertPayload($response, $update);

        //now delete the segment
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $response = $this->api->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the segment
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactApi = $this->getContext('contacts');
        $response = $contactApi->create(array('firstname' => 'API segments test'));
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
}

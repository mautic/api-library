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
    protected $testPayload = array(
        'name' => 'API test'
    );

    protected $context = 'segments';

    protected $itemName = 'list'; // this will be changed to 'segment' in Mautic 3

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        $apiContext = $this->getContext($this->context);

        // Create some items first
        $itemIds = array();
        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->itemName]['id'];
        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->itemName]['id'];

        $search = 'ids:'.implode(',', $itemIds);

        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response['lists'] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $apiContext->delete($item['id']);
            $this->assertErrors($response);
        }
    }

    public function testGetListMinimal()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList('', 0,  0, '', 'ASC', false, true);
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']); 
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
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        $update = array(
            'name' => 'test2'
        );

        $response = $apiContext->edit($response[$this->itemName]['id'], $update);
        $this->assertPayload($response, $update);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response = $contactsContext->create(array('firstname' => 'API segments test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create segment
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $segment = $response[$this->itemName];

        // Add the contact to the segment
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->addContact($segment['id'], $contact['id']);
        $this->assertErrors($response);

        // Test get contact segments API endpoint
        $contactContext = $this->getContext('contacts');
        $response = $contactContext->getContactSegments($contact['id']);
        $this->assertErrors($response);
        $this->assertEquals($response['total'], 1);
        $this->assertFalse(empty($response['lists']));

        // Remove the contact from the segment
        $response = $apiContext->removeContact($segment['id'], $contact['id']);
        $this->assertErrors($response);

        // Delete the contact and the segment
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $apiContext->delete($segment['id']);
        $this->assertErrors($response);
    }
}

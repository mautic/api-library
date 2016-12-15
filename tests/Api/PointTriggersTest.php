<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class PointTriggersTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test',
        'description' => 'created as a API test',
        'points' => 5,
        'color' => '4e5d9d',
        'trigger_existing_leads' => false,
        'events' => array(
            array(
                'name' => 'tag test event',
                'description' => 'created as a API test',
                'type' => 'lead.changetags',
                'order' => 1,
                'properties' => array(
                    'add_tags' => array('tag-a'),
                    'remove_tags' => array()
                )
            ),
            array(
                'name' => 'send email test event',
                'description' => 'created as a API test',
                'type' => 'email.send',
                'order' => 2,
                'properties' => array(
                    'email' => 1
                )
            )
        )
    );

    protected $context = 'pointTriggers';

    protected $itemName = 'trigger';

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->itemName]['id']), 'The point trigger id is empty.');
        $this->assertSame($response[$this->itemName]['name'], $this->testPayload['name']);
        $this->assertFalse(empty($response[$this->itemName]['events']), 'The point trigger event array is empty.');
        $lastEvent = array_pop($response[$this->itemName]['events']);
        $this->assertSame($lastEvent['name'], $this->testPayload['events'][1]['name']);
    }

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

        foreach ($response['triggers'] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $apiContext->delete($item['id']);
            $this->assertErrors($response);
        }
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

        //there should be an error as the point shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2',
            )
        );

        $this->assertErrors($response);
        // $this->assertTrue(empty($response[$this->itemName]['id']), 'The point id is empty.');
        $this->assertSame($response[$this->itemName]['name'], 'test2');

        //now delete the point
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the point
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEventDeleteViaPut()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        // Remove the trigger events
        unset($response[$this->itemName]['events']);

        // Edit the same entitiy without the fields and actions
        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            $response[$this->itemName],
            true
        );

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->itemName]['events']), 'Trigger events were not deleted via PUT request');

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testDeleteEvents()
    {
        $eventIds  = array();
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        foreach ($response[$this->itemName]['events'] as $event) {
            $eventIds[] = $event['id'];
        }

        $response = $apiContext->deleteTriggerEvents($response[$this->itemName]['id'], $eventIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->itemName]['events']), 'Events were not deleted');

        //now delete the trigger
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testGetEventTypes()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getEventTypes();

        $this->assertErrors($response);
        $this->assertFalse(empty($response['eventTypes']), 'The eventTypes array is empty.');
    }
}

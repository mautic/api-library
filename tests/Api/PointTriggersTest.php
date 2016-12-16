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
    public function setUp() {
        $this->api = $this->getContext('pointTriggers');
        $this->testPayload = array(
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
    }

    protected function assertPayload($response, array $payload = array())
    {
        if (empty($payload)) {
            $payload = $this->testPayload;
        }

        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->api->itemName()]['id']), 'The point trigger id is empty.');
        $this->assertSame($response[$this->api->itemName()]['name'], $payload['name']);
        $this->assertFalse(empty($response[$this->api->itemName()]['events']), 'The point trigger event array is empty.');
        $lastEvent = array_pop($response[$this->api->itemName()]['events']);
        $this->assertSame($lastEvent['name'], $payload['events'][1]['name']);
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
        $editTo = array(
            'name' => 'test2',
            'events' => $this->testPayload['events']
        );
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testEventDeleteViaPut()
    {
        $response = $this->api->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        // Remove the trigger events
        unset($response[$this->api->itemName()]['events']);

        // Edit the same entitiy without the fields and actions
        $response = $this->api->edit(
            $response[$this->api->itemName()]['id'],
            $response[$this->api->itemName()],
            true
        );

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->api->itemName()]['events']), 'Trigger events were not deleted via PUT request');

        //now delete the form
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testDeleteEvents()
    {
        $eventIds  = array();
        $response   = $this->api->create($this->testPayload);
        $this->assertErrors($response);

        foreach ($response[$this->api->itemName()]['events'] as $event) {
            $eventIds[] = $event['id'];
        }

        $response = $this->api->deleteTriggerEvents($response[$this->api->itemName()]['id'], $eventIds);
        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->api->itemName()]['events']), 'Events were not deleted');

        //now delete the trigger
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testGetEventTypes()
    {
        $response   = $this->api->getEventTypes();
        $this->assertErrors($response);
        $this->assertFalse(empty($response['eventTypes']), 'The eventTypes array is empty.');
    }
}

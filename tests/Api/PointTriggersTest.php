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

    protected function assertPayload($response) {
        $this->assertErrors($response);
        $this->assertFalse(empty($response['trigger']['id']), 'The point trigger id is empty.');
        $this->assertSame($response['trigger']['name'], $this->testPayload['name']);
        $this->assertFalse(empty($response['trigger']['events']), 'The point trigger event array is empty.');
        $lastEvent = array_pop($response['trigger']['events']);
        $this->assertSame($lastEvent['name'], $this->testPayload['events'][1]['name']);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('pointTriggers');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext('pointTriggers');

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $apiContext->get($response['trigger']['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response['trigger']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext('pointTriggers');
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the point shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response['trigger']['id'],
            array(
                'name' => 'test2',
            )
        );

        $this->assertErrors($response);
        // $this->assertTrue(empty($response['trigger']['id']), 'The point id is empty.');
        $this->assertSame($response['trigger']['name'], 'test2');

        //now delete the point
        $response = $apiContext->delete($response['trigger']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext('pointTriggers');
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the point
        $response = $apiContext->delete($response['trigger']['id']);
        $this->assertErrors($response);
    }

    public function testActionDeleteViaPut()
    {
        $apiContext = $this->getContext('pointTriggers');

        // Firstly create a form with fields
        $response = $apiContext->edit(10000, $this->testPayload, true);

        $this->assertErrors($response);

        // Remove the trigger events
        unset($response['trigger']['events']);

        // Edit the same entitiy without the fields and actions
        $response = $apiContext->edit(
            $response['trigger']['id'],
            $response['trigger'],
            true
        );

        $this->assertErrors($response);
        $this->assertTrue(empty($response['trigger']['events']), 'Trigger events were not deleted via PUT request');

        //now delete the form
        $response = $apiContext->delete($response['trigger']['id']);
        $this->assertErrors($response);
    }

    public function testDeleteEvents()
    {
        $eventIds  = array();
        $apiContext = $this->getContext('pointTriggers');
        $response   = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        foreach ($response['trigger']['events'] as $event) {
            $eventIds[] = $event['id'];
        }

        $response = $apiContext->deleteActions($response['trigger']['id'], $eventIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response['trigger']['events']), 'Events were not deleted');

        //now delete the trigger
        $response = $apiContext->delete($response['trigger']['id']);
        $this->assertErrors($response);
    }
}

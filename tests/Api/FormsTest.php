<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class FormsTest extends MauticApiTestCase
{
    /**
     * Payload of example form to test the endpoints with
     *
     * @var array
     */
    protected $testPayload = array(
        'name' => 'test',
        'formType' => 'standalone',
        'description' => 'API test',
        'fields' => array(
            array(
                'label' => 'field name',
                'type' => 'text'
            )
        ),
        'actions' => array(
            array(
                'name' => 'action name',
                'description' => 'action desc',
                'type' => 'lead.pointschange',
                'properties' => array(
                    'operator' => 'plus',
                    'points' => 2
                )
            )
        )
    );

    protected $context = 'forms';

    protected $itemName = 'form';

    /**
     * Check if the response contains a form
     *
     * @param array $response
     */
    protected function assertForm($response)
    {
        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->itemName]['fields']), 'The form field array is empty.');
        $this->assertFalse(empty($response[$this->itemName]['actions']), 'The form action array is empty.');
        $this->assertSame($response[$this->itemName]['name'], $this->testPayload['name']);
        $lastField = array_pop($response[$this->itemName]['fields']);
        $lastAction = array_pop($response[$this->itemName]['actions']);
        $this->assertSame($lastField['label'], $this->testPayload['fields'][0]['label']);
        $this->assertSame($lastAction['name'], $this->testPayload['actions'][0]['name']);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertForm($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertForm($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertForm($response);
    }

    public function testDeleteFields()
    {
        $fieldIds   = array();
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        foreach ($response[$this->itemName]['fields'] as $field) {
            $fieldIds[] = $field['id'];
        }

        $response = $apiContext->deleteFields($response[$this->itemName]['id'], $fieldIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->itemName]['fields']), 'Fields were not deleted');

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testDeleteActions()
    {
        $actionIds  = array();
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        foreach ($response[$this->itemName]['actions'] as $action) {
            $actionIds[] = $action['id'];
        }

        $response = $apiContext->deleteActions($response[$this->itemName]['id'], $actionIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->itemName]['actions']), 'Actions were not deleted');

        //now delete the form
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

        $lastField = array_pop($response[$this->itemName]['fields']);
        $lastAction = array_pop($response[$this->itemName]['actions']);
        $lastField['label'] = 'edited field';
        $lastAction['name'] = 'edited action';

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2',
                'formType' => 'standalone',
                'fields' => array(
                    $lastField
                ),
                'actions' => array(
                    $lastAction
                )
            )
        );

        $this->assertErrors($response);
        $this->assertTrue(!empty($response[$this->itemName]['fields']), 'The form field array is empty.');
        $this->assertTrue(!empty($response[$this->itemName]['actions']), 'The form action array is empty.');
        $lastField = array_pop($response[$this->itemName]['fields']);
        $lastAction = array_pop($response[$this->itemName]['actions']);
        $this->assertSame($lastField['label'], 'edited field');
        $this->assertSame($lastAction['name'], 'edited action');

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);

        $this->assertForm($response);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testFieldAndActionDeleteViaPut()
    {
        $apiContext = $this->getContext($this->context);

        // Firstly create a form with fields
        $response = $apiContext->edit(10000, $this->testPayload, true);

        $this->assertErrors($response);

        // Remove fields and actions
        unset($response[$this->itemName]['fields']);
        unset($response[$this->itemName]['actions']);

        // Edit the same entitiy without the fields and actions
        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            $response[$this->itemName],
            true
        );

        $this->assertErrors($response);
        $this->assertTrue(empty($response[$this->itemName]['fields']), 'Fields were not deleted via PUT request');
        $this->assertTrue(empty($response[$this->itemName]['actions']), 'Actions were not deleted via PUT request');

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}

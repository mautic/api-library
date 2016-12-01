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
    protected $testForm = array(
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

    /**
     * Check if the response contains a form
     *
     * @param array $response
     */
    protected function assertForm($response)
    {
        $this->assertErrors($response);
        $this->assertFalse(empty($response['form']['fields']), 'The form field array is empty.');
        $this->assertFalse(empty($response['form']['actions']), 'The form action array is empty.');
        $this->assertSame($response['form']['name'], $this->testForm['name']);
        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $this->assertSame($lastField['label'], $this->testForm['fields'][0]['label']);
        $this->assertSame($lastAction['name'], $this->testForm['actions'][0]['name']);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('forms');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext('forms');

        // Test Create
        $response = $apiContext->create($this->testForm);
        $this->assertForm($response);

        // Test Get
        $response = $apiContext->get($response['form']['id']);
        $this->assertForm($response);

        // Test Delete
        $response = $apiContext->delete($response['form']['id']);
        $this->assertForm($response);
    }

    public function testDeleteFields()
    {
        $fieldIds   = array();
        $apiContext = $this->getContext('forms');
        $response   = $apiContext->create($this->testForm);

        $this->assertErrors($response);

        foreach ($response['form']['fields'] as $field) {
            $fieldIds[] = $field['id'];
        }

        $response = $apiContext->deleteFields($response['form']['id'], $fieldIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response['form']['fields']), 'Fields were not deleted');

        //now delete the form
        $response = $apiContext->delete($response['form']['id']);
        $this->assertErrors($response);
    }

    public function testDeleteActions()
    {
        $actionIds  = array();
        $apiContext = $this->getContext('forms');
        $response   = $apiContext->create($this->testForm);

        $this->assertErrors($response);

        foreach ($response['form']['actions'] as $action) {
            $actionIds[] = $action['id'];
        }

        $response = $apiContext->deleteActions($response['form']['id'], $actionIds);

        $this->assertErrors($response);
        $this->assertTrue(empty($response['form']['actions']), 'Actions were not deleted');

        //now delete the form
        $response = $apiContext->delete($response['form']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext('forms');
        $response   = $apiContext->edit(10000, $this->testForm);

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testForm);

        $this->assertErrors($response);

        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $lastField['label'] = 'edited field';
        $lastAction['name'] = 'edited action';

        $response = $apiContext->edit(
            $response['form']['id'],
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
        $this->assertTrue(!empty($response['form']['fields']), 'The form field array is empty.');
        $this->assertTrue(!empty($response['form']['actions']), 'The form action array is empty.');
        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $this->assertSame($lastField['label'], 'edited field');
        $this->assertSame($lastAction['name'], 'edited action');

        //now delete the form
        $response = $apiContext->delete($response['form']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext('forms');
        $response   = $apiContext->edit(10000, $this->testForm, true);

        $this->assertForm($response);

        //now delete the form
        $response = $apiContext->delete($response['form']['id']);
        $this->assertErrors($response);
    }

    public function testFieldAndActionDeleteViaPut()
    {
        $apiContext = $this->getContext('forms');

        // Firstly create a form with fields
        $response = $apiContext->edit(10000, $this->testForm, true);

        $this->assertErrors($response);

        // Remove fields and actions
        unset($response['form']['fields']);
        unset($response['form']['actions']);

        // Edit the same entitiy without the fields and actions
        $response = $apiContext->edit(
            $response['form']['id'],
            $response['form'],
            true
        );

        $this->assertErrors($response);
        $this->assertTrue(empty($response['form']['fields']), 'Fields were not deleted via PUT request');
        $this->assertTrue(empty($response['form']['actions']), 'Actions were not deleted via PUT request');

        //now delete the form
        $response = $apiContext->delete($response['form']['id']);
        $this->assertErrors($response);
    }
}

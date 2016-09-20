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
    protected $basicForm = array(
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

    public function testGet()
    {
        $apiContext = $this->getContext('forms');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('forms');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $formApi  = $this->getContext('forms');
        $response = $formApi->create($this->basicForm);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        $this->assertFalse(empty($response['form']['fields']), 'The form field was not created.');
        $this->assertFalse(empty($response['form']['actions']), 'The form action was not created.');
        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $this->assertTrue($lastField['label'] === $this->basicForm['fields'][0]['label'], 'The form field name does not match');
        $this->assertTrue($lastAction['name'] === $this->basicForm['actions'][0]['name'], 'The form action name does not match');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testDeleteFields()
    {
        $fieldIds = array();
        $formApi  = $this->getContext('forms');
        $response = $formApi->create($this->basicForm);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        foreach ($response['form']['fields'] as $field) {
            $fieldIds[] = $field['id'];
        }

        $response = $formApi->deleteFields($response['form']['id'], $fieldIds);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);
        $this->assertTrue(empty($response['form']['fields']), 'Fields were not deleted');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testDeleteActions()
    {
        $actionIds = array();
        $formApi  = $this->getContext('forms');
        $response = $formApi->create($this->basicForm);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        foreach ($response['form']['actions'] as $action) {
            $actionIds[] = $action['id'];
        }

        $response = $formApi->deleteActions($response['form']['id'], $actionIds);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);
        $this->assertTrue(empty($response['form']['actions']), 'Actions were not deleted');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $formApi  = $this->getContext('forms');
        $response = $formApi->edit(10000, $this->basicForm);

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $formApi->create($this->basicForm);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $lastField['label'] = 'edited field';
        $lastAction['name'] = 'edited action';

        $response = $formApi->edit(
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

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        $this->assertTrue(!empty($response['form']['fields']), 'The form field array is empty.');
        $this->assertTrue(!empty($response['form']['actions']), 'The form action array is empty.');
        $lastField = array_pop($response['form']['fields']);
        $lastAction = array_pop($response['form']['actions']);
        $this->assertTrue($lastField['label'] === 'edited field', 'The form field name does not match');
        $this->assertTrue($lastAction['name'] === 'edited action', 'The form action name does not match');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $formApi  = $this->getContext('forms');
        $response = $formApi->edit(10000, $this->basicForm, true);

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);
        $this->assertFalse(empty($response['form']['fields']), 'Fields were not added via PUT request');
        $this->assertFalse(empty($response['form']['actions']), 'Actions were not added via PUT request');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testFieldAndActionDeleteViaPut()
    {
        $formApi  = $this->getContext('forms');

        // Firstly create a form with fields
        $response = $formApi->edit(10000, $this->basicForm, true);

        // Remove fields and actions
        unset($response['form']['fields']);
        unset($response['form']['actions']);

        // Edit the same entitiy without the fields and actions
        $response = $formApi->edit(
            $response['form']['id'],
            $response['form'],
            true
        );

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);
        $this->assertTrue(empty($response['form']['fields']), 'Fields were not deleted via PUT request');
        $this->assertTrue(empty($response['form']['actions']), 'Actions were not deleted via PUT request');

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

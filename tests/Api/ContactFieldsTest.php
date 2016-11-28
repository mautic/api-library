<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ContactFieldsTest extends MauticApiTestCase
{
    /**
     * Payload of example form to test the endpoints with
     *
     * @var array
     */
    protected $testPayload = array(
        'label' => 'API test field',
        'type' => 'text',
    );

    protected $context = 'contactFields';

    protected $itemName = 'field';

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
        $this->assertTrue(isset($response['fields']));
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext($this->context);

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

    public function testCreateGetAndDeleteOfLookupField()
    {
        $lookupField = array(
            'label' => 'API test lookup field',
            'type' => 'lookup',
            'properties' => array(
                'list' => array(
                    array('label' => 'Mr', 'value' => 'mr'),
                    array('label' => 'Mrs', 'value' => 'mrs'),
                    array('label' => 'Miss', 'value' => 'miss'),
                )
            )
        );
        $apiContext  = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($lookupField);
        $this->assertPayload($response, $lookupField);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response, $lookupField);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testBooleanField()
    {
        // Create a testing contact
        $contactContext = $this->getContext('contacts');
        $response = $contactContext->create(array('firstname' => 'Boolean Field', 'lastname' => 'API test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        $apiContext = $this->getContext($this->context);
        $possibleValues = array(1 => 1, 0 => 0, 'yes' => 1, 'no' => 0, 'true' => 1, 'false' => 0);
        $boolField = array(
            'label' => 'API test Boolean field',
            'type' => 'boolean',
            'properties' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        );

        // Create the Boolean field
        $response = $apiContext->create($boolField);
        $this->assertErrors($response);
        $field = $response[$this->itemName];

        // Test if the Boolean value gets updated with test values
        foreach ($possibleValues as $value => $boolValue) {
            $response = $contactContext->edit($contact['id'], array($field['alias'] => $value));
            $this->assertErrors($response);
            $this->assertTrue(isset($response['contact']['fields']['all'][$field['alias']]), $field['alias'].' does not exist in the field list');
            $this->assertEquals($response['contact']['fields']['all'][$field['alias']], $boolValue);
        }

        // Clean after youself
        $response = $apiContext->delete($field['id']);
        $this->assertErrors($response);
        $response = $contactContext->delete($contact['id']);
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

        $update = array(
            'label' => 'test2',
        );

        $response = $apiContext->edit($response[$this->itemName]['id'], $update);
        $this->assertPayload($response, $update);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}

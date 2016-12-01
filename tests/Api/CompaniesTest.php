<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CompaniesTest extends MauticApiTestCase
{
    /**
     * Payload of example form to test the endpoints with
     *
     * @var array
     */
    protected $testPayload = array(
        'companyname' => 'test',
        'companyemail' => 'test@company.com',
        'companycity' => 'Raleigh',
    );

    protected $context = 'companies';

    protected $itemName = 'company';

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);

        $this->assertFalse(empty($response[$this->itemName]['id']), 'The '.$this->itemName.' id is empty.');
        $this->assertFalse(empty($response[$this->itemName]['fields']['all']), 'The '.$this->itemName.' fields are missing.');

        foreach ($this->testPayload as $itemProp => $itemVal) {
            $this->assertTrue(isset($response[$this->itemName]['fields']['all'][$itemProp]), 'The ["'.$this->itemName.'" => "'.$itemProp.'"] doesn\'t exist in the response.');
            $this->assertSame($response[$this->itemName]['fields']['all'][$itemProp], $itemVal);
        }
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
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

    public function testEditPatch()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        $update = array(
            'companyname' => 'test2',
        );

        $response = $apiContext->edit($response[$this->itemName]['id'], $update);
        $this->assertErrors($response, $update);

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

    public function testAddAndRemove()
    {
        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response = $contactsContext->create(array('firstname' => 'API segments test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create company
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $company = $response[$this->itemName];

        // Add the contact to the company
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->addContact($company['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Remove the contact from the company
        $response = $apiContext->removeContact($company['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Delete the contact and the segment
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $apiContext->delete($company['id']);
        $this->assertErrors($response);
    }
}

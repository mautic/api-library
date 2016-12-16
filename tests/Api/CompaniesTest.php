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
    public function setUp() {
        $this->api = $this->getContext('companies');
        $this->testPayload = array(
            'companyname' => 'test',
            'companyemail' => 'test@company.com',
            'companycity' => 'Raleigh',
        );
    }

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);

        if (empty($payload)) {
            $payload = $this->testPayload;
        }

        $this->assertFalse(empty($response[$this->api->itemName()]['id']), 'The '.$this->api->itemName().' id is empty.');
        $this->assertFalse(empty($response[$this->api->itemName()]['fields']['all']), 'The '.$this->api->itemName().' fields are missing.');

        foreach ($payload as $itemProp => $itemVal) {
            $this->assertTrue(isset($response[$this->api->itemName()]['fields']['all'][$itemProp]), 'The ["'.$this->api->itemName().'" => "'.$itemProp.'"] doesn\'t exist in the response.');
            $this->assertSame($response[$this->api->itemName()]['fields']['all'][$itemProp], $itemVal);
        }
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testCreateGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = array(
            'companyname' => 'test2',
        );
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testAddAndRemove()
    {
        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response = $contactsContext->create(array('firstname' => 'API segments test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create company
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $company = $response[$this->api->itemName()];

        // Add the contact to the company
        $response   = $this->api->addContact($company['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Test get contact companies API endpoint
        $contactContext = $this->getContext('contacts');
        $response = $contactContext->getContactCompanies($contact['id']);
        $this->assertErrors($response);
        $this->assertEquals($response['total'], 1);
        $this->assertFalse(empty($response['companies']));

        // Remove the contact from the company
        $response = $this->api->removeContact($company['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Delete the contact and the segment
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $this->api->delete($company['id']);
        $this->assertErrors($response);
    }
}

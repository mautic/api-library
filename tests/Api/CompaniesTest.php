<?php
/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CompaniesTest extends AbstractCustomFieldsTest
{
    public function setUp(): void
    {
        $this->api         = $this->getContext('companies');
        $this->testPayload = [
            'companyname'  => 'test',
            'companyemail' => 'test@company.com',
            'companycity'  => 'Raleigh',
        ];
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
        $editTo = [
            'companyname' => 'test2',
        ];
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
        $response        = $contactsContext->create(['firstname' => 'API segments test']);
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
        $response       = $contactContext->getContactCompanies($contact['id']);
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

    public function testBatchEndpoints()
    {
        $batch = [];

        // Company name must be unique
        for ($i = 0; $i < 3; ++$i) {
            $company                = $this->testPayload;
            $company['companyname'] = 'test'.$i;
            $batch[]                = $company;
        }

        $this->standardTestBatchEndpoints($batch);
    }
}

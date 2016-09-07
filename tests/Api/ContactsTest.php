<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ContactsTest extends MauticApiTestCase
{
    public function testGet()
    {
        $contactApi = $this->getContext('contacts');
        $contact    = $contactApi->get(1);

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);
    }

    public function testGetList()
    {
        $contactApi = $this->getContext('contacts');
        $contacts   = $contactApi->getList();

        $message = isset($contacts['error']) ? $contacts['error']['message'] : '';
        $this->assertFalse(isset($contacts['error']), $message);
    }

    public function testGetFieldList()
    {
        $contactApi = $this->getContext('contacts');
        $fields  = $contactApi->getFieldList();

        $message = isset($fields['error']) ? $fields['error']['message'] : '';
        $this->assertFalse(isset($fields['error']), $message);
    }

    public function testGetSegmentsList()
    {
        $contactApi = $this->getContext('contacts');
        $lists   = $contactApi->getSegments();

        $message = isset($lists['error']) ? $lists['error']['message'] : '';
        $this->assertFalse(isset($lists['error']), $message);
    }

    public function testGetNotes()
    {
        $contactApi = $this->getContext('contacts');
        $contacts   = $contactApi->getContactNotes(1);

        $message = isset($contacts['error']) ? $contacts['error']['message'] : '';
        $this->assertFalse(isset($contacts['error']), $message);
    }

    public function testGetContactSegments()
    {
        $contactApi = $this->getContext('contacts');
        $contacts   = $contactApi->getContactSegments(1);

        $message = isset($contacts['error']) ? $contacts['error']['message'] : '';
        $this->assertFalse(isset($contacts['error']), $message);
    }

    public function testGetCampaigns()
    {
        $contactApi = $this->getContext('contacts');
        $contacts   = $contactApi->getContactCampaigns(1);

        $message = isset($contacts['error']) ? $contacts['error']['message'] : '';
        $this->assertFalse(isset($contacts['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $contactApi = $this->getContext('contacts');
        $contact    = $contactApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);

        //now delete the contact
        $result = $contactApi->delete($contact['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $contactApi = $this->getContext('contacts');
        $contact    = $contactApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        //there should be an error as the contact shouldn't exist
        $this->assertTrue(isset($contact['error']), $contact['error']['message']);

        $contact = $contactApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);

        $contact = $contactApi->edit(
            $contact['contact']['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2'
            )
        );

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);

        //now delete the contact
        $result = $contactApi->delete($contact['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPutFormError()
    {
        $contactApi = $this->getContext('contacts');

        $contact = $contactApi->create(
            array(
                'firstname' => 'country',
                'lastname'  => 'test'
            )
        );

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);

        $contact = $contactApi->edit(
            $contact['contact']['id'],
            array(
                'country' => 'not existing country'
            )
        );

        //there should be an error as the country does not exist
        $this->assertTrue(isset($contact['error']), $contact['error']['message']);
    }

    public function testEditPatch()
    {
        $contactApi = $this->getContext('contacts');
        $contact    = $contactApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            ),
            true
        );

        $message = isset($contact['error']) ? $contact['error']['message'] : '';
        $this->assertFalse(isset($contact['error']), $message);

        //now delete the contact
        $result = $contactApi->delete($contact['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

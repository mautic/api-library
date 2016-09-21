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
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getList();
        $this->assertErrors($response);
    }

    public function testGetFieldList()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getFieldList();
        $this->assertErrors($response);
    }

    public function testGetSegmentsList()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getSegments();
        $this->assertErrors($response);
    }

    public function testGetNotes()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getContactNotes(1);
        $this->assertErrors($response);
    }

    public function testGetContactSegments()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getContactSegments(1);
        $this->assertErrors($response);
    }

    public function testGetCampaigns()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->getContactCampaigns(1);
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $this->assertErrors($response);

        //now delete the contact
        $response = $responseApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        //there should be an error as the contact shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $responseApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $this->assertErrors($response);

        $response = $responseApi->edit(
            $response['contact']['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the contact
        $response = $responseApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatchFormError()
    {
        $responseApi = $this->getContext('contacts');

        $response = $responseApi->create(
            array(
                'firstname' => 'country',
                'lastname'  => 'test'
            )
        );

        $this->assertErrors($response);

        $response = $responseApi->edit(
            $response['contact']['id'],
            array(
                'country' => 'not existing country'
            )
        );

        //there should be an error as the country does not exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);
    }

    public function testEditPut()
    {
        $responseApi = $this->getContext('contacts');
        $response    = $responseApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            ),
            true
        );

        $this->assertErrors($response);

        //now delete the contact
        $response = $responseApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }
}

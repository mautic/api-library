<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class LeadsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'firstname' => 'test',
        'lastname'  => 'test'
    );

    public function testGet()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getList();
        $this->assertErrors($response);
    }

    public function testGetFieldList()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getFieldList();
        $this->assertErrors($response);
    }

    public function testGetListList()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getLists();
        $this->assertErrors($response);
    }

    public function testGetNotes()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getLeadNotes(1);
        $this->assertErrors($response);
    }

    public function testGetLists()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getLeadLists(1);
        $this->assertErrors($response);
    }

    public function testGetCampaigns()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->getLeadCampaigns(1);
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the lead
        $response = $leadApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->edit(10000, $this->testPayload);

        //there should be an error as the lead shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $leadApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $leadApi->edit(
            $response['contact']['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the lead
        $response = $leadApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $leadApi  = $this->getContext('leads');
        $response = $leadApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the lead
        $response = $leadApi->delete($response['contact']['id']);
        $this->assertErrors($response);
    }
}

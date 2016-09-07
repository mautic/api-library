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
    public function testGet()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->get(1);

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);
    }

    public function testGetList()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getList();

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetFieldList()
    {
        $leadApi = $this->getContext('leads');
        $fields  = $leadApi->getFieldList();

        $message = isset($fields['error']) ? $fields['error']['message'] : '';
        $this->assertFalse(isset($fields['error']), $message);
    }

    public function testGetListList()
    {
        $leadApi = $this->getContext('leads');
        $lists   = $leadApi->getLists();

        $message = isset($lists['error']) ? $lists['error']['message'] : '';
        $this->assertFalse(isset($lists['error']), $message);
    }

    public function testGetNotes()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getLeadNotes(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetLists()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getLeadLists(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetCampaigns()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getLeadCampaigns(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        //now delete the lead
        $result = $leadApi->delete($lead['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        //there should be an error as the lead shouldn't exist
        $this->assertTrue(isset($lead['error']), $lead['error']['message']);

        $lead = $leadApi->create(
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            )
        );

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        $lead = $leadApi->edit(
            $lead['contact']['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2'
            )
        );

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        //now delete the lead
        $result = $leadApi->delete($lead['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->edit(
            10000,
            array(
                'firstname' => 'test',
                'lastname'  => 'test'
            ),
            true
        );

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        //now delete the lead
        $result = $leadApi->delete($lead['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

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
        $lead = $leadApi->get(1);

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);
    }

    public function testGetList()
    {
        $leadApi = $this->getContext('leads');
        $leads = $leadApi->getList();

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetFieldList()
    {
        $leadApi = $this->getContext('leads');
        $fields = $leadApi->getFieldList();

        $message = isset($fields['error']) ? $fields['error']['message'] : '';
        $this->assertFalse(isset($fields['error']), $message);
    }

    public function testGetListList()
    {
        $leadApi = $this->getContext('leads');
        $lists = $leadApi->getLists();

        $message = isset($lists['error']) ? $lists['error']['message'] : '';
        $this->assertFalse(isset($lists['error']), $message);
    }

    public function testGetNotes()
    {
        $leadApi = $this->getContext('leads');
        $leads = $leadApi->getLeadNotes(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetLists()
    {
        $leadApi = $this->getContext('leads');
        $leads = $leadApi->getLeadLists(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testGetCampaigns()
    {
        $leadApi = $this->getContext('leads');
        $leads = $leadApi->getLeadCampaigns(1);

        $message = isset($leads['error']) ? $leads['error']['message'] : '';
        $this->assertFalse(isset($leads['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $leadApi = $this->getContext('leads');
        $lead = $leadApi->create(array(
            'firstname' => 'test',
            'lastname' => 'test'
        ));

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        // now delete the lead
        $result = $leadApi->delete($lead['contact']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $leadApi = $this->getContext('leads');
        $lead = $leadApi->edit(10000, array(
            'firstname' => 'test',
            'lastname' => 'test'
        ));

        // there should be an error as the lead shouldn't exist
        $this->assertTrue(isset($lead['error']), $lead['error']['message']);

        $lead = $leadApi->create(array(
            'firstname' => 'test',
            'lastname' => 'test'
        ));

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        $lead = $leadApi->edit($lead['contact']['id'], array(
            'firstname' => 'test2',
            'lastname' => 'test2'
        ));

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);
    }

    public function testEditPatch()
    {
        $leadApi = $this->getContext('leads');
        $lead = $leadApi->edit(10000, array(
            'firstname' => 'test',
            'lastname' => 'test'
        ), true);

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);
    }

    public function testSetPoints()
    {
        $newPointsValue = 28;
        $leadApi = $this->getContext('leads');

        $leadBefore = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];
        if ($pointsBefore === $newPointsValue) {
            $newPointsValue = $newPointsValue - 1;
        }

        $result = $leadApi->setPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $newPointsValue);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while setting Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $this->assertEquals($newPointsValue, $lead['contact']['points'], 'Points not modified correctly');

        // rollback point modification for lead
        $leadRollback = $leadApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }

    public function testAddPoints()
    {
        $pointToAdd = 5;
        $leadApi = $this->getContext('leads');

        $leadBefore = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];

        $result = $leadApi->addPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointToAdd);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while adding Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $this->assertEquals($pointsBefore + $pointToAdd, $lead['contact']['points'], 'Points not added correctly');

        $message = isset($lead['error']) ? $lead['contact']['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        // rollback point modification for lead
        $leadRollback = $leadApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }

    public function testSubtractPoints()
    {
        $pointToRemove = 3;
        $leadApi = $this->getContext('leads');

        $leadBefore = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];

        $leadApi = $this->getContext('leads');
        $result = $leadApi->subtractPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointToRemove);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while removing Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $leadApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $this->assertEquals($pointsBefore - $pointToRemove, $lead['contact']['points'], 'Points not subed correctly');

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        // rollback point modification for lead
        $leadRollback = $leadApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }
}

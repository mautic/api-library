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

    public function testSetPoints()
    {
        $newPointsValue = 28;
        $contactApi = $this->getContext('contacts');

        $leadBefore = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];
        if ($pointsBefore === $newPointsValue) {
            $newPointsValue = $newPointsValue-1;
        }

        $result = $contactApi->setPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $newPointsValue);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while setting Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $this->assertEquals($newPointsValue, $lead['contact']['points'], 'Points not modified correctly');

        // rollback point modification for lead
        $leadRollback = $contactApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }

    public function testAddPoints()
    {
        $pointToAdd = 5;
        $contactApi = $this->getContext('contacts');

        $leadBefore = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];

        $result = $contactApi->addPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointToAdd);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while adding Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $this->assertEquals($pointsBefore + $pointToAdd, $lead['contact']['points'], 'Points not successfuly added');

        $message = isset($lead['error']) ? $lead['contact']['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        // rollback point modification for lead
        $leadRollback = $contactApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }

    public function testSubtractPoints()
    {
        $pointToRemove = 3;
        $contactApi = $this->getContext('contacts');

        $leadBefore = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $pointsBefore = $leadBefore['contact']['points'];

        $result = $contactApi->subtractPoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointToRemove);
        $resultMessage = (isset($result['success']) && !$result['success'] && isset($result['message']))?$result['message']:'';
        $this->assertEquals((isset($result['success']) && $result['success']), TRUE, 'Error while removing Points to lead ID : ' . UnitTestConstant::LEAD_ID_TO_MODIFY . '. Message : ' . $resultMessage);

        $lead = $contactApi->get(UnitTestConstant::LEAD_ID_TO_MODIFY);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertEquals($pointsBefore - $pointToRemove, $lead['contact']['points'], 'Points not subed correctly');

        $message = isset($lead['error']) ? $lead['error']['message'] : '';
        $this->assertFalse(isset($lead['error']), $message);

        // rollback point modification for lead
        $leadRollback = $contactApi->setpoints(UnitTestConstant::LEAD_ID_TO_MODIFY, $pointsBefore);
        $message = isset($leadRollback['error']) ? $leadRollback['error']['message'] : '';
        $this->assertFalse(isset($leadRollback['error']), $message);
    }
}

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

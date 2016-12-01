<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

use Mautic\Api\Contacts;

class ContactsTest extends MauticApiTestCase
{
    protected $context = 'contacts';

    protected $itemName = 'contact';

    public function setUp()
    {
        $this->testPayload = array(
            'firstname' => 'test',
            'lastname'  => 'test',
            'points'    => 3
        );
    }

    protected function assertEventResponse($response, $expectedEvents = array())
    {
        $this->assertErrors($response);
        $this->assertTrue(isset($response['events']));
        $this->assertTrue(isset($response['total']));
        $this->assertTrue(isset($response['types']));
        $this->assertTrue(isset($response['order']));
        $this->assertTrue(isset($response['filters']));
        $this->assertEquals(count($response['events']), count($expectedEvents));

        foreach ($expectedEvents as $key => $event) {
            $this->assertEquals($response['events'][$key]['event'], $event);
        }
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetFieldList()
    {
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->getFieldList();
        $this->assertErrors($response);
        $this->assertGreaterThan(0, count($response));
    }

    public function testGetSegmentsList()
    {
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->getSegments();
        $this->assertErrors($response);
    }

    public function testGetEvents()
    {
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->itemName];

        $response = $apiContext->getEvents($contact['id']);
        $this->assertEventResponse($response, array('lead.create', 'lead.identified'));

        $response = $apiContext->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testGetEventsAdvanced()
    {
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->itemName];

        $response = $apiContext->getEvents($contact['id'], '', array('lead.identified'));
        $this->assertEventResponse($response, array('lead.identified'));

        $response = $apiContext->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertErrors($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testDncAddInCreate()
    {
        $apiContext = $this->getContext($this->context);

        // Add DNC to the payload
        $this->testPayload['doNotContact'] = array(
            array(
                'channel' => 'email',
                'reason' => Contacts::BOUNCED,
            )
        );

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $this->assertEquals(count($response[$this->itemName]['doNotContact']), 1);

        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testDncAddRemoveEndpoints()
    {
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        // Test Add
        $response = $apiContext->addDnc($response[$this->itemName]['id'], 'email', Contacts::BOUNCED);
        $this->assertErrors($response);
        $this->assertEquals(count($response[$this->itemName]['doNotContact']), 1);

        // Test Remove
        $response = $apiContext->removeDnc($response[$this->itemName]['id'], $response[$this->itemName]['doNotContact'][0]['channel']);
        $this->assertErrors($response);

        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $pointsSet   = 5;
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the contact shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2',
                'points'    => $pointsSet,
            )
        );

        $this->assertErrors($response);
        $this->assertSame($response[$this->itemName]['points'], $pointsSet, 'Points were not set correctly');

        //now delete the contact
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatchFormError()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'country' => 'not existing country'
            )
        );

        //there should be an error as the country does not exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->edit(10000, $this->testPayload, true);

        $this->assertErrors($response);

        //now delete the contact
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testAddPoints()
    {
        $pointToAdd = 5;
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->itemName];

        $response = $apiContext->addPoints($contact['id'], $pointToAdd);
        $this->assertErrors($response);
        $this->assertTrue(!empty($response['success']), 'Adding point to a contact with ID ='.$contact['id'].' was not successful');

        $response = $apiContext->get($contact['id']);
        $this->assertErrors($response);
        $this->assertSame($response[$this->itemName]['points'], ($contact['points'] + $pointToAdd), 'Points were not added correctly');

        $response = $apiContext->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testSubtractPoints()
    {
        $pointToSub = 5;
        $apiContext = $this->getContext($this->context);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->itemName];

        $response = $apiContext->subtractPoints($contact['id'], $pointToSub);
        $this->assertErrors($response);
        $this->assertTrue(!empty($response['success']), 'Subtracting point to a contact with ID ='.$contact['id'].' was not successful');

        $response = $apiContext->get($contact['id']);
        $this->assertErrors($response);
        $this->assertSame($response[$this->itemName]['points'], ($contact['points'] - $pointToSub), 'Points were not subtracted correctly');

        $response = $apiContext->delete($contact['id']);
        $this->assertErrors($response);
    }
}

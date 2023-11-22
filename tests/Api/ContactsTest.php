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

use Mautic\Api\Contacts;
use Mautic\QueryBuilder\QueryBuilder;

class ContactsTest extends AbstractCustomFieldsTest
{
    protected $skipPayloadAssertion = ['firstname', 'lastname', 'tags', 'owner', 'stage', 'points'];

    /**
     * @var Contacts
     */
    protected $api;

    public function setUp(): void
    {
        $this->api         = $this->getContext('contacts');
        $this->testPayload = [
            'firstname' => 'John',
            'lastname'  => 'APIDoe',
            'address2'  => 'Sam & Sons',
            'email'     => 'test@mautic.api',
            'owner'     => 1,
            'points'    => 3,
            'tags'      => [
                'APItag1',
                'APItag2',
            ],
        ];
    }

    protected function assertEventResponse($response, $expectedEvents = null)
    {
        $this->assertErrors($response);
        $this->assertTrue(isset($response['events']));
        $this->assertTrue(isset($response['total']));
        $this->assertTrue(isset($response['types']));
        $this->assertTrue(isset($response['order']));
        $this->assertTrue(isset($response['filters']));

        if ($expectedEvents) {
            foreach ($expectedEvents as $key => $eventName) {
                $actual = 'oops Missing';
                foreach ($response['events'] as $event) {
                    if ($eventName == $event['event']) {
                        $actual = $event['event'];
                        break;
                    }
                }
                $this->assertEquals($eventName, $actual);
            }
        }
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    /**
     * We cannot use standard method since contacts with the same email are merged into one.
     */
    public function testGetListOfSpecificIds()
    {
        // Create some items first
        $itemIds = [];
        for ($i = 0; $i <= 2; ++$i) {
            $testPayload          = $this->testPayload;
            $testPayload['email'] = $i.$this->testPayload['email'];
            $response             = $this->api->create($testPayload);
            $this->assertErrors($response);
            $itemIds[] = $response[$this->api->itemName()]['id'];
        }

        $search   = 'ids:'.implode(',', $itemIds);
        $response = $this->api->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response[$this->api->listName()] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $this->api->delete($item['id']);
            $this->assertErrors($response);
        }
    }

    public function testGetListOfSpecificSegment()
    {
        $segmentApi = $this->getContext('segments');

        // Create Segment
        $segmentPayload = [
            'name' => 'Contact Segment Search API test',
        ];
        $response = $segmentApi->create($segmentPayload);
        $this->assertErrors($response);
        $segmentId    = $response['list']['id'];
        $segmentAlias = $response['list']['alias'];

        $itemIds = [];
        for ($i = 0; $i <= 2; ++$i) {
            $testPayload          = $this->testPayload;
            $testPayload['email'] = $i.$this->testPayload['email'];

            // Create some items
            $response = $this->api->create($testPayload);
            $this->assertErrors($response);
            $itemIds[] = $response[$this->api->itemName()]['id'];

            // Add contacts to the segment
            $response = $segmentApi->addContact($segmentId, $response[$this->api->itemName()]['id']);
            $this->assertErrors($response);
        }

        $search   = 'segment:'.$segmentAlias;
        $response = $this->api->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response[$this->api->listName()] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $this->api->delete($item['id']);
            $this->assertErrors($response);
        }

        $segmentApi->delete($segmentId);
        $this->assertErrors($response);
    }

    public function testGetFieldList()
    {
        $response    = $this->api->getFieldList();
        $this->assertErrors($response);
        $this->assertGreaterThan(0, count($response));
    }

    public function testGetSegmentsList()
    {
        $response    = $this->api->getSegments();
        $this->assertErrors($response);
    }

    public function testGetActivityForContact()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->api->itemName()];

        // Add some activity
        $this->api->addDnc($contact['id'], 'email', Contacts::BOUNCED);
        $this->api->addPoints($contact['id'], 3);

        $response = $this->api->getActivityForContact($contact['id']);
        $this->assertEventResponse($response, ['lead.donotcontact', 'point.gained']);

        $response = $this->api->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testGetActivityForContactAdvanced()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->api->itemName()];

        // Add some activity
        $this->api->addDnc($contact['id'], 'email', Contacts::BOUNCED);
        $this->api->addPoints($contact['id'], 3);

        $response = $this->api->getActivityForContact($contact['id'], '', ['lead.donotcontact']);
        $this->assertEventResponse($response, ['lead.donotcontact']);

        $response = $this->api->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testGetActivity()
    {
        $response = $this->api->getActivity();
        $this->assertEventResponse($response, null);
    }

    public function testGetActivityAdvanced()
    {
        // Ensure a page hit exists
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->config['baseUrl'].'/mtracking.gif?url='.urlencode('http://mautic.org'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_exec($curl);

        $response = $this->api->getActivity('', ['page.hit']);
        $this->assertEventResponse($response, ['page.hit']);
    }

    public function testGetActivityWithDateRange()
    {
        $dateFrom = new \DateTime('-1 week');
        $dateTo   = new \DateTime('-1 day');
        $response = $this->api->getActivity('', [], [], '', 'ASC', 1, $dateFrom, $dateTo);

        $this->assertEventResponse($response);

        foreach ($response['events'] as $event) {
            $timestamp = new \DateTime($event['timestamp']);
            $this->assertGreaterThanOrEqual($dateFrom, $timestamp);
            $this->assertLessThanOrEqual($dateTo, $timestamp);
        }
    }

    public function testCreateGetAndDelete()
    {
        // Create a stage to test with it too
        $stageApi                   = $this->getContext('stages');
        $response                   = $stageApi->create(['name' => 'contact API test']);
        $stage                      = $response[$stageApi->itemName()];
        $this->testPayload['stage'] = $stage['id'];

        // Test Create
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $contact = $response[$this->api->itemName()];

        $this->assertPayload($response);
        $this->assertEquals(count($this->testPayload['tags']), count($contact['tags']));
        $this->assertEquals($this->testPayload['firstname'], $contact['fields']['core']['firstname']['value']);
        $this->assertEquals($this->testPayload['lastname'], $contact['fields']['core']['lastname']['value']);
        $this->assertEquals($this->testPayload['address2'], $contact['fields']['core']['address2']['value']);
        $this->assertEquals($this->testPayload['firstname'], $contact['fields']['all']['firstname']);
        $this->assertEquals($this->testPayload['lastname'], $contact['fields']['all']['lastname']);
        $this->assertEquals($this->testPayload['address2'], $contact['fields']['all']['address2']);
        $this->assertEquals($this->testPayload['points'], $contact['points']);
        $this->assertEquals($this->testPayload['owner'], $contact['owner']['id']);
        $this->assertEquals($this->testPayload['stage'], $contact['stage']['id']);

        // Test Get
        $response = $this->api->get($contact['id']);
        $this->assertPayload($response);

        // Test Delete
        // $response = $this->api->delete($response[$this->api->itemName()]['id']);
        // $this->assertErrors($response);
        $stageApi->delete($stage['id']);
    }
    
    public function testMergingDuplicateContacts()
    {
        // Check if there is some contact with the email
        $response = $this->api->getList('email:'.$this->testPayload['email']);
        $this->assertErrors($response);
        $duplicates = $response[$this->api->listName()];

        // Create contact A
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $contactA = $response[$this->api->itemName()];

        // If there are some duplicates, the contactA should update one of them instead of creating a new contact
        if ($duplicates) {
            $this->assertTrue(isset($duplicates[$contactA['id']]));
        }

        // Create contact B
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $contactB = $response[$this->api->itemName()];

        // Since contactA has the same email as AssetB, their ID should be the same.
        $this->assertSame($contactA['id'], $contactB['id']);

        // Clean after this test - we have to delete only one contact, because contactA === contactB
        $response = $this->api->delete($contactA['id']);
        $this->assertErrors($response);
    }

    public function testDncAddInCreate()
    {
        // Add DNC to the payload
        $this->testPayload['doNotContact'] = [
            [
                'channel' => 'email',
                'reason'  => Contacts::BOUNCED,
            ],
        ];

        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $this->assertEquals(count($response[$this->api->itemName()]['doNotContact']), 1);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testDncAddRemoveEndpoints()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);

        // Test Add
        $response = $this->api->addDnc($response[$this->api->itemName()]['id'], 'email', Contacts::BOUNCED);
        $this->assertErrors($response);
        $this->assertEquals(count($response[$this->api->itemName()]['doNotContact']), 1);

        // Test Remove
        $response = $this->api->removeDnc($response[$this->api->itemName()]['id'], $response[$this->api->itemName()]['doNotContact'][0]['channel']);
        $this->assertErrors($response);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $pointsSet   = 5;
        $response    = $this->api->edit(10000, $this->testPayload);

        //there should be an error as the contact shouldn't exist
        $this->assertTrue(isset($response['errors'][0]), $response['errors'][0]['message']);

        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);

        $response = $this->api->edit(
            $response[$this->api->itemName()]['id'],
            [
                'firstname' => 'test2',
                'lastname'  => 'test2',
                'points'    => $pointsSet,
            ]
        );

        $this->assertErrors($response);
        $this->assertSame($response[$this->api->itemName()]['points'], $pointsSet, 'Points were not set correctly');

        //now delete the contact
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatchFormError()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);

        $response = $this->api->edit(
            $response[$this->api->itemName()]['id'],
            [
                'country' => 'not existing country',
            ]
        );

        //there should be an error as the country does not exist
        $this->assertTrue(isset($response['errors'][0]), $response['errors'][0]['message']);
    }

    public function testEditPut()
    {
        $qb = new QueryBuilder();
        $qb->addWhere($qb->getWhereBuilder()->eq('email', $this->testPayload['email']));
        $response = $this->api->getCustomList($qb, 0, 1);
        $this->assertErrors($response);

        // Making sure that if the contact exists, it won't try to create it. Otherwise we'll get an error about duplicated email.
        if (isset($response[$this->api->listName()]) && count($response[$this->api->listName()])) {
            $response = $this->api->delete(array_pop($response[$this->api->listName()])['id']);
            $this->assertErrors($response);
        }

        $this->standardTestEditPut();
    }

    public function testAddPoints()
    {
        $pointToAdd = 5;

        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->api->itemName()];

        $response = $this->api->addPoints($contact['id'], $pointToAdd);
        $this->assertErrors($response);
        $this->assertTrue(!empty($response['success']), 'Adding point to a contact with ID ='.$contact['id'].' was not successful');

        $response = $this->api->get($contact['id']);
        $this->assertErrors($response);
        $this->assertSame($response[$this->api->itemName()]['points'], ($contact['points'] + $pointToAdd), 'Points were not added correctly');

        $response = $this->api->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testSubtractPoints()
    {
        $pointToSub = 5;

        $response = $this->api->create($this->testPayload);
        $this->assertErrors($response);
        $contact = $response[$this->api->itemName()];

        $response = $this->api->subtractPoints($contact['id'], $pointToSub);
        $this->assertErrors($response);
        $this->assertTrue(!empty($response['success']), 'Subtracting point to a contact with ID ='.$contact['id'].' was not successful');

        $response = $this->api->get($contact['id']);
        $this->assertErrors($response);
        $this->assertSame($response[$this->api->itemName()]['points'], ($contact['points'] - $pointToSub), 'Points were not subtracted correctly');

        $response = $this->api->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testBatchEndpoints()
    {
        $contact1          = $this->testPayload;
        $contact2          = $this->testPayload;
        $contact3          = $this->testPayload;
        $contact1['email'] = 'batch1@test.email';
        $contact2['email'] = 'batch2@test.email';
        $contact3['email'] = 'batch3@test.email';
        $batch             = [
            $contact1,
            $contact2,
            $contact3,
        ];

        $this->standardTestBatchEndpoints($batch);
    }
}

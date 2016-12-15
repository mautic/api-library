<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CampaignsTest extends MauticApiTestCase
{
    protected $testPayloadEdit = array(
        'name' => 'test',
        'description' => 'Created via API',
    );

    protected $testPayload = array();

    protected $context = 'campaigns';

    protected $itemName = 'campaign';

    protected $requiredItems = array(
        'segments' => array(
            'item' => 'list',
            'payload' => array(
                'name' => 'Company API test'
            )
        ),
        'dynamicContents' => array(
            'item' => 'dynamicContent',
            'payload' => array(
                'name' => 'Company API test',
                'content' => 'Company API test'
            )
        ),
        'emails' => array(
            'item' => 'email',
            'payload' => array(
                'name' => 'Company API test',
                'body' => 'Company API test'
            )
        ),
        'companies' => array(
            'item' => 'company',
            'payload' => array(
                'companyname' => 'Company API test'
            )
        )
    );

    protected $skipPayloadAssertion = array('events', 'forms', 'lists', 'canvasSettings', 'dateModified', 'dateAdded');

    public function setUp() {
        $this->testPayload = array(
            'name' => 'test',
            'description' => 'Created via API',
            'events' => array(
                array(
                    'id' => 'new_43', // Event ID will be replaced on /new
                    'name' => 'DWC event test',
                    'description' => 'API test',
                    'type' => 'dwc.decision',
                    'eventType' => 'decision',
                    'order' => 1,
                    'properties' => array(
                        'dwc_slot_name' => 'dwc',
                        'dynamicContent' => 1, // Create DWC first
                    ),
                    'triggerDate' => null,
                    'triggerInterval' => 0,
                    'triggerIntervalUnit' => null,
                    'triggerMode' => null,
                    'children' => array(
                        'new_44', // Event ID will be replaced on /new
                        'new_55', // Event ID will be replaced on /new
                    ),
                    'parent' => null,
                    'decisionPath' => null,
                ),
                array(
                    'id' => 'new_44', // Event ID will be replaced on /new
                    'name' => 'Send email',
                    'description' => 'API test',
                    'type' => 'email.send',
                    'eventType' => 'action',
                    'order' => 2,
                    'properties' => array(
                        'email' => 1, // Create email first
                        'email_type' => 'transactional',
                    ),
                    'triggerDate' => null,
                    'triggerInterval' => 1,
                    'triggerIntervalUnit' => 'd',
                    'triggerMode' => 'interval',
                    'children' => array(),
                    'parent' => 'new_43', // Event ID will be replaced on /new
                    'decisionPath' => 'yes',
                ),
                array(
                    'id' => 'new_55', // Event ID will be replaced on /new
                    'name' => 'Add to company action',
                    'description' => 'API test',
                    'type' => 'lead.addtocompany',
                    'eventType' => 'action',
                    'order' => 2,
                    'properties' => array(
                        'company' => '10', // Create company first
                    ),
                    'triggerDate' => null,
                    'triggerInterval' => 1,
                    'triggerIntervalUnit' => 'd',
                    'triggerMode' => 'interval',
                    'children' => array(),
                    'parent' => 'new_43', // Event ID will be replaced on /new
                    'decisionPath' => 'no',
                )
            ),
            'forms' => array(),
            'lists' => array(
                array(
                    'id' => 1 // Create the list first
                )
            ),
            'canvasSettings' => array(
                'nodes' => array(
                    array(
                        'id' => 'new_43', // Event ID will be replaced on /new
                        'positionX' => '650',
                        'positionY' => '189',
                    ),
                    array(
                        'id' => 'new_44', // Event ID will be replaced on /new
                        'positionX' => '433',
                        'positionY' => '348',
                    ),
                    array(
                        'id' => 'new_55', // Event ID will be replaced on /new
                        'positionX' => '750',
                        'positionY' => '411',
                    ),
                    array(
                        'id' => 'lists',
                        'positionX' => '629',
                        'positionY' => '65',
                    ),
                ),
                'connections' => array(
                    array(
                        'sourceId' => 'lists',
                        'targetId' => 'new_43', // Event ID will be replaced on /new
                        'anchors' => array(
                            'source' => 'leadsource',
                            'target' => 'top',
                        )
                    ),
                    array(
                        'sourceId' => 'new_43', /// Event ID will be replaced on /new
                        'targetId' => 'new_44', // Event ID will be replaced on /new
                        'anchors' => array(
                            'source' => 'yes',
                            'target' => 'top',
                        ),
                    ),
                    array(
                        'sourceId' => 'new_43', // Event ID will be replaced on /new
                        'targetId' => 'new_55', // Event ID will be replaced on /new
                        'anchors' => array(
                            'source' => 'no',
                            'target' => 'top',
                        ),
                    )
                )
            )
        );
    }

    public function setUpPayloadClass() {
        // Create items used in the test campaign payload.
        foreach ($this->requiredItems as $context => &$data) {
            $response = $this->getContext($context)->create($data['payload']);
            $this->assertErrors($response);
            $data['payload'] = $response[$data['item']];
        }

        $this->testPayload['events'][0]['properties']['dynamicContent'] = $this->requiredItems['dynamicContents']['payload']['id'];
        $this->testPayload['events'][1]['properties']['email'] = $this->requiredItems['emails']['payload']['id'];
        $this->testPayload['events'][2]['properties']['company'] = $this->requiredItems['companies']['payload']['id'];
        $this->testPayload['lists'] = array(array('id' => $this->requiredItems['segments']['payload']['id']));
    }

    public function clearPayloadItems() {
        // Delete items used in the test campaign payload.
        foreach ($this->requiredItems as $context => &$data) {
            $response = $this->getContext($context)->delete($data['payload']['id']);
            $this->assertErrors($response);
        }
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $this->setUpPayloadClass();
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
        $this->clearPayloadItems();
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext($this->context);
        $response    = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the campaign shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $this->setUpPayloadClass();
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Modify the crated campaign
        $campaign = $response[$this->itemName];
        $campaign['name'] = 'test2';

        foreach ($campaign['events'] as &$event) {
            $event['name'] = 'Event Name Modified';
        }

        $response = $apiContext->edit($campaign['id'], $campaign);
        $this->assertPayload($response, $campaign);

        foreach ($response[$this->itemName]['events'] as $event) {
            $this->assertEquals($event['name'], 'Event Name Modified');
        }

        //now delete the campaign
        $response = $apiContext->delete($campaign['id']);
        $this->assertErrors($response);
        $this->clearPayloadItems();
    }

    public function testEditPut()
    {
        $this->setUpPayloadClass();
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->edit(1000000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the campaign
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
        $this->clearPayloadItems();
    }

    public function testEventAndSourceDeleteViaPut()
    {
        $apiContext = $this->getContext($this->context);
        $this->setUpPayloadClass();
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Remove the last event
        array_pop($response[$this->itemName]['events']);

        // Create a new list
        $segmentResponse = $this->getContext('segments')->create(
            array(
                'name' => 'second campaign test segment'
            )
        );
        $this->assertErrors($segmentResponse);

        // Substitude another segment
        $newSegmentsArray = array($segmentResponse['list']);
        $response[$this->itemName]['lists'] = $newSegmentsArray;

        // Edit the same entitiy without the fields and actions
        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            $response[$this->itemName],
            true
        );
        $this->assertPayload($response);
        $this->assertEquals(count($response[$this->itemName]['events']), 2);
        $this->assertEquals(count($response[$this->itemName]['lists']), 1);
        $this->assertEquals($response[$this->itemName]['lists'][0]['id'], $newSegmentsArray[0]['id']);
        $this->assertEquals($response[$this->itemName]['lists'][0]['name'], $newSegmentsArray[0]['name']);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
        $this->clearPayloadItems();
    }

    public function testAddAndRemove()
    {
        $this->setUpPayloadClass();

        // Create contact
        $contactsContext = $this->getContext('contacts');
        $response = $contactsContext->create(array('firstname' => 'API campagin test'));
        $this->assertErrors($response);
        $contact = $response['contact'];

        // Create campaign
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $campaign = $response[$this->itemName];

        // Add the contact to the campaign
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->addContact($campaign['id'], $contact['id']);
        $this->assertErrors($response);

        // Test get contact campaigns API endpoint
        $contactContext = $this->getContext('contacts');
        $response = $contactContext->getContactCampaigns($contact['id']);
        $this->assertErrors($response);
        $this->assertEquals($response['total'], 1);
        $this->assertFalse(empty($response['campaigns']));

        // Remove the contact from the campaign
        $response = $apiContext->removeContact($campaign['id'], $contact['id']);
        $this->assertErrors($response);

        // Delete the contact and the campaign
        $response = $contactsContext->delete($contact['id']);
        $this->assertErrors($response);
        $response = $apiContext->delete($campaign['id']);
        $this->assertErrors($response);
        $this->clearPayloadItems();
    }
}

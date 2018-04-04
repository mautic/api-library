<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class SmsesTest extends MauticApiTestCase
{
    protected $endpoint = 'smses';

    private $contactPayload = [
        'firstname' => 'John',
            'lastname'  => 'APIDoe',
            'address2'  => 'Sam & Sons',
            'email'     => 'test@mautic.api',
            'points'    => 3,
            'phone'      => '1234567890'
    ];

    public function setUp() {
        $this->api = $this->getContext('smses');
        $this->testPayload = array(
            'name' => 'test',
            'message' => 'API test message'
        );
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = array(
            'name' => 'test2',
        );
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }

    private function createEntity($type, $entity) {
        $context = $this->getContext($type);
        $response = $context->create($entity);
        $itemName = $context->getItemName();

        $this->assertTrue(isset($response[$itemName]), isset($response['error']['message']) ? $response['error']['message'] : '');

        return isset($response[$itemName]) ? $response[$itemName] : false;
    }

    private function removeEntity($type, $entity) {
        $context = $this->getContext($type);
        $response = $context->delete($entity['id']);
        $itemName = $context->getItemName();

        $this->assertTrue(isset($response[$itemName]), isset($response['error']['message']) ? $response['error']['message'] : '');

        return isset($response[$itemName]) ? true : false;
    }

    public function testSendSMS() {
        $contact = $this->createEntity('contacts', $this->contactPayload);
        $message = $this->createEntity('smses', [
            'created_by'  => $contact['id'],
            'create_by_user' => "API Test",
            'name'      => 'API Test Message',
            'message'   => 'You should not get this',
        ]);

        if ($contact && $message) {
            $requestResponse =  $this->api->makeRequest($this->endpoint.'/' . $message['id'] . '/contact/' . $contact['id'] . '/send');
            $this->assertCount(1, $requestResponse['errors']);
            $response = $this->api->sendSMS($message['id'], $contact['id']);

            $this->assertTrue(!$response['result']['sent']);
            $this->assertEquals($response['result']['name'], $message['name']);

            $this->assertEquals($response, $requestResponse);

            $this->assertTrue($this->removeEntity('smses', $message));
            $this->assertTrue($this->removeEntity('contacts', $contact));
        } else {
            $this->assertTrue(false, "Failed to perform test as dependencies failed");
        }
    }
}

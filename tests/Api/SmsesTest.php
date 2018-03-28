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

    public function testSendSMS() {
        $requestResponse =  $this->api->makeRequest($this->endpoint.'/1/contact/1/send');        
        $this->assertCount(1, $requestResponse['errors']);

        $response = $this->api->sendSMS(1, 1);

        $this->assertEquals($response, $requestResponse);
    }
}

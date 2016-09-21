<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class StagesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test'
    );

    public function testGet()
    {
        $stageApi = $this->getContext('stages');
        $response = $stageApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $stageApi = $this->getContext('stages');
        $response = $stageApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $stageApi = $this->getContext('stages');
        $response = $stageApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the stage
        $response = $stageApi->delete($response['stage']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $stageApi = $this->getContext('stages');
        $response = $stageApi->edit(10000, $this->testPayload);

        //there should be an error as the stage shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $stageApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $stageApi->edit($response['stage']['id'], $this->testPayload);
        $this->assertErrors($response);

        //now delete the stage
        $response = $stageApi->delete($response['stage']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $stageApi = $this->getContext('stages');
        $response    = $stageApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the stage
        $response = $stageApi->delete($response['stage']['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        $stageApi = $this->getContext('stages');
        $response   = $stageApi->addContact(1, 1);
        $this->assertErrors($response);

        //now remove the lead from the stage
        $response = $stageApi->removeContact(1, 1);
        $this->assertErrors($response);
    }
}

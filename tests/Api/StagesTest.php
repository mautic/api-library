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

    protected $context = 'stages';

    protected $itemName = 'stage';

    public function testGetList()
    {
        $stageApi = $this->getContext($this->context);
        $response = $stageApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $stageApi = $this->getContext($this->context);
        $response = $stageApi->edit(10000, $this->testPayload);

        //there should be an error as the stage shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $stageApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $stageApi->edit($response[$this->itemName]['id'], $this->testPayload);
        $this->assertErrors($response);

        //now delete the stage
        $response = $stageApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $stageApi = $this->getContext($this->context);
        $response = $stageApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the stage
        $response = $stageApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        $stageApi = $this->getContext($this->context);
        $response = $stageApi->addContact(1, 1);
        $this->assertErrors($response);

        //now remove the lead from the stage
        $response = $stageApi->removeContact(1, 1);
        $this->assertErrors($response);
    }
}

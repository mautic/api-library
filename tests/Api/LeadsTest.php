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
    protected $testPayload = array(
        'firstname' => 'test',
        'lastname'  => 'test'
    );

    protected $context = 'leads';

    protected $itemName = 'contact';

    public function testGetList()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetFieldList()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getFieldList();
        $this->assertErrors($response);
    }

    public function testGetListList()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getLists();
        $this->assertErrors($response);
    }

    public function testGetNotes()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getLeadNotes(1);
        $this->assertErrors($response);
    }

    public function testGetLists()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getLeadLists(1);
        $this->assertErrors($response);
    }

    public function testGetCampaigns()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->getLeadCampaigns(1);
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

    public function testEditPatch()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the lead shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'firstname' => 'test2',
                'lastname'  => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the lead
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext  = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the lead
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}

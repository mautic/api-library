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
    protected $testCampaign = array(
        'name' => 'test'
    );

    public function testGet()
    {
        $apiContext = $this->getContext('campaigns');
        $response   = $apiContext->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('campaigns');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $campaignApi = $this->getContext('campaigns');
        $response    = $campaignApi->create($this->testCampaign);
        $this->assertErrors($response);

        //now delete the campaign
        $response = $campaignApi->delete($response['campaign']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $campaignApi = $this->getContext('campaigns');
        $response    = $campaignApi->edit(10000, $this->testCampaign);

        //there should be an error as the campaign shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $campaignApi->create($this->testCampaign);
        $this->assertErrors($response);

        $response = $campaignApi->edit(
            $response['campaign']['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the campaign
        $response = $campaignApi->delete($response['campaign']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $campaignApi = $this->getContext('campaigns');
        $response    = $campaignApi->edit(10000, $this->testCampaign, true);

        $this->assertErrors($response);

        //now delete the campaign
        $response = $campaignApi->delete($response['campaign']['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        $campaignApi = $this->getContext('campaigns');
        $response    = $campaignApi->addContact(1, 1);

        $this->assertErrors($response);

        //now remove the lead from the campaign
        $response = $campaignApi->removeContact(1, 1);
        $this->assertErrors($response);
    }
}

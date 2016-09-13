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
    public function testGet()
    {
        $apiContext = $this->getContext('campaigns');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('campaigns');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $campaignApi = $this->getContext('campaigns');
        $campaign    = $campaignApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($campaign['error']) ? $campaign['error']['message'] : '';
        $this->assertFalse(isset($campaign['error']), $message);

        //now delete the campaign
        $result = $campaignApi->delete($campaign['campaign']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $campaignApi = $this->getContext('campaigns');
        $campaign    = $campaignApi->edit(
            10000,
            array(
                'name' => 'test'
            )
        );

        //there should be an error as the campaign shouldn't exist
        $this->assertTrue(isset($campaign['error']), $campaign['error']['message']);

        $campaign = $campaignApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($campaign['error']) ? $campaign['error']['message'] : '';
        $this->assertFalse(isset($campaign['error']), $message);

        $campaign = $campaignApi->edit(
            $campaign['campaign']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($campaign['error']) ? $campaign['error']['message'] : '';
        $this->assertFalse(isset($campaign['error']), $message);

        //now delete the campaign
        $result = $campaignApi->delete($campaign['campaign']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $campaignApi = $this->getContext('campaigns');
        $campaign    = $campaignApi->edit(
            10000,
            array(
                'name' => 'test',
                // following cannot be null
                'isPublished' => 1
            ),
            true
        );

        $message = isset($campaign['error']) ? $campaign['error']['message'] : '';
        $this->assertFalse(isset($campaign['error']), $message);

        //now delete the campaign
        $result = $campaignApi->delete($campaign['campaign']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testAddAndRemove()
    {
        $campaignApi = $this->getContext('campaigns');
        $result   = $campaignApi->addContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);

        //now remove the lead from the campaign
        $result = $campaignApi->removeContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}

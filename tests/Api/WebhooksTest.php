<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class WebhooksTest extends MauticApiTestCase
{
    public function setUp() {
        $this->api = $this->getContext('webhooks');
        $this->testPayload = array(
            'name' => 'test',
            'description' => 'Created via API',
            'webhookUrl' => 'http://some.url',
            'events' => array(
                ''
            )
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
            'description' => 'Updated via API',
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

    public function testGetWebhookEvents()
    {
        $response = $this->api->getEvents();

        $this->assertTrue(isset($response['events']));

        $this->assertTrue(isset($response['events']['mautic.lead_post_delete']));
        $this->assertTrue(isset($response['events']['mautic.lead_points_change']));
        $this->assertTrue(isset($response['events']['mautic.lead_post_save_update']));
        $this->assertTrue(isset($response['events']['mautic.email_on_open']));
        $this->assertTrue(isset($response['events']['mautic.form_on_submit']));
        $this->assertTrue(isset($response['events']['mautic.lead_post_save_new']));
        $this->assertTrue(isset($response['events']['mautic.page_on_hit']));

        $this->assertTrue(isset($response['events']['mautic.page_on_hit']['label']));
        $this->assertTrue(isset($response['events']['mautic.page_on_hit']['description']));

    }
}

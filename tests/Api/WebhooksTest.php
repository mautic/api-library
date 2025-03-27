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

class WebhooksTest extends MauticApiTestCase
{
    protected $skipPayloadAssertion = ['dateModified', 'modifiedBy', 'modifiedByUser'];

    public function setUp(): void
    {
        $this->api         = $this->getContext('webhooks');
        $this->testPayload = [
            'name'             => 'test',
            'description'      => 'Created via API',
            'webhookUrl'       => 'http://some.url',
            'eventsOrderbyDir' => 'DESC',
            'triggers'         => [
                'mautic.lead_post_save_update',
                'mautic.lead_post_save_new',
            ],
        ];
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

    public function testCreateWithWrongOrderDir()
    {
        $this->testPayload['eventsOrderbyDir'] = 'abrakadabra';
        $response                              = $this->api->create($this->testPayload);

        $this->assertFalse(empty($response['errors']));
    }

    public function testCreateWithUndefinedOrderDir()
    {
        unset($this->testPayload['eventsOrderbyDir']);

        $this->standardTestCreateGetAndDelete($this->testPayload);
    }

    public function testEditPatch()
    {
        $editTo = [
            'name'        => 'test2',
            'description' => 'Updated via API',
        ];
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

    public function testAddingTriggerToWebhook()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $editTo = $response[$this->api->itemName()];

        $editTo['triggers'][] = 'mautic.email_on_open';

        $response = $this->api->edit($response[$this->api->itemName()]['id'], $editTo);
        $this->assertPayload($response, $editTo);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testRemovingTriggerFromWebhook()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $editTo = $response[$this->api->itemName()];

        $editTo['triggers'] = ['mautic.email_on_open'];

        $response = $this->api->edit($response[$this->api->itemName()]['id'], $editTo, true);
        $this->assertPayload($response, $editTo);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testGetWebhookTriggers()
    {
        $response = $this->api->getTriggers();

        $this->assertTrue(isset($response['triggers']));

        $this->assertTrue(isset($response['triggers']['mautic.lead_post_save_new']));
        $this->assertTrue(isset($response['triggers']['mautic.lead_post_save_update']));
        $this->assertTrue(isset($response['triggers']['mautic.lead_post_delete']));
        $this->assertTrue(isset($response['triggers']['mautic.lead_points_change']));
        $this->assertTrue(isset($response['triggers']['mautic.email_on_open']));
        $this->assertTrue(isset($response['triggers']['mautic.form_on_submit']));
        $this->assertTrue(isset($response['triggers']['mautic.page_on_hit']));

        $this->assertTrue(isset($response['triggers']['mautic.page_on_hit']['label']));
        $this->assertTrue(isset($response['triggers']['mautic.page_on_hit']['description']));
    }
}

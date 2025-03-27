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

class MessagesTest extends MauticApiTestCase
{
    protected $skipPayloadAssertion = ['channels'];

    protected $singularPlural = [
        'email'        => 'emails',
        'sms'          => 'smses',
        'notification' => 'notifications',
    ];

    public function setUp(): void
    {
        $this->api         = $this->getContext('messages');
        $this->testPayload = [
            'name'        => 'API message',
            'description' => 'Marketing message created via API unit test',
            'channels'    => [
                'email' => [
                    'channel'   => 'email',
                    'channelId' => null,
                    'isEnabled' => true,
                ],
                'sms' => [
                    'channel'   => 'sms',
                    'channelId' => null,
                    'isEnabled' => false,
                ],
            ],
        ];
    }

    protected function setUpPayloadClass()
    {
        foreach ($this->testPayload['channels'] as $key => $channel) {
            $contextName = $this->singularPlural[$channel['channel']];
            $api         = $this->getContext($contextName);
            $response    = $api->create(
                [
                    'name'    => 'MM API test', // required for all
                    'subject' => 'MM API test', // required for email
                    'message' => 'test', // required for sms
                    'heading' => 'test', // required for notifications
                ]
            );
            $this->assertErrors($response);
            $this->testPayload['channels'][$key]['channelId'] = $response[$api->itemName()]['id'];
        }
    }

    protected function clearPayloadItems()
    {
        foreach ($this->testPayload['channels'] as $key => $channel) {
            $contextName = $this->singularPlural[$channel['channel']];
            $api         = $this->getContext($contextName);
            $response    = $api->delete($this->testPayload['channels'][$key]['channelId']);
            $this->assertErrors($response);
        }
    }

    protected function assertPayload($response, array $payload = [], $isBatch = false, $idColumn = 'id', $callback = null)
    {
        parent::assertPayload($response, $payload, $isBatch, $idColumn, $callback);

        // Assert channels
        if (!$isBatch) {
            $this->assertTrue(!empty($response[$this->api->itemName()]));
            foreach ($response[$this->api->itemName()]['channels'] as $responseChannel) {
                // All channels will be returned, even the empty ones
                if (isset($this->testPayload['channels'][$responseChannel['channel']])) {
                    unset($responseChannel['id'], $responseChannel['channelName']);
                    $this->assertSame($this->testPayload['channels'][$responseChannel['channel']], $responseChannel);
                }
            }
        }
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->setUpPayloadClass();
        $this->standardTestGetListOfSpecificIds();
        $this->clearPayloadItems();
    }

    public function testCreateGetAndDelete()
    {
        $this->setUpPayloadClass();
        $this->standardTestCreateGetAndDelete();
        $this->clearPayloadItems();
    }

    public function testEditPatch()
    {
        $this->setUpPayloadClass();
        $this->standardTestEditPatch([
            'name' => 'Modified by PATCH',
        ]);
        $this->clearPayloadItems();
    }

    public function testEditPut()
    {
        $this->setUpPayloadClass();
        $this->standardTestEditPut();
        $this->clearPayloadItems();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }
}

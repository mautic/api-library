<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class StatsTest extends MauticApiTestCase
{
    public function setUp() {
        $this->api = $this->getContext('stats');
    }

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);
        $this->assertTrue(isset($response[$this->api->listName()]), 'The '.$this->api->listName().' array does not exist.');
    }

    protected function assertGreaterThanIdsInArray($response)
    {
        $previous = 0;

        foreach ($response[$this->api->listName()] as $row) {
            $id = (int) $row['id'];
            $this->assertGreaterThan($previous, $id);
            $previous = $id;
        }
    }

    protected function assertLessThanIdsInArray($response)
    {
        $previous = 999999999999999;

        foreach ($response[$this->api->listName()] as $row) {
            $id = (int) $row['id'];
            $this->assertLessThan($previous, $id);
            $previous = $id;
        }
    }

    public function testGetTables()
    {
        $expectedTables = array(
            'asset_downloads',
            'audit_log',
            'campaign_lead_event_log',
            'campaign_leads',
            'channel_url_trackables',
            'companies_leads',
            'dynamic_content_lead_data',
            'dynamic_content_stats',
            'email_stats',
            'email_stats_devices',
            'focus_stats',
            'form_submissions',
            'ip_addresses',
            'lead_categories',
            'lead_companies_change_log',
            'lead_devices',
            'lead_donotcontact',
            'lead_frequencyrules',
            'lead_lists_leads',
            'lead_points_change_log',
            'lead_stages_change_log',
            'lead_utmtags',
            'page_hits',
            'page_redirects',
            'point_lead_action_log',
            'point_lead_event_log',
            'push_notification_stats',
            'sms_message_stats',
            'stage_lead_action_log',
            'video_hits',
            'webhook_logs',
        );
        $response = $this->api->get();
        $this->assertTrue(!empty($response['availableTables']));
        $this->assertSame(
            array_diff($expectedTables, $response['availableTables']),
            array_diff($response['availableTables'], $expectedTables)
        );
    }

    public function testGetSimple()
    {
        $response = $this->api->get('asset_downloads');
        $this->assertPayload($response);
    }

    public function testGetStartLimit()
    {
        $response = $this->api->get('asset_downloads', 1, 2);
        $this->assertPayload($response);
        $this->assertTrue((count($response[$this->api->listName()])) <= 2);
    }

    public function testGetOrderSimple()
    {
        $orderWithoutDir = array(
            array(
                'col' => 'date_download'
            )
        );

        $response = $this->api->get('asset_downloads', 0, 10, $orderWithoutDir);
        $this->assertPayload($response);
        $this->assertGreaterThanIdsInArray($response);
    }

    public function testGetOrderAsc()
    {
        $orderWithDir = array(
            array(
                'col' => 'id',
                'dir' => 'asc'
            )
        );

        $response = $this->api->get('asset_downloads', 0, 10, $orderWithDir);
        $this->assertPayload($response);
        $this->assertGreaterThanIdsInArray($response);
    }

    public function testGetOrderDesc()
    {
        $orderWithDirDesc = array(
            array(
                'col' => 'id',
                'dir' => 'DESC'
            )
        );

        $response = $this->api->get('asset_downloads', 0, 10, $orderWithDirDesc);
        $this->assertPayload($response);
        $this->assertLessThanIdsInArray($response);
    }

    public function testGetWhereEqual()
    {
        $where = array(
            array(
                'col' => 'id',
                'expr' => 'eq',
                'val' => 3,
            )
        );

        $response = $this->api->get('asset_downloads', 0, 2, array(), $where);
        $this->assertPayload($response);
        $this->assertTrue((count($response[$this->api->listName()])) <= 1);

        // The record might not exist in the database, but in case it does...
        if ((count($response[$this->api->listName()])) === 1) {
            $this->assertSame((int) $response[$this->api->listName()][0]['id'], $where[0]['val']);
        }
    }

    public function testGetWhereGreaterThan()
    {
        $where = array(
            array(
                'col' => 'id',
                'expr' => 'gt',
                'val' => 3,
            )
        );
        
        $response = $this->api->get('asset_downloads', 0, 2, array(), $where);
        $this->assertPayload($response);

        // The record might not exist in the database, but in case it does...
        if ((count($response[$this->api->listName()])) > 0) {
            $this->assertSame((int) $response[$this->api->listName()][0]['id'], ($where[0]['val'] + 1));
        }
    }
}

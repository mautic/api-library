<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class PluginsTest extends MauticApiTestCase
{
    public function setUp() {
        $this->api = $this->getContext('plugins');
    }

    public function testGetPluginSettings()
    {
        $integrationName = ''; //Put here the name of an activated and configured plugin
        $response   = $this->api->getPluginSettings($integrationName);
        $this->assertErrors($response);
    }
}

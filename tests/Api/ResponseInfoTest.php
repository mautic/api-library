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

class ResponseInfoTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api = $this->getContext('contacts');
        $response  = $this->api->getList('', 0, 1);
        $this->assertErrors($response);
    }

    public function testGetVersion()
    {
        $version = $this->api->getMauticVersion();
        $this->assertMatchesRegularExpression("/^(\d+\.)?(\d+\.)?(.+|\d+)$/", $version);
    }

    public function testResponseHeaders()
    {
        $headers = $this->api->getResponseHeaders();
        $this->assertEquals($headers['Content-Type'], 'application/json');
    }
}

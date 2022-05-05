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

    public function testGetVersion(): void
    {
        $version = $this->api->getMauticVersion();
        $this->assertNotNull($version);
        $this->assertMatchesRegularExpression("/^(\d+\.)?(\d+\.)?(.+|\d+)$/", $version);
    }

    public function testResponseInfo(): void
    {
        $info = $this->api->getResponseInfo();
        $this->assertEquals($info['content_type'], 'application/json');
    }

    public function testResponseHeaders(): void
    {
        $headers = $this->api->getResponseHeaders();
        $this->assertStringContainsString('200', $headers[0]);
    }
}

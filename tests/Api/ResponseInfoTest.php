<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ResponseInfoTest extends MauticApiTestCase
{
    public function testGetVersion() {
        $apiContext = $this->getContext('contacts');
        $response = $apiContext->getList('', 0, 1);
        $this->assertErrors($response);
        $version = $apiContext->getMauticVersion();
        $this->assertRegExp("/^(\d+\.)?(\d+\.)?(.+|\d+)$/", $version);
    }

    public function testResponseInfo() {
        $apiContext = $this->getContext('contacts');
        $response = $apiContext->getList('', 0, 1);
        $this->assertErrors($response);
        $info = $apiContext->getResponseInfo();
        $this->assertEquals($info['content_type'], 'application/json');
    }

    public function testResponseHeaders() {
        $apiContext = $this->getContext('contacts');
        $response = $apiContext->getList('', 0, 1);
        $this->assertErrors($response);
        $headers = $apiContext->getResponseHeaders();
        $this->assertEquals($headers[0], 'HTTP/1.1 200 OK');
    }
}

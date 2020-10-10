<?php
/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api\Auth;

use Mautic\Auth\AbstractAuth;
use Mautic\Exception\UnexpectedResponseFormatException;
use PHPUnit\Framework\TestCase;

class AbstractAuthTest extends TestCase
{
    protected $config;

    public function setUp(): void
    {
        $this->config = include __DIR__.'/../../local.config.php';
    }

    public function test404Response()
    {
        $auth = $this->getMockForAbstractClass(AbstractAuth::class);
        $this->expectException(UnexpectedResponseFormatException::class);
        $auth->makeRequest('https://github.com/mautic/api-library/this-page-does-not-exist');
    }

    public function testHtmlResponse()
    {
        $auth = $this->getMockForAbstractClass(AbstractAuth::class);
        $this->expectException(UnexpectedResponseFormatException::class);
        $auth->makeRequest($this->config['baseUrl']);
    }

    public function testJsonResponse()
    {
        $auth = $this->getMockForAbstractClass(AbstractAuth::class);
        try {
            $response = $auth->makeRequest($this->config['apiUrl'].'contacts');
            $this->assertTrue(is_array($response));
            $this->assertFalse(empty($response));
        } catch (UnexpectedResponseFormatException $exception) {
            $response = json_decode($exception->getResponse()->getBody(), true);
            $this->assertTrue(is_array($response));
            $this->assertFalse(empty($response));
        }
    }
}

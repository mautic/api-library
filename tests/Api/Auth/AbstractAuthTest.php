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

use GuzzleHttp\Client;
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
        $auth = $this->getMockForAbstractClass(AbstractAuth::class, [new Client()]);
        $this->expectException(UnexpectedResponseFormatException::class);
        $auth->makeRequest('https://github.com/mautic/api-library/this-page-does-not-exist');
    }

    public function testHtmlResponse()
    {
        $auth = $this->getMockForAbstractClass(AbstractAuth::class, [new Client()]);
        $this->expectException(UnexpectedResponseFormatException::class);
        $auth->makeRequest($this->config['baseUrl']);
    }

    public function testJsonResponse()
    {
        $auth = $this->getMockForAbstractClass(AbstractAuth::class, [new Client()]);
        try {
            $auth->makeRequest($this->config['apiUrl'].'contacts');
            self::fail('This should not happen, as the API does not have the authentication.');
        } catch (UnexpectedResponseFormatException $exception) {
            $body = $exception->getResponse()->getBody();
            try {
                $response = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                if ('' === $body) {
                    $body = '(empty string)';
                }

                self::fail('Mautic returned wrong json response: '.$body.'. JSON exception: '.$e->getMessage());
            }
            $this->assertIsArray($response, $body);
            $this->assertGreaterThan(0, count($response));
        }
    }
}

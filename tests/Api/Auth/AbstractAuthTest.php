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
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Mautic\Auth\AbstractAuth;
use Mautic\Exception\ConnectionException;
use Mautic\Exception\UnexpectedResponseFormatException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

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

    public function testConnectionExceptionIsThrownWhenTheTransportFails(): void
    {
        $url       = 'https://mautic.example.com/api/contacts';
        $curlError = 'cURL error 7: Failed to connect to mautic.example.com port 443: Connection timed out';

        // Simulate a transport level failure (timeout/DNS/TLS) from the PSR-18 client.
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')
            ->willThrowException(new ConnectException($curlError, new Request('GET', $url)));

        $auth = $this->getMockForAbstractClass(AbstractAuth::class, [$client]);
        $auth->method('prepareRequest')->willReturnCallback(
            static fn ($url, array $headers, array $parameters, $method, array $settings): array => [$headers, $parameters]
        );

        try {
            $auth->makeRequest($url);
            self::fail('A ConnectionException should have been thrown when the transport fails.');
        } catch (ConnectionException $exception) {
            // The underlying cURL error must be surfaced instead of a generic "status code (0)".
            self::assertStringContainsString($curlError, $exception->getMessage());
            // The target URL is included so it is clear what failed.
            self::assertStringContainsString($url, $exception->getMessage());
            // The original transport exception is preserved for further inspection.
            self::assertInstanceOf(ClientExceptionInterface::class, $exception->getPrevious());
        }
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

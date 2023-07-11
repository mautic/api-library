<?php
/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests;

use GuzzleHttp\Psr7\Response as HttpResponse;
use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    private $headers = [
        'Date'              => ['Fri, 17 Nov 2017 14:09:31 GMT'],
        'Server'            => ['Apache/2.4.25 (Unix) OpenSSL/0.9.8zh PHP/7.0.15'],
        'X-Powered-By'      => ['PHP/7.0.15'],
        'Set-Cookie'        => ['9743595cf0a472cb3ec0272949ffe7e8=psh2rh9cam538t1u3e1gd3d8l3; path=/; HttpOnly'],
        'Transfer-Encoding' => ['chunked'],
        'Content-Type'      => ['text/html; charset=UTF-8'],
    ];

    private $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
</head>
<body>
    hello world
</body>
</html>';

    private $jsonBody = '{
        "hello": "world"
    }';

    private $urlParamBody = 'first=value&arr[]=foo+bar&arr[]=baz';

    private function getHtmlResponse($code = 200)
    {
        return new HttpResponse($code, $this->headers, $this->htmlBody);
    }

    private function getJsonResponse($code = 200)
    {
        return new HttpResponse($code, $this->headers, $this->jsonBody);
    }

    private function getUrlParamResponse($code = 200)
    {
        return new HttpResponse($code, $this->headers, $this->urlParamBody);
    }

    public function testParseResponse()
    {
        $response = new Response($this->getJsonResponse());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($this->headers, $response->getHeaders());
        $this->assertSame($this->jsonBody, $response->getBody());
    }

    public function testValidation()
    {
        $this->expectException(UnexpectedResponseFormatException::class);
        $response = new Response($this->getHtmlResponse(500));
    }

    public function testValidation404()
    {
        try {
            $response = new Response($this->getHtmlResponse(404));
        } catch (UnexpectedResponseFormatException $e) {
            $this->assertSame(404, $e->getCode());
        }
    }

    public function testDecodeFromUrlParamsWithParams()
    {
        $response       = new Response($this->getUrlParamResponse());
        $responseParams = $response->decodeFromUrlParams();
        $this->assertSame('value', $responseParams['first']);
    }

    public function testDecodeFromUrlParamsWithNoParams()
    {
        $response = new Response(new HttpResponse(200, $this->headers));
        $this->expectException(UnexpectedResponseFormatException::class);
        $response->decodeFromUrlParams();
    }

    public function testDecodeFromJsonWithJson()
    {
        $response = new Response($this->getJsonResponse());
        $json     = $response->decodeFromJson();
        $this->assertSame('world', $json['hello']);
    }

    public function testDecodeFromJsonWithEmptyJson()
    {
        $response = new Response(new HttpResponse(200, $this->headers));
        $this->expectException(UnexpectedResponseFormatException::class);
        $response->decodeFromUrlParams();
    }

    public function testGetDecodedBodyWithJson()
    {
        $response = new Response($this->getJsonResponse());
        $body     = $response->getDecodedBody();
        $this->assertSame('world', $body['hello']);
    }

    public function testDecodeFromJsonWithEmptyResponse()
    {
        $response = new Response(new HttpResponse(200, $this->headers));
        $this->expectException(UnexpectedResponseFormatException::class);
        $body = $response->getDecodedBody();
    }

    public function testDecodeFromJsonWithTextResponse()
    {
        $response = new Response(new HttpResponse(200, $this->headers, 'OK'));
        $this->expectException(UnexpectedResponseFormatException::class);
        $body = $response->getDecodedBody();
    }

    public function testSaveToFile()
    {
        $response = new Response($this->getJsonResponse());
        $result   = $response->saveToFile(sys_get_temp_dir());
        $this->assertFalse(empty($result['file']));
        $this->assertTrue(file_exists($result['file']));
        $this->assertSame($this->jsonBody, file_get_contents($result['file']));
        $this->assertTrue(unlink($result['file']));
    }

    public function testIsZip()
    {
        $response = new Response($this->getJsonResponse()->withHeader('Content-Type', 'application/zip'));
        $this->assertTrue($response->isZip());
    }

    public function testIsNotZip()
    {
        $response = new Response($this->getJsonResponse()->withHeader('Content-Type', 'application/json'));
        $this->assertFalse($response->isZip());
    }
}

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

use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    private $headersWithRedirects = 'HTTP/1.1 302 Found
Date: Fri, 17 Nov 2017 14:09:31 GMT
Server: Apache/2.4.25 (Unix) OpenSSL/0.9.8zh PHP/7.0.15
X-Powered-By: PHP/7.0.15
Set-Cookie: 9743595cf0a472cb3ec0272949ffe7e8=4ocah9itj45lmnhv4ub25ml1b7; path=/; HttpOnly
Cache-Control: no-cache
Location: /index_dev.php/s/dashboard
Content-Length: 348

Content-Type: text/html; charset=UTF-8

HTTP/1.1 302 Found
Date: Fri, 17 Nov 2017 14:09:31 GMT
Server: Apache/2.4.25 (Unix) OpenSSL/0.9.8zh PHP/7.0.15
X-Powered-By: PHP/7.0.15
Set-Cookie: 9743595cf0a472cb3ec0272949ffe7e8=ahtmrsuem98b5kunm2g162pa85; path=/; HttpOnly
Cache-Control: no-cache
Location: http://mautic.dev/index_dev.php/s/login
Content-Length: 400
Content-Type: text/html; charset=UTF-8

HTTP/1.1 200 OK
Date: Fri, 17 Nov 2017 14:09:31 GMT
Server: Apache/2.4.25 (Unix) OpenSSL/0.9.8zh PHP/7.0.15
X-Powered-By: PHP/7.0.15
Set-Cookie: 9743595cf0a472cb3ec0272949ffe7e8=psh2rh9cam538t1u3e1gd3d8l3; path=/; HttpOnly
Cache-Control: no-cache
Transfer-Encoding: chunked
Content-Type: text/html; charset=UTF-8';

    private $space = "\r\n\r\n";

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

    private $curlInfo = [
        'url'                     => 'http://mautic.dev/index_dev.php',
        'content_type'            => null,
        'http_code'               => 200,
        'header_size'             => 0,
        'request_size'            => 0,
        'filetime'                => -1,
        'ssl_verify_result'       => 0,
        'redirect_count'          => 0,
        'total_time'              => 0,
        'namelookup_time'         => 0,
        'connect_time'            => 0,
        'pretransfer_time'        => 0,
        'size_upload'             => 0,
        'size_download'           => 0,
        'speed_download'          => 0,
        'speed_upload'            => 0,
        'download_content_length' => -1,
        'upload_content_length'   => -1,
        'starttransfer_time'      => 0,
        'redirect_time'           => 0,
        'redirect_url'            => null,
        'primary_ip'              => null,
        'certinfo'                => [],
        'primary_port'            => 0,
        'local_ip'                => null,
        'local_port'              => 0,
    ];

    private function getHtmlResponse()
    {
        return $this->headersWithRedirects.$this->space.$this->htmlBody;
    }

    private function getJsonResponse()
    {
        return $this->headersWithRedirects.$this->space.$this->jsonBody;
    }

    private function getUrlParamResponse()
    {
        return $this->headersWithRedirects.$this->space.$this->urlParamBody;
    }

    private function getInfo($code = 200)
    {
        $info              = $this->curlInfo;
        $info['http_code'] = $code;

        return $info;
    }

    public function testParseResponse()
    {
        $response = new Response($this->getJsonResponse(), $this->getInfo());
        $this->assertSame($this->headersWithRedirects, $response->getHeaders());
        $this->assertSame($this->jsonBody, $response->getBody());
        $this->assertSame($this->getInfo(), $response->getInfo());
    }

    public function testValidation()
    {
        $this->expectException(UnexpectedResponseFormatException::class);
        $response = new Response($this->getHtmlResponse(), $this->getInfo(500));
    }

    public function testValidation404()
    {
        try {
            $response = new Response($this->getHtmlResponse(), $this->getInfo(404));
        } catch (UnexpectedResponseFormatException $e) {
            $this->assertSame(404, $e->getCode());
        }
    }

    public function testDecodeFromUrlParamsWithParams()
    {
        $response       = new Response($this->getUrlParamResponse(), $this->getInfo());
        $responseParams = $response->decodeFromUrlParams();
        $this->assertSame('value', $responseParams['first']);
    }

    public function testDecodeFromUrlParamsWithNoParams()
    {
        $response = new Response($this->headersWithRedirects.$this->space, $this->getInfo());
        $this->expectException(UnexpectedResponseFormatException::class);
        $response->decodeFromUrlParams();
    }

    public function testDecodeFromJsonWithJson()
    {
        $response = new Response($this->getJsonResponse(), $this->getInfo());
        $json     = $response->decodeFromJson();
        $this->assertSame('world', $json['hello']);
    }

    public function testDecodeFromJsonWithEmptyJson()
    {
        $response = new Response($this->headersWithRedirects.$this->space, $this->getInfo());
        $this->expectException(UnexpectedResponseFormatException::class);
        $response->decodeFromUrlParams();
    }

    public function testGetDecodedBodyWithJson()
    {
        $response = new Response($this->getJsonResponse(), $this->getInfo());
        $body     = $response->getDecodedBody();
        $this->assertSame('world', $body['hello']);
    }

    public function testDecodeFromJsonWithEmptyResponse()
    {
        $response = new Response($this->headersWithRedirects.$this->space, $this->getInfo());
        $this->expectException(UnexpectedResponseFormatException::class);
        $body = $response->getDecodedBody();
    }

    public function testDecodeFromJsonWithTextResponse()
    {
        $response = new Response($this->headersWithRedirects.$this->space.'OK', $this->getInfo());
        $this->expectException(UnexpectedResponseFormatException::class);
        $body = $response->getDecodedBody();
    }

    public function testSaveToFile()
    {
        $response = new Response($this->getJsonResponse(), $this->getInfo());
        $result   = $response->saveToFile(sys_get_temp_dir());
        $this->assertFalse(empty($result['file']));
        $this->assertTrue(file_exists($result['file']));
        $this->assertSame($this->jsonBody, file_get_contents($result['file']));
        $this->assertTrue(unlink($result['file']));
    }

    public function testIsZip()
    {
        $response = new Response($this->getJsonResponse(), ['http_code' => 200, 'content_type' => 'application/zip']);
        $this->assertTrue($response->isZip());
    }

    public function testIsNotZip()
    {
        $response = new Response($this->getJsonResponse(), ['http_code' => 200, 'content_type' => 'application/json']);
        $this->assertFalse($response->isZip());
    }
}

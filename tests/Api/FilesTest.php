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

class FilesTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api                 = $this->getContext('files');
        $this->testPayload['file'] = dirname(__DIR__).'/mauticlogo.png';
        $this->assertTrue(file_exists($this->testPayload['file']), 'A file for test at '.$this->testPayload['file'].' does not exist.');
    }

    protected function assertPayload($response, array $payload = [], $isBatch = false, $idColumn = 'id', $callback = null)
    {
        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->api->itemName()]['name']), 'The '.$this->api->itemName().' file name is empty.');
    }

    public function testGetList()
    {
        $response = $this->api->getList();
        $this->assertErrors($response);
        $this->assertTrue(isset($response[$this->api->listName()]));
    }

    public function testGetListSubdir()
    {
        $this->api->setFolder('images/test_api_dir');
        $createResponse = $this->api->create($this->testPayload);

        $response = $this->api->getList();
        $this->assertTrue(isset($response['files']));
        $this->assertErrors($response);

        $this->api->delete($createResponse['file']['name']);
    }

    public function testGetListMediaFiles()
    {
        $this->api->setFolder('media');
        $response   = $this->api->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDeleteImage()
    {
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $this->assertFalse(empty($response[$this->api->itemName()]['link']), 'The '.$this->api->itemName().' link is empty.');

        $response = $this->api->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }

    public function testCreateAndDeletePhpScript()
    {
        // Get this PHP script to send
        $this->testPayload['file'] = dirname(__DIR__).'/Api/FilesTest.php';
        $this->assertTrue(file_exists($this->testPayload['file']), 'A file for test at '.$this->testPayload['file'].' does not exist.');

        $response = $this->api->create($this->testPayload);
        $this->assertFalse(empty($response['errors']), 'The PHP script was uploaded! Danger! DANGER!');
    }

    public function testCreateAndDeleteImageInSubdir()
    {
        $this->api->setFolder('images/test_api_dir');
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);
        $this->assertFalse(empty($response[$this->api->itemName()]['link']), 'The '.$this->api->itemName().' link is empty.');

        $response = $this->api->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }

    public function testCreateAndDeleteMedia()
    {
        $this->api->setFolder('media');
        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $response = $this->api->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }
}

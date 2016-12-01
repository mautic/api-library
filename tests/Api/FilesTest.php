<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class FilesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'file' => null
    );

    protected $itemName = 'file';

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->itemName]['name']), 'The '.$this->itemName.' file name is empty.');
    }

    public function setUp()
    {
        $this->testPayload['file'] = dirname(__DIR__).'/'.'mauticlogo.png';
        $this->assertTrue(file_exists($this->testPayload['file']), 'A file for test at '.$this->testPayload['file'].' does not exist.');
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('files');
        $response = $apiContext->getList();
        $this->assertTrue(isset($response['files']));
        $this->assertErrors($response);
    }

    public function testGetListSubdir()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('images/flags');
        $response = $apiContext->getList();
        $this->assertTrue(isset($response['files']));
        $this->assertErrors($response);
    }

    public function testGetListAssetFiles()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('assets');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDeleteImage()
    {
        $apiContext = $this->getContext('files');
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $this->assertFalse(empty($response[$this->itemName]['link']), 'The '.$this->itemName.' link is empty.');

        $response = $apiContext->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }

    public function testCreateAndDeletePhpScript()
    {
        $apiContext = $this->getContext('files');

        // Get this PHP script to send
        $this->testPayload['file'] = dirname(__DIR__).'/Api/FilesTest.php';
        $this->assertTrue(file_exists($this->testPayload['file']), 'A file for test at '.$this->testPayload['file'].' does not exist.');

        $response = $apiContext->create($this->testPayload);
        $this->assertFalse(empty($response['error']), 'The PHP script was uploaded! Danger! DANGER!');
    }

    public function testCreateAndDeleteImageInSubdir()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('images/test_api_dir');
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $this->assertFalse(empty($response[$this->itemName]['link']), 'The '.$this->itemName.' link is empty.');

        $response = $apiContext->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }

    public function testCreateAndDeleteAsset()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('assets');
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        $response = $apiContext->delete($response['file']['name']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
    }
}

<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class AssetsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'title' => 'Mautic Logo sent as a API request',
        'storageLocation' => 'remote',
        'file' => 'https://www.mautic.org/media/logos/logo/Mautic_Logo_DB.pdf'
    );

    protected $skipPayloadAssertion = array('file');

    protected $context = 'assets';

    protected $itemName = 'asset';

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList();

        $this->assertErrors($response);
    }

    public function testCreateWithLocalFileGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        // Upload a testing file
        $apiContextFiles = $this->getContext('files');
        $apiContextFiles->setFolder('assets');
        $fileRequest = array(
            'file' => dirname(__DIR__).'/'.'mauticlogo.png'
        );
        $response = $apiContextFiles->create($fileRequest);
        $this->assertErrors($response);
        $file = $response['file'];

        // Build local file payload
        $testPayload = $this->testPayload;
        $testPayload['storageLocation'] = 'local';
        $testPayload['file'] = $file['name'];

        // Create Asset
        $response = $apiContext->create($testPayload);
        $this->assertPayload($response, $testPayload);
        
        // Delete Asset
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testCreateWithRemoteFileGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        // Create Asset
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        
        // Delete Asset
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}
